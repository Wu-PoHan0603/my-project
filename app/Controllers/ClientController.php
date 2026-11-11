<?php

namespace App\Controllers;

use App\Controllers\BaseController;
//讓 ClientController 可以使用 ClientModel。
use App\Models\ClientModel;

// 引用案主詳細資料 Model
use App\Models\ClientProfileModel;

// 引用案主附件 Model
use App\Models\ClientAttachmentModel;

use App\Libraries\SmartyEngine;

use PhpOffice\PhpSpreadsheet\IOFactory;

// 建立 Excel 活頁簿
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// 將活頁簿輸出為 .xlsx 檔案
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 明確設定儲存格為文字，避免姓名、電話等資料被 Excel 改格式
use PhpOffice\PhpSpreadsheet\Cell\DataType;

use Throwable;

use CodeIgniter\HTTP\ResponseInterface;

class ClientController extends BaseController
{
    public function index()
    {
        // 建立 ClientModel 物件
        // 之後透過 $clientModel 操作 clients 資料表
        $clientModel = new ClientModel();

        //取得網址中的keyword
        //例如/clients?keyword=王
        //(string) 避免沒有keyword時取得null
        //trim()移除關鍵字前後的空白
        $keyword = trim(
            (string) $this->request->getGet('keyword')
        );

        //只有關鍵字不是空白食材加入搜尋條件
        if ('' !== $keyword) {
            //like()進行部分文字比對
            //ct_name是要搜尋的資料表欄位
            //$keyword 是使用者輸入的關鍵字
            $clientModel->like('ct_name', $keyword);
        }

        // 每頁筆數只允許 10、20 或 50，預設為 10
        $pageSizeOptions = [10, 20, 50];
        $pageSize = (int) ($this->request->getGet('perPage') ?? 10);

        if (! in_array($pageSize, $pageSizeOptions, true)) {
            $pageSize = 10;
        }

        // clients 是分頁群組名稱，所以頁碼參數使用 page_clients
        $currentPage = max(
            1,
            (int) ($this->request->getGet('page_clients') ?? 1)
        );

        // 按照筆數與目前頁碼取得案主資料
        $clients = $clientModel
            ->orderBy('id', 'DESC')
            ->paginate($pageSize, 'clients', $currentPage);

        return view('clients/index', [
            'clients' => $clients,
            'keyword' => $keyword,
            'pager' => $clientModel->pager,

            // 傳給 index.php 的每頁筆數設定
            'pageSize' => $pageSize,
            'pageSizeOptions' => $pageSizeOptions,

            // 傳給 index.php 的目前頁碼與總頁數
            'currentPage' => $clientModel->pager->getCurrentPage('clients'),
            'pageCount' => $clientModel->pager->getPageCount('clients'),
        ]);
    }

    public function create()
    {
        return view('clients/create');
    }

    // 顯示指定案主的獨立詳細資料頁
    public function details(int $id)
    {
        // 建立案主主資料模型
        $clientModel = new \App\Models\ClientModel();

        // 建立案主個人資料模型
        $profileModel = new \App\Models\ClientProfileModel();

        // 建立案主附件模型
        $attachmentModel = new \App\Models\ClientAttachmentModel();

        // 依照編號查詢案主主資料
        $client = $clientModel->find($id);

        // 找不到案主時顯示 404 頁面
        if ($client === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                '找不到案主資料'
            );
        }

        // 查詢案主個人資料；尚未建立時傳入空陣列
        $profile = $profileModel->find($id) ?? [];

        // 查詢這位案主的附件
        $attachments = $attachmentModel
            ->where('client_id', $id)
            ->findAll();

        // 顯示獨立的 HTML 詳細資料頁
        return view('clients/details', [
            'client' => $client,
            'profile' => $profile,
            'attachments' => $attachments,
        ]);
    }

    //接收案主詳細資料表單，驗證後更新資料庫
    public function updateDetails(int $id)
    {
        //建立操作兩張資料表的Model
        $clientModel = new ClientModel();
        $profileModel = new ClientProfileModel();

        //先確認案主存在；找不到時顯示404
        if (null === $clientModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('找不到案主資料');
        }

        //clients 資料表的欄位
        $clientData = [
            'ct_name' => trim((string) $this->request->getPost('ct_name')),
            'ct_addr' => trim((string) $this->request->getPost('ct_addr')),
        ];

        //client_profiles 資料表中允許由表單修改的欄位
        $profileFields = [
            'region',
            'national_id',
            'sex',
            'birthday',
            'mobile',
            'home_phone',
            'contact_person_1',
            'contact_person_2',
            'case_manager',
            'care_specialist',
            'home_service',
            'registered_address',
            'longitude',
            'latitude',
            'checkin_distance',
            'meal_box_price',
            'remittance_suffix',
            'only_ot_case',
            'living_status',
            'identity_type',
            'disability_status',
            'health_status',
            'disability_level',
            'disease_names',
            'care_level',
            'assessment_summary',
            'treatment_plan_summary',
            'is_active',
        ];

        // 讀取表單資料；空白欄位轉成 null，方便寫入可為空的資料庫欄位
        $profileData = [];

        foreach ($profileFields as $field) {
            $value = $this->request->getPost($field);

            if ($value === null || trim((string) $value) === '') {
                $profileData[$field] = null;
            } else {
                // 保留字串格式，例如匯款尾碼前面的 0
                $profileData[$field] = trim((string) $value);
            }
        }

        // 合併兩張資料表的表單欄位，交給 CI4 驗證
        $formData = array_merge($clientData, $profileData);

        // 伺服器端驗證規則；即使有人略過瀏覽器表單限制也會檢查
        $rules = [
            'ct_name' => 'required|min_length[2]|max_length[100]',
            'ct_addr' => 'required|max_length[255]',

            'region' => 'permit_empty|max_length[100]',
            'national_id' => 'permit_empty|max_length[20]',
            'sex' => 'permit_empty|max_length[20]',
            'birthday' => 'permit_empty|valid_date[Y-m-d]',
            'mobile' => 'permit_empty|max_length[30]',
            'home_phone' => 'permit_empty|max_length[30]',
            'contact_person_1' => 'permit_empty|max_length[255]',
            'contact_person_2' => 'permit_empty|max_length[255]',
            'case_manager' => 'permit_empty|max_length[100]',
            'care_specialist' => 'permit_empty|max_length[100]',
            'home_service' => 'permit_empty|max_length[100]',
            'registered_address' => 'permit_empty|max_length[255]',
            'longitude' => 'permit_empty|decimal',
            'latitude' => 'permit_empty|decimal',
            'checkin_distance' => 'permit_empty|decimal',
            'meal_box_price' => 'permit_empty|decimal',
            'remittance_suffix' => 'permit_empty|max_length[20]',
            'only_ot_case' => 'required|in_list[0,1]',
            'living_status' => 'permit_empty|max_length[100]',
            'identity_type' => 'permit_empty|max_length[255]',
            'disability_status' => 'permit_empty|max_length[100]',
            'health_status' => 'permit_empty',
            'disability_level' => 'permit_empty|max_length[100]',
            'disease_names' => 'permit_empty',
            'care_level' => 'permit_empty|max_length[100]',
            'assessment_summary' => 'permit_empty',
            'treatment_plan_summary' => 'permit_empty',
            'is_active' => 'required|in_list[0,1]',
        ];

        // 驗證失敗時返回詳細資料頁，保留輸入內容和錯誤訊息
        if (! $this->validateData($formData, $rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 只取出通過驗證的 clients 欄位
        $validatedData = $this->validator->getValidated();

        $validatedClientData = array_intersect_key(
            $validatedData,
            array_flip(['ct_name', 'ct_addr'])
        );

        // 只取出通過驗證的 client_profiles 欄位
        $validatedProfileData = array_intersect_key(
            $validatedData,
            array_flip($profileFields)
        );

        // 兩張資料表一起更新；任一筆資料失敗時，避免只存成功一半
        $db = \Config\Database::connect();
        $db->transStart();

        $clientModel->update($id, $validatedClientData);

        // 這位案主第一次填詳細資料時新增資料列，之後則更新既有資料列
        if ($profileModel->find($id) === null) {
            $profileModel->insert(
                array_merge(
                    ['client_id' => $id],
                    $validatedProfileData
                )
            );
        } else {
            $profileModel->update($id, $validatedProfileData);
        }

        $db->transComplete();

        // 資料庫儲存失敗時返回頁面並顯示錯誤
        if ($db->transStatus() === false) {
            return redirect()
                ->to('clients/' . $id . '/details')
                ->with('error', '案主詳細資料更新失敗，請確認資料庫欄位設定');
        }

        // 儲存成功後重新載入詳細資料頁
        return redirect()
            ->to('clients/' . $id . '/details')
            ->with('success', '案主詳細資料更新成功');
    }

    public function store()
    {
        $data = [
            'ct_name' => trim((string) $this->request->getPost('ct_name')),
            'ct_addr' => trim((string) $this->request->getPost('ct_addr')),
            'route_no' => trim((string) $this->request->getPost('route_no')),
        ];

        // 設定每個欄位的錯誤訊息
        // 設定每個欄位的驗證規則與畫面標籤
        $rules = [
            'ct_name' => [
                'label' => '案主姓名',
                'rules' => 'required|min_length[2]|max_length[100]',
            ],
            'ct_addr' => [
                'label' => '案主地址',
                'rules' => 'required|max_length[255]',
            ],
            'route_no' => [
                'label' => '路線編號',
                'rules' => 'required|integer',
            ],
        ];

        // 依照「欄位名稱 → 驗證規則」設定中文錯誤訊息
        $messages = [
            'ct_name' => [
                'required'   => '請輸入案主姓名。',
                'min_length' => '案主姓名至少需要 2 個字。',
                'max_length' => '案主姓名不可超過 100 個字。',
            ],
            'ct_addr' => [
                'required'   => '請輸入案主地址。',
                'max_length' => '案主地址不可超過 255 個字。',
            ],
            'route_no' => [
                'required' => '請輸入路線編號。',
                'integer'  => '路線編號必須是整數。',
            ],
        ];

        //驗證失敗時返回新增表單，保留輸入內容及錯誤訊息。
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $this->validator->getErrors());
        }

        //只取出通過驗證的欄位資料
        $vaildData = $this->validator->getValidated();

        //路線編號通過整數驗證，再轉成PHP整數
        $vaildData['route_no'] = (int) $vaildData['route_no'];

        //請Model將通過驗證的資料新增至資料庫
        $clientModel = new ClientModel();
        $clientModel->insert($vaildData);

        //新增完成後回到案主列表，並顯示一次性成功訊息
        return redirect()
            ->to('/clients')
            ->with('success', '案主新增成功');
    }

    // 接收網址傳進來的案主編號
    // int 表示 $id 必須是整數型態
    public function edit(int $id)
    {
        // 建立 ClientModel 物件
        // 用來操作 clients 資料表
        $clientModel = new ClientModel();

        // 根據主鍵 id 查詢一位案主
        // 例如 $id 是 2，就查詢 id = 2 的資料
        $client = $clientModel->find($id);

        // 如果找不到這筆案主資料
        if ($client === null) {
            // 回到案主列表並顯示一次性的錯誤訊息
            return redirect()
                ->to('/clients')
                ->with('error', '找不到指定的案主資料');
        }

        // 將查詢結果放進 $data
        // 陣列鍵 client 會成為 View 裡面的 $client
        $data = [
            'client' => $client,
        ];

        // 載入 app/Views/clients/edit.php
        // 並把 $data 傳給修改畫面
        return view('clients/edit', $data);
    }

    // 接收表單網址中的案主編號
    // 例如 POST /clients/update/2，$id 就是 2
    public function update(int $id)
    {
        //接收修改表單送來的資料，並移除文字前後的空白
        $data = [
            'ct_name' => trim((string) $this->request->getPost('ct_name')),
            'ct_addr' => trim((string) $this->request->getPost('ct_addr')),
            'route_no' => trim((string) $this->request->getPost('route_no')),
        ];

        //驗證規則：三個欄位都必填，路線編號必須是整數
        $rules = [
            'ct_name' => 'required',
            'ct_addr' => 'required',
            'route_no' => 'required|integer',
        ];

        //每條驗證規則對應的自訂錯誤訊息
        $messages = [
            'ct_name' => [
                'required' => '請輸入案主姓名。',
            ],
            'ct_addr' => [
                'required' => '請輸入案主地址。',
            ],
            'route_no' => [
                'required' => '請輸入路線編號。',
                'integer' => '路線編號必須是整數。',
            ],
        ];

        //驗證失敗時回到修改頁，保留使用者輸入並顯示錯誤
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $this->validator->getErrors());
        }

        // 建立 Model，準備操作 clients 資料表
        $clientModel = new ClientModel();

        // 先確認這筆資料確實存在
        $client = $clientModel->find($id);

        // 如果找不到資料，就停止更新
        if ($client === null) {
            return redirect()
                ->to('/clients')
                ->with('error', '找不到指定的案主資料');
        }

        // 取得表單欄位
        // (string) 避免 null 直接傳入 trim()
        // trim() 會移除文字前後的空白
        $ctName = trim(
            (string) $this->request->getPost('ct_name')
        );

        $ctAddress = trim(
            (string) $this->request->getPost('ct_addr')
        );

        $routeNo = trim(
            (string) $this->request->getPost('route_no')
        );

        // 只要有一個欄位空白，就停止更新
        // 避免空字串覆蓋資料庫原本的資料
        if (
            $ctName === ''
            || $ctAddress === ''
            || $routeNo === ''
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '姓名、地址及路線編號都不能空白');
        }

        // 驗證路線編號是否為整數格式
        // 驗證失敗時，filter_var() 會回傳 false
        if (filter_var($routeNo, FILTER_VALIDATE_INT) === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '路線編號必須是整數');
        }

        // 整理要更新到資料庫的內容
        // 使用已經整理、驗證過的變數
        $data = [
            'ct_name'    => $ctName,
            'ct_addr' => $ctAddress,
            'route_no'   => (int) $routeNo,
        ];

        // 更新主鍵等於 $id 的資料
        // 第一個參數：要修改哪一筆
        // 第二個參數：要修改成什麼內容
        $clientModel->update($id, $data);

        // 更新完成後回到案主列表
        return redirect()
            ->to('/clients')
            ->with('success', '案主資料修改成功');
    }

    // 接收 Route 傳進來的案主編號
    public function delete(int $id)
    {
        //建立ClientModel物件
        //準備操作clients資料表
        $clientModel = new ClientModel();

        //先查詢指定的案主是否存在
        $client = $clientModel->find($id);

        if (null === $client) {
            //回到案主列表
            //並顯示一次性錯誤訊息
            return redirect()
                ->to('/clients')
                ->with('error', '找不到指定的案主資料');
        }

        //軟刪除指定的案主
        //因為Model已啟用 useSoftDeletes
        //所以這邊只會寫入 d_date
        $clientModel->delete($id);

        //刪除完成後返回案主列表
        //success是一次性的Flashdata訊息
        return redirect()
            ->to('/clients')
            ->with('success', '案主已移至資源回收桶');
    }

    // 顯示資源回收桶
    public function trash()
    {
        //建立ClientModel物件
        //用來查詢clients資料表
        $clientModel = new ClientModel();

        // 1.onlyDeleted() 只查詢已軟刪除的資料 也就是 d_date 有刪除時間的資料
        // 2.orderBy() 依照刪除時間倒序排列最新刪除的資料會顯示在最上方
        // 3.findAll() 執行查詢並取得全部結果
        $deletedClients = $clientModel
            ->onlyDeleted()
            ->orderBy('d_date', 'DESC')
            ->findAll();

        //將查詢結果放入$data
        //deletedClients會成為View裡的$deletedClients
        $data = [
            'deletedClients' => $deletedClients,
        ];

        //載入資源回收桶View
        //對應app/View/clients/trash.php
        return view('clients/trash', $data);
    }

    //接收Route傳來的案主編號
    public function restore(int $id)
    {
        //建立ClinetModel物件
        $clientModel = new ClientModel();

        //onlyDeleted()表示只搜尋已軟刪除的資料
        //避免把原本就正常的資料當成回收桶資料
        // 只查詢回收桶中編號符合 $id 的案主
        $deletedClient = $clientModel
            ->onlyDeleted()
            ->find($id);

        //如果回收桶裡找不到這筆資料
        if (null === $deletedClient) {
            //回到資源回收桶並顯示錯誤訊息
            return redirect()
                ->to('clients/trash')
                ->with('error', '找不到指定的已刪除案主');
        }

        //呼叫Model自訂的還原方式
        $restore = $clientModel->restoreClient($id);

        //如果資料庫更新失敗
        if (! $restore) {
            //回到資源回收桶並顯示錯誤訊息
            return redirect()
                ->to('clients/trash')
                ->with('error', '案主資料還原失敗');
        }

        return redirect()
            ->to('clients/trash')
            ->with('success', '案主資料還原成功');
    }

    public function forceDelete(int $id)
    {
        //建立ClientModel物件
        $clientModel = new ClientModel();

        //只從已軟刪除的資料中尋找指定編號
        //避免直接永久刪除一般列表中的正常資料
        $deletedClients = $clientModel
            ->onlyDeleted()
            ->find($id);

        //如果資源回收桶找不到這筆資料
        if (null === $deletedClients) {
            //回到資源回收桶並顯示錯誤訊息
            return redirect()
                ->to('clients/trash')
                ->with('error', '找不到指定的已刪除案主');
        }

        //第二個參數true代表永久刪除
        //這會真正移除資料列,不是更新 d_date
        $deleted = $clientModel->delete($id, true);

        //如果資料庫刪除失敗
        if (! $deleted) {
            return redirect()
                ->to('clients/trash')
                ->with('error', '案主資料永久刪除失敗');
        }

        //永久刪除成功後回到資源回收桶
        return redirect()
            ->to('clients/trash')
            ->with('success', '案主資料已永久刪除');
    }

    // 接收指定案主上傳的人物照片
    public function uploadAttachment(int $id)
    {
        // 建立案主資料模型，用來確認案主是否存在
        $clientModel = new ClientModel();

        // 依照網址中的案主編號查詢案主
        $client = $clientModel->find($id);

        // 找不到案主就回到列表並顯示錯誤
        if ($client === null) {
            return redirect()
                ->to('clients')
                ->with('error', '找不到指定的案主資料');
        }

        // 取得表單中 name="attachment" 的上傳檔案
        $file = $this->request->getFile('attachment');

        // 確認檔案存在、上傳成功，而且尚未被移動
        if (
            $file === null
            || ! $file->isValid()
            || $file->hasMoved()
        ) {
            return redirect()
                ->to('clients/' . $id . '/details')
                ->with('error', '照片上傳失敗，請重新選擇檔案');
        }

        // 設定人物照片的最大檔案大小為 10 MB
        $maxFileSize = 10 * 1024 * 1024;

        // 取得照片大小，轉成整數方便比較
        $fileSize = (int) $file->getSize();

        // 拒絕空檔案或超過大小限制的檔案
        if ($fileSize <= 0 || $fileSize > $maxFileSize) {
            return redirect()
                ->to('clients/' . $id . '/details')
                ->with('error', '照片不可為空檔案，且大小不可超過 10 MB');
        }

        // 只允許這些圖片 MIME 類型，並指定儲存時使用的副檔名
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        // 由 PHP 判斷檔案實際類型，不只依賴使用者選擇的副檔名
        $mimeType = $file->getMimeType();

        // MIME 類型不在圖片清單中就拒絕上傳
        if (! isset($allowedMimeTypes[$mimeType])) {
            return redirect()
                ->to('clients/' . $id . '/details')
                ->with('error', '只允許上傳 JPG、PNG、WebP 或 GIF 圖片');
        }

        // 從允許清單取得安全的副檔名
        $extension = $allowedMimeTypes[$mimeType];

        // 建立這位案主專屬的私有照片資料夾
        $clientDirectory = WRITEPATH
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'clients'
            . DIRECTORY_SEPARATOR
            . $id;

        // 如果資料夾不存在，就建立資料夾
        if (
            ! is_dir($clientDirectory)
            && ! mkdir($clientDirectory, 0750, true)
            && ! is_dir($clientDirectory)
        ) {
            return redirect()
                ->to('clients/' . $id . '/details')
                ->with('error', '無法建立照片儲存資料夾');
        }

        // 使用隨機內容產生 SHA-256 檔名，避免使用原始檔名
        $storedName = hash('sha256', random_bytes(32)) . '.' . $extension;

        // 組合照片移動後的完整路徑，稍後失敗時用來清除檔案
        $storedPath = $clientDirectory
            . DIRECTORY_SEPARATOR
            . $storedName;

        // 原始檔名只存入資料庫供畫面顯示，不用來命名實際檔案
        $originalName = $file->getClientName();

        // 取得目前登入者的使用者編號
        $uploadedBy = session()->get('user_id');

        // 準備寫入 client_attachments 的資料
        $attachmentData = [
            'client_id'    => $id,
            'category'     => 'client_photo',
            'original_name' => $originalName,
            'stored_name'  => $storedName,
            'mime_type'    => $mimeType,
            'file_size'    => $fileSize,
            'uploaded_by'  => is_numeric($uploadedBy)
                ? (int) $uploadedBy
                : null,
        ];

        // 建立附件模型，準備新增照片資料
        $attachmentModel = new ClientAttachmentModel();

        try {
            // 將照片移到案主的私有資料夾，並使用雜湊檔名儲存
            $file->move($clientDirectory, $storedName);

            // 將照片資訊新增到附件資料表
            $insertResult = $attachmentModel->insert($attachmentData);

            // insert 回傳 false 代表資料庫沒有成功新增
            if ($insertResult === false) {
                throw new \RuntimeException('照片資料寫入資料庫失敗');
            }
        } catch (\Throwable $exception) {
            // 若資料庫新增失敗，刪除剛才已經移動的照片
            if (is_file($storedPath)) {
                unlink($storedPath);
            }

            // 將詳細錯誤記錄在 CI4 日誌，不直接顯示給使用者
            log_message(
                'error',
                '案主人像上傳失敗：' . $exception->getMessage()
            );

            // 回到案主詳細資料頁並顯示一般錯誤訊息
            return redirect()
                ->to('clients/' . $id . '/details')
                ->with('error', '照片儲存失敗，請稍後再試');
        }

        // 照片和資料庫資料都成功後，回到案主詳細資料頁
        return redirect()
            ->to('clients/' . $id . '/details')
            ->with('success', '人物照片上傳成功');
    }

    // 顯示指定案主的附件
    public function viewAttachment(int $clientId, int $attachmentId)
    {
        // 確認網址中的案主存在
        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        // 案主不存在時回傳 404
        if ($client === null) {
            return $this->response->setStatusCode(404);
        }

        // 只查詢屬於這位案主的指定附件
        $attachmentModel = new ClientAttachmentModel();
        $attachment = $attachmentModel
            ->where('client_id', $clientId)
            ->find($attachmentId);

        // 附件不存在或不屬於這位案主時回傳 404
        if ($attachment === null) {
            return $this->response->setStatusCode(404);
        }

        // 取得這位案主的私人附件資料夾
        $clientDirectory = realpath(
            WRITEPATH . 'uploads/clients/' . $clientId
        );

        // 資料夾不存在時回傳 404
        if ($clientDirectory === false) {
            return $this->response->setStatusCode(404);
        }

        // 只取資料庫記錄中的檔名，不接受資料夾路徑
        $storedName = basename((string) ($attachment['stored_name'] ?? ''));

        // 檔名空白時回傳 404
        if ($storedName === '') {
            return $this->response->setStatusCode(404);
        }

        // 組出附件完整路徑，並確認檔案實際存在
        $filePath = realpath(
            $clientDirectory . DIRECTORY_SEPARATOR . $storedName
        );

        // 檔案不存在時回傳 404
        if ($filePath === false) {
            return $this->response->setStatusCode(404);
        }

        // 確認檔案仍在這位案主的資料夾內
        $allowedPathPrefix = $clientDirectory . DIRECTORY_SEPARATOR;

        // 路徑不符合或不是一般檔案時回傳 404
        if (
            ! str_starts_with($filePath, $allowedPathPrefix)
            || ! is_file($filePath)
            || ! is_readable($filePath)
        ) {
            return $this->response->setStatusCode(404);
        }

        // 根據實際檔案內容判斷 MIME 類型
        $actualMimeType = mime_content_type($filePath);

        // 只允許顯示圖片與 PDF
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
        ];

        // 檔案類型不允許時回傳 404
        if (! in_array($actualMimeType, $allowedMimeTypes, true)) {
            return $this->response->setStatusCode(404);
        }

        // 讀取檔案內容
        $fileContents = file_get_contents($filePath);

        // 檔案讀取失敗時回傳 404
        if ($fileContents === false) {
            return $this->response->setStatusCode(404);
        }

        // 將圖片或 PDF 以瀏覽器可顯示的方式回傳
        return $this->response
            ->setContentType($actualMimeType)
            ->setHeader('Content-Disposition', 'inline')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody($fileContents);
    }

    // 永久刪除指定案主的附件
    public function deleteAttachment(int $clientId, int $attachmentId)
    {
        // 確認網址中的案主存在
        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        // 案主不存在時回到案主列表
        if ($client === null) {
            return redirect()
                ->to('/clients')
                ->with('error', '找不到指定的案主資料');
        }

        // 只查詢屬於這位案主的指定附件
        $attachmentModel = new ClientAttachmentModel();
        $attachment = $attachmentModel
            ->where('client_id', $clientId)
            ->find($attachmentId);

        // 附件不存在或不屬於這位案主時回到詳細資料頁
        if ($attachment === null) {
            return redirect()
                ->to('/clients/' . $clientId . '/details')
                ->with('error', '找不到指定的附件');
        }

        // 只取資料庫記錄中的檔名，不接受資料夾路徑
        $storedName = basename((string) ($attachment['stored_name'] ?? ''));

        // 檔名空白時停止刪除
        if ($storedName === '') {
            return redirect()
                ->to('/clients/' . $clientId . '/details')
                ->with('error', '附件檔名資料無效');
        }

        // 先設定實體檔案路徑；檔案可能已不存在
        $filePath = false;

        // 取得這位案主的私人附件資料夾
        $clientDirectory = realpath(
            WRITEPATH . 'uploads/clients/' . $clientId
        );

        // 資料夾存在時，才繼續確認檔案路徑
        if ($clientDirectory !== false) {
            // 取得附件實際路徑；檔案不存在時會得到 false
            $resolvedFilePath = realpath(
                $clientDirectory . DIRECTORY_SEPARATOR . $storedName
            );

            // 檔案存在時，確認它仍位於案主資料夾內
            if ($resolvedFilePath !== false) {
                // 建立允許的資料夾路徑前綴
                $allowedPathPrefix = $clientDirectory . DIRECTORY_SEPARATOR;

                // 發現路徑不安全或不是一般檔案時停止刪除
                if (
                    ! str_starts_with($resolvedFilePath, $allowedPathPrefix)
                    || ! is_file($resolvedFilePath)
                ) {
                    return redirect()
                        ->to('/clients/' . $clientId . '/details')
                        ->with('error', '附件路徑資料無效');
                }

                // 保存通過檢查的實體檔案路徑
                $filePath = $resolvedFilePath;
            }
        }

        // 第二個參數 true 代表直接永久刪除資料庫記錄
        $deleted = $attachmentModel->delete($attachmentId, true);

        // 資料庫記錄刪除失敗時，不刪除實體檔案
        if (! $deleted) {
            return redirect()
                ->to('/clients/' . $clientId . '/details')
                ->with('error', '附件資料刪除失敗');
        }

        // 資料庫記錄刪除成功後，再刪除實體檔案
        if ($filePath !== false && is_file($filePath)) {
            // @ 避免檔案系統錯誤直接中斷頁面，失敗時會寫入系統紀錄
            $fileDeleted = @unlink($filePath);

            // 記錄實體檔案刪除失敗，供管理者後續檢查
            if (! $fileDeleted) {
                log_message('error', '案主附件資料已刪除，但實體檔案清理失敗');

                // 提醒使用者資料記錄已刪除，但檔案清理需要處理
                return redirect()
                    ->to('/clients/' . $clientId . '/details')
                    ->with('error', '附件資料已刪除，但檔案清理失敗，請聯絡管理者');
            }
        }

        // 資料庫記錄與實體檔案都完成刪除
        return redirect()
            ->to('/clients/' . $clientId . '/details')
            ->with('success', '附件已永久刪除');
    }

    // 回傳指定案主的最新照片，供姓名旁的預覽使用
    public function photoPreview(int $id)
    {
        // 找出這位案主最新上傳的圖片附件
        $attachmentModel = new ClientAttachmentModel();
        $attachment = $attachmentModel
            ->where('client_id', $id)
            ->where('category', 'client_photo')
            ->like('mime_type', 'image/', 'after')
            ->orderBy('id', 'DESC')
            ->first();

        // 沒有圖片附件時回傳 404
        if ($attachment === null) {
            return $this->response->setStatusCode(404);
        }

        // 取得這位案主的私人檔案資料夾
        $clientDirectory = realpath(
            WRITEPATH . 'uploads/clients/' . $id
        );

        // 只使用附件記錄中的檔名，不接受資料夾路徑
        $storedName = basename($attachment['stored_name']);

        // 確認圖片檔案實際存在
        $filePath = realpath(
            WRITEPATH
            . 'uploads/clients/'
            . $id
            . DIRECTORY_SEPARATOR
            . $storedName
        );

        // 資料夾或檔案不存在時回傳 404
        if ($clientDirectory === false || $filePath === false) {
            return $this->response->setStatusCode(404);
        }

        // 確認檔案仍在這位案主的資料夾內，避免讀取其他路徑
        $allowedPathPrefix = $clientDirectory . DIRECTORY_SEPARATOR;

        if (! str_starts_with($filePath, $allowedPathPrefix)) {
            return $this->response->setStatusCode(404);
        }

        // 再從實際檔案判斷 MIME 類型，不只相信資料庫內容
        $actualMimeType = mime_content_type($filePath);

        // 只允許瀏覽器預覽這些圖片格式
        $allowedImageTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ];

        // 檔案無法讀取或不是允許的圖片格式時回傳 404
        if (
            ! is_readable($filePath)
            || ! in_array($actualMimeType, $allowedImageTypes, true)
        ) {
            return $this->response->setStatusCode(404);
        }

        // 設定圖片回應標頭，並傳回圖片內容
        return $this->response
            ->setContentType($actualMimeType)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($filePath));
    }

    // 匯出現有案主基本資料與詳細資料為 Excel
    public function export()
    {
        // 取得資料庫連線
        $db = \Config\Database::connect();

        // 設定 Excel 欄位順序
        // s_num 對應 clients.id，作為回傳上傳時辨認案主的依據
        $columns = [
            's_num',
            'ct_name',
            'ct_addr',
            'route_no',
            'region',
            'national_id',
            'sex',
            'birthday',
            'mobile',
            'home_phone',
            'contact_person_1',
            'contact_person_2',
            'case_manager',
            'care_specialist',
            'home_service',
            'registered_address',
            'longitude',
            'latitude',
            'checkin_distance',
            'meal_box_price',
            'remittance_suffix',
            'only_ot_case',
            'living_status',
            'identity_type',
            'disability_status',
            'health_status',
            'disability_level',
            'disease_names',
            'care_level',
            'assessment_summary',
            'treatment_plan_summary',
            'is_active',
        ];

        // 指定要匯出的資料欄位
        // b_date、e_date、d_date 是系統時間欄位，不放進可修改的 Excel
        $selectFields = [
            'c.id AS s_num',
            'c.ct_name',
            'c.ct_addr',
            'c.route_no',
            'cp.region',
            'cp.national_id',
            'cp.sex',
            'cp.birthday',
            'cp.mobile',
            'cp.home_phone',
            'cp.contact_person_1',
            'cp.contact_person_2',
            'cp.case_manager',
            'cp.care_specialist',
            'cp.home_service',
            'cp.registered_address',
            'cp.longitude',
            'cp.latitude',
            'cp.checkin_distance',
            'cp.meal_box_price',
            'cp.remittance_suffix',
            'cp.only_ot_case',
            'cp.living_status',
            'cp.identity_type',
            'cp.disability_status',
            'cp.health_status',
            'cp.disability_level',
            'cp.disease_names',
            'cp.care_level',
            'cp.assessment_summary',
            'cp.treatment_plan_summary',
            'cp.is_active',
        ];

        // 查詢未被軟刪除的案主，並合併其詳細資料
        $rows = $db->table('clients c')
            ->select($selectFields)
            ->join(
                'client_profiles cp',
                'cp.client_id = c.id',
                'left'
            )
            ->where('c.d_date', null)
            ->orderBy('c.id', 'ASC')
            ->get()
            ->getResultArray();

        // 建立新的 Excel 活頁簿
        $spreadsheet = new Spreadsheet();

        // 取得第一個工作表並命名
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('案主資料');

        // 將資料庫欄位名稱放在第一列，供後續匯入程式辨認
        foreach ($columns as $index => $columnName) {
            $cell = $sheet->getCell([$index + 1, 1]);
            $cell->setValueExplicit($columnName, DataType::TYPE_STRING);
        }

        // 從第二列開始輸出案主資料
        foreach ($rows as $rowIndex => $row) {
            // Excel 第 1 列是標題，因此資料從第 2 列開始
            $excelRow = $rowIndex + 2;

            // 依照欄位順序逐格輸出資料
            foreach ($columns as $columnIndex => $columnName) {
                // null 欄位以空字串輸出
                $value = $row[$columnName] ?? '';

                // 所有內容先以文字輸出，保留編號、電話尾碼與日期格式
                $sheet
                    ->getCell([$columnIndex + 1, $excelRow])
                    ->setValueExplicit((string) $value, DataType::TYPE_STRING);
            }
        }

        // 凍結標題列，向下捲動時仍看得到欄位名稱
        $sheet->freezePane('A2');

        // 開啟標題列篩選功能
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = max(1, $sheet->getHighestRow());
        $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);

        // 設定標題列樣式
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFill()
            ->getStartColor()
            ->setARGB('FFD9EAF7');

        // 自動調整欄寬，方便檢視資料
        foreach (range('A', $lastColumn) as $columnLetter) {
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // 建立 Excel 寫入器
        $writer = new Xlsx($spreadsheet);

        // 暫存 Excel 二進位內容
        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();

        // 設定下載檔名
        $fileName = '案主資料_' . date('Ymd_His') . '.xlsx';

        // 回傳 Excel 檔案下載
        return $this->response->download($fileName, $excelContent);
    }

    // 使用 Smarty 顯示案主 Excel 匯入頁
    public function import()
    {
        // 建立 Smarty 元件
        $smarty = new SmartyEngine();

        // 傳入頁面需要的網址
        $smarty->assign('cssUrl', base_url('css/style.css'));
        $smarty->assign('homeUrl', base_url('/'));
        $smarty->assign('clientListUrl', base_url('clients'));
        $smarty->assign('staffListUrl', base_url('delivery_staff'));
        $smarty->assign('trashUrl', base_url('clients/trash'));
        $smarty->assign('changePasswordUrl', base_url('change-password'));
        $smarty->assign('logoutUrl', base_url('logout'));
        $smarty->assign('importPreviewUrl', base_url('clients/import/preview'));

        // 傳入上傳表單需要的 CSRF 欄位
        $smarty->assign('csrfField', csrf_field());

        // 取得登入者帳號
        $currentUsername = session()->get('username') ?? '未取得帳號';

        // 取得顯示名稱；沒有設定時以登入帳號代替
        $currentDisplayName = session()->get('display_name')
            ?: $currentUsername;

        // 將角色代碼轉成中文名稱
        $roleLabels = [
            'admin'  => '系統管理員',
            'staff'  => '工作人員',
            'viewer' => '唯讀使用者',
        ];

        // 取得目前角色及角色中文名稱
        $currentRole = session()->get('role') ?? '';
        $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';

        // 傳入側欄顯示的登入者資訊
        $smarty->assign('currentUsername', $currentUsername);
        $smarty->assign('currentDisplayName', $currentDisplayName);
        $smarty->assign('currentRoleLabel', $currentRoleLabel);

        // 傳入登出表單需要的 CSRF 欄位
        $smarty->assign('logoutCsrfField', csrf_field());

        // 取得一次性錯誤訊息
        $errorMessage = session()->getFlashdata('error') ?? '';
        $smarty->assign('errorMessage', $errorMessage);

        // 執行 Smarty 模板並取得 HTML
        $html = $smarty->fetch('clients_import.tpl');

        // 回傳 UTF-8 HTML 頁面
        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    // 接收匯出的案主 Excel，檢查資料並建立預覽
    public function previewImport()
    {
        // 取得上傳表單中的 Excel 檔案
        $file = $this->request->getFile('excel_file');

        // 確認檔案存在、上傳成功且尚未被移動
        if (
            $file === null
            || ! $file->isValid()
            || $file->hasMoved()
        ) {
            return redirect()
                ->to('/clients/import')
                ->with('error', '請選擇有效的 Excel 檔案。');
        }

        // 只接受由系統匯出的 .xlsx 檔案
        if (strtolower($file->getExtension()) !== 'xlsx') {
            return redirect()
                ->to('/clients/import')
                ->with('error', '請上傳系統匯出的 .xlsx 檔案。');
        }

        // 限制上傳檔案大小為 5 MB
        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()
                ->to('/clients/import')
                ->with('error', '檔案大小不可超過 5 MB。');
        }

        try {
            // 從上傳暫存位置讀取 Excel
            $spreadsheet = IOFactory::load($file->getTempName());

            // 取得目前工作表
            $sheet = $spreadsheet->getActiveSheet();

            // 將工作表內容轉成陣列；第四個 true 會用欄位字母作為索引
            $sheetRows = $sheet->toArray(null, true, true, true);

            // 檢查工作表是否有資料
            if (empty($sheetRows)) {
                return redirect()
                    ->to('/clients/import')
                    ->with('error', 'Excel 工作表沒有資料。');
            }

            // 取出第一列，作為欄位標題
            $headerRow = array_shift($sheetRows);

            // 列出系統匯出的所有欄位
            // 必須完整保留欄位名稱，避免漏掉資料造成原資料被清空
            $expectedHeaders = [
                's_num',
                'ct_name',
                'ct_addr',
                'route_no',
                'region',
                'national_id',
                'sex',
                'birthday',
                'mobile',
                'home_phone',
                'contact_person_1',
                'contact_person_2',
                'case_manager',
                'care_specialist',
                'home_service',
                'registered_address',
                'longitude',
                'latitude',
                'checkin_distance',
                'meal_box_price',
                'remittance_suffix',
                'only_ot_case',
                'living_status',
                'identity_type',
                'disability_status',
                'health_status',
                'disability_level',
                'disease_names',
                'care_level',
                'assessment_summary',
                'treatment_plan_summary',
                'is_active',
            ];

            // 建立「欄位名稱 → Excel 欄位字母」對照表
            $headerMap = [];

            foreach ($headerRow as $columnLetter => $headerValue) {
                // 去除標題前後空白並轉成小寫
                $header = strtolower(trim((string) $headerValue));

                // 移除可能出現在第一個標題前的 UTF-8 BOM
                $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

                // 只記錄有名稱的欄位
                if ($header !== '') {
                    $headerMap[$header] = $columnLetter;
                }
            }

            // 確認 Excel 沒有缺少系統欄位
            $missingHeaders = array_diff(
                $expectedHeaders,
                array_keys($headerMap)
            );

            // 欄位不完整時停止預覽
            if (! empty($missingHeaders)) {
                return redirect()
                    ->to('/clients/import')
                    ->with(
                        'error',
                        'Excel 缺少欄位：' . implode('、', $missingHeaders)
                    );
            }

            // 查出目前未軟刪除的案主編號
            $clientModel = new ClientModel();

            $existingClients = $clientModel
                ->select('id')
                ->findAll();

            // 建立案主編號查找表，方便檢查 s_num 是否存在
            $existingIds = [];

            foreach ($existingClients as $client) {
                $existingIds[(string) $client['id']] = true;
            }

            // 設定欄位最大長度
            $maxLengths = [
                'ct_name' => 100,
                'ct_addr' => 255,
                'region' => 100,
                'national_id' => 20,
                'sex' => 20,
                'mobile' => 30,
                'home_phone' => 30,
                'contact_person_1' => 255,
                'contact_person_2' => 255,
                'case_manager' => 100,
                'care_specialist' => 100,
                'home_service' => 100,
                'registered_address' => 255,
                'remittance_suffix' => 20,
                'living_status' => 100,
                'identity_type' => 255,
                'disability_status' => 100,
                'disability_level' => 100,
                'care_level' => 100,
            ];

            // 數值欄位必須是數字；空白仍允許
            $decimalFields = [
                'longitude',
                'latitude',
                'checkin_distance',
                'meal_box_price',
            ];

            // 準備預覽列、待確認匯入資料及錯誤列
            $previewRows = [];
            $importRows = [];
            $seenIds = [];
            $hasErrors = false;
            $totalRows = 0;

            // 逐列讀取案主資料
            foreach ($sheetRows as $rowIndex => $row) {
                // 依照標題對照表整理這一列的欄位資料
                $data = [];

                foreach ($expectedHeaders as $header) {
                    $columnLetter = $headerMap[$header];

                    $data[$header] = trim(
                        (string) ($row[$columnLetter] ?? '')
                    );
                }

                // 整列皆空白時略過
                if (implode('', $data) === '') {
                    continue;
                }

                // array_shift 後資料列索引從 0 開始，Excel 實際列號要加 2
                $excelRowNumber = (int) $rowIndex + 2;

                // 準備記錄這一列的錯誤
                $rowErrors = [];

                // s_num 必須是正整數
                if (
                    $data['s_num'] === ''
                    || ! ctype_digit($data['s_num'])
                    || (int) $data['s_num'] < 1
                ) {
                    $rowErrors[] = 's_num 必須是正整數';
                } else {
                    $clientId = (string) (int) $data['s_num'];

                    // 案主編號必須存在於未軟刪除的案主資料
                    if (! isset($existingIds[$clientId])) {
                        $rowErrors[] = '找不到此案主編號';
                    }

                    // 同一個 Excel 中不可重複出現相同案主編號
                    if (isset($seenIds[$clientId])) {
                        $rowErrors[] = '案主編號重複';
                    }

                    $seenIds[$clientId] = true;
                }

                // 檢查案主姓名
                if ($data['ct_name'] === '') {
                    $rowErrors[] = '案主姓名不可空白';
                }

                // 檢查案主地址
                if ($data['ct_addr'] === '') {
                    $rowErrors[] = '案主地址不可空白';
                }

                // 路線編號必須是整數
                if (
                    $data['route_no'] === ''
                    || filter_var($data['route_no'], FILTER_VALIDATE_INT) === false
                ) {
                    $rowErrors[] = '路線編號必須是整數';
                }

                // 檢查有字數限制的欄位
                foreach ($maxLengths as $field => $maxLength) {
                    if (mb_strlen($data[$field]) > $maxLength) {
                        $rowErrors[] = $field . '超過 ' . $maxLength . ' 個字';
                    }
                }

                // 生日必須是 YYYY-MM-DD 格式
                if ($data['birthday'] !== '') {
                    $birthday = \DateTime::createFromFormat(
                        '!Y-m-d',
                        $data['birthday']
                    );

                    if (
                        $birthday === false
                        || $birthday->format('Y-m-d') !== $data['birthday']
                    ) {
                        $rowErrors[] = 'birthday 必須使用 YYYY-MM-DD 格式';
                    }
                }

                // 經緯度、距離及餐盒單價必須是數字
                foreach ($decimalFields as $field) {
                    if (
                        $data[$field] !== ''
                        && ! is_numeric($data[$field])
                    ) {
                        $rowErrors[] = $field . ' 必須是數字';
                    }
                }

                // 空白狀態欄位使用資料表預設值
                if ($data['only_ot_case'] === '') {
                    $data['only_ot_case'] = '0';
                }

                if ($data['is_active'] === '') {
                    $data['is_active'] = '1';
                }

                // 狀態欄位只能是 0 或 1
                if (! in_array($data['only_ot_case'], ['0', '1'], true)) {
                    $rowErrors[] = 'only_ot_case 只能填 0 或 1';
                }

                if (! in_array($data['is_active'], ['0', '1'], true)) {
                    $rowErrors[] = 'is_active 只能填 0 或 1';
                }

                // 轉成匯入時可使用的資料結構
                $importRows[] = [
                    's_num' => (int) $data['s_num'],
                    'client' => [
                        'ct_name' => $data['ct_name'],
                        'ct_addr' => $data['ct_addr'],
                        'route_no' => (int) $data['route_no'],
                    ],
                    'profile' => $this->buildClientProfileImportData($data),
                ];

                // 準備交給預覽頁顯示的資料
                $previewRows[] = [
                    'rowNumber' => $excelRowNumber,
                    's_num' => $data['s_num'],
                    'ct_name' => $data['ct_name'],
                    'ct_addr' => $data['ct_addr'],
                    'route_no' => $data['route_no'],
                    'errorText' => implode('、', $rowErrors),
                ];

                // 只要有一列出錯，就不允許後續確認匯入
                if (! empty($rowErrors)) {
                    $hasErrors = true;
                }

                // 累計有內容的資料列
                $totalRows++;
            }

            // 沒有任何案主資料列時返回匯入頁
            if ($totalRows === 0) {
                return redirect()
                    ->to('/clients/import')
                    ->with('error', 'Excel 沒有可匯入的案主資料。');
            }

            // 先清除上一次預覽，避免沿用舊資料
            session()->remove('client_import_rows');

            // 只有全部資料都通過檢查時，才把匯入資料暫存在 Session
            if (! $hasErrors) {
                session()->set('client_import_rows', $importRows);
            }

            // 建立 Smarty 元件
            $smarty = new SmartyEngine();

            // 傳入預覽頁使用的網址
            $smarty->assign('cssUrl', base_url('css/style.css'));
            $smarty->assign('homeUrl', base_url('/'));
            $smarty->assign('clientListUrl', base_url('clients'));
            $smarty->assign('importUrl', base_url('clients/import'));

            // 傳入確認匯入表單的網址
            $smarty->assign(
                'confirmImportUrl',
                base_url('clients/import/confirm')
            );

            // 產生確認表單需要的 CSRF 欄位
            $smarty->assign('csrfField', csrf_field());

            // 傳入預覽內容及檢查狀態
            $smarty->assign('fileName', $file->getClientName());
            $smarty->assign('previewRows', $previewRows);
            $smarty->assign('totalRows', $totalRows);
            $smarty->assign('hasErrors', $hasErrors);

            // 執行 Smarty 預覽模板並取得 HTML
            $html = $smarty->fetch('clients_import_preview.tpl');

            // 回傳 UTF-8 預覽畫面
            return $this->response
                ->setContentType('text/html', 'UTF-8')
                ->setBody($html);
        
        } catch (Throwable $exception) {
            // 讀取失敗時返回匯入頁，不顯示系統內部錯誤細節
            return redirect()
                ->to('/clients/import')
                ->with('error', 'Excel 檔案讀取失敗，請確認檔案內容與格式。');
        }
    }

    // 將 Excel 的明細欄位整理成 client_profiles 可使用的資料
    private function buildClientProfileImportData(array $data): array
    {
        // 列出案主明細表中允許匯入的文字與數值欄位
        $fields = [
            'region',
            'national_id',
            'sex',
            'birthday',
            'mobile',
            'home_phone',
            'contact_person_1',
            'contact_person_2',
            'case_manager',
            'care_specialist',
            'home_service',
            'registered_address',
            'longitude',
            'latitude',
            'checkin_distance',
            'meal_box_price',
            'remittance_suffix',
            'living_status',
            'identity_type',
            'disability_status',
            'health_status',
            'disability_level',
            'disease_names',
            'care_level',
            'assessment_summary',
            'treatment_plan_summary',
        ];

        // 準備要交給 ClientProfileModel 的資料
        $profileData = [];

        // 將空白欄位轉成 null，其他欄位保留為文字
        foreach ($fields as $field) {
            $value = trim((string) ($data[$field] ?? ''));

            $profileData[$field] = $value === '' ? null : $value;
        }

        // 這兩個欄位在資料表中使用 0 或 1
        $profileData['only_ot_case'] = (int) ($data['only_ot_case'] ?? 0);
        $profileData['is_active'] = (int) ($data['is_active'] ?? 1);

        return $profileData;
    }

    // 使用者確認後，將預覽資料寫入資料庫
    public function confirmImport()
    {
        // 取得預覽階段暫存在 Session 的資料
        $importRows = session()->get('client_import_rows');

        // 沒有有效預覽資料時，不執行更新
        if (! is_array($importRows) || $importRows === []) {
            return redirect()
                ->to('/clients/import')
                ->with('error', '找不到有效的匯入預覽，請重新上傳 Excel。');
        }

        // 建立案主主資料 Model
        $clientModel = new ClientModel();

        // 建立案主詳細資料 Model
        $profileModel = new ClientProfileModel();

        // 取得資料庫連線，讓所有資料更新使用同一筆交易
        $db = \Config\Database::connect();

        // 開始資料庫交易
        $db->transBegin();

        try {
            // 逐筆更新預覽階段已通過檢查的資料
            foreach ($importRows as $row) {
                // 確認 Session 中每筆資料都有必要結構
                if (
                    ! isset($row['s_num'], $row['client'], $row['profile'])
                    || ! is_array($row['client'])
                    || ! is_array($row['profile'])
                ) {
                    throw new \RuntimeException('匯入資料結構不正確。');
                }

                // 取得要更新的案主編號
                $clientId = (int) $row['s_num'];

                // 再確認案主仍存在且未被軟刪除
                if ($clientId < 1 || $clientModel->find($clientId) === null) {
                    throw new \RuntimeException(
                        '案主編號 ' . $clientId . ' 已不存在，請重新上傳檔案。'
                    );
                }

                // 更新 clients 資料表中的案主基本資料
                if (! $clientModel->update($clientId, $row['client'])) {
                    throw new \RuntimeException(
                        '案主編號 ' . $clientId . ' 的基本資料更新失敗。'
                    );
                }

                // 查詢這位案主是否已有詳細資料
                $profile = $profileModel->find($clientId);

                if ($profile === null) {
                    // 尚無詳細資料時，新增一筆並設定關聯案主編號
                    $profileSaved = $profileModel->insert(
                        array_merge(
                            ['client_id' => $clientId],
                            $row['profile']
                        )
                    );
                } else {
                    // 已有詳細資料時，更新原本資料
                    $profileSaved = $profileModel->update(
                        $clientId,
                        $row['profile']
                    );
                }

                // 明細新增或更新失敗時，取消整批匯入
                if ($profileSaved === false) {
                    throw new \RuntimeException(
                        '案主編號 ' . $clientId . ' 的詳細資料更新失敗。'
                    );
                }
            }

            // 確認交易過程沒有資料庫錯誤
            if ($db->transStatus() === false) {
                throw new \RuntimeException('資料庫交易失敗。');
            }

            // 全部更新成功後提交交易
            $db->transCommit();

            // 清除已使用的預覽資料，避免重複送出
            session()->remove('client_import_rows');

            // 回到案主列表並顯示更新筆數
            return redirect()
                ->to('/clients')
                ->with('success', '案主資料匯入更新成功，共更新 ' . count($importRows) . ' 筆。');
        } catch (Throwable $exception) {
            // 任一筆失敗時回復整批更新
            $db->transRollback();

            // 將詳細錯誤寫入系統日誌，方便排查
            log_message(
                'error',
                '案主 Excel 匯入失敗：' . $exception->getMessage()
            );

            // 對使用者顯示簡短錯誤訊息
            return redirect()
                ->to('/clients/import')
                ->with('error', '匯入失敗，沒有完成整批更新。請重新預覽檔案。');
        }
    }
}
