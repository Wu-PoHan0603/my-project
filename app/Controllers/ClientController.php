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
        return view('clients/index');
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
