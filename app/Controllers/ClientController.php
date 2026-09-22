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
    
}
