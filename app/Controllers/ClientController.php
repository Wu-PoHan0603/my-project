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

        // 查詢 clients 資料表中的所有案主
        // orderBy('id', 'DESC')：按照 id 由大到小排列
        // findAll()：取得所有尚未被軟刪除的資料
        $clients = $clientModel
            ->orderBy('id', 'DESC')
            ->findAll();

        // 準備傳入 View 的資料
        // 陣列鍵 clients 會變成 View 裡的 $clients
        $data = [
            'clients' => $clients,
        ];

        // 載入 app/Views/clients/index.php
        // 同時將 $data 傳給 View
        return view('clients/index', $data);
    }

    public function create()
    {
        return view('clients/create');
    }

    public function store()
    {
        $data = [
            'ct_name' => $this->request->getPost('ct_name'),
            'ct_address' => $this->request->getPost('ct_address'),
            'route_no' => $this->request->getPost('route_no'),
        ];
        
        //建立Model物件
        $clientModel = new ClientModel();
        //執行新增
        $clientModel->insert($data);
        //新增後重新導向
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
        
}
