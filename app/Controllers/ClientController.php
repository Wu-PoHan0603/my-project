<?php

namespace App\Controllers;

use App\Controllers\BaseController;
//讓 ClientController 可以使用 ClientModel。
use App\Models\ClientModel;
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

        // 取代原本的findAll()，改成每頁顯示5筆
        $pageSize = 5;
        $clients = $clientModel
            ->orderBy('id', 'DESC')
            ->paginate($pageSize, 'clients');

        return view('clients/index',[
            'clients' => $clients,
            'keyword' => $keyword,
            'pager' => $clientModel->pager,
        ]);
    }

    public function create()
    {
        return view('clients/create');
    }

    public function store()
    {
        $data = [
            'ct_name' => trim((string) $this->request->getPost('ct_name')),
            'ct_address' => trim((string) $this->request->getPost('ct_address')),
            'route_no' => trim((string) $this->request->getPost('route_no')),
        ];

        // 設定每個欄位的錯誤訊息
        // 設定每個欄位的驗證規則與畫面標籤
        $rules = [
            'ct_name' => [
                'label' => '案主姓名',
                'rules' => 'required|min_length[2]|max_length[100]',
            ],
            'ct_address' => [
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
            'ct_address' => [
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
            (string) $this->request->getPost('ct_address')
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
            'ct_address' => $ctAddress,
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
        //因為Model已啟用useSoftDeletes
        //所以這邊只會寫入delete_at
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

        // 1.onlyDeleted() 只查詢已軟刪除的資料 也就是 deleted_at 有刪除時間的資料
        // 2.orderBy() 依照刪除時間倒序排列最新刪除的資料會顯示在最上方
        // 3.findAll() 執行查詢並取得全部結果
        $deletedClients = $clientModel
            ->onlyDeleted()
            ->orderBy('deleted_at', 'DESC')
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
        $deletedClients = $clientModel
            ->onlyDeleted()
            ->findAll($id);

        //如果回收桶裡找不到這筆資料
        if (null === $deletedClients) {
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
        //這會真正移除資料列,不是更新deleted_at
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
}
