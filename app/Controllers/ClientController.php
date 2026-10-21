<?php

namespace App\Controllers;

use App\Controllers\BaseController;
//讓 ClientController 可以使用 ClientModel。
use App\Models\ClientModel;

// 引用案主詳細資料 Model
use App\Models\ClientProfileModel;

// 引用案主附件 Model
use App\Models\ClientAttachmentModel;

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
}
