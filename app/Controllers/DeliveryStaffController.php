<?php

namespace App\Controllers;

use App\Models\DeliveryStaffModel;
use CodeIgniter\Controller;

// 處理送餐員列表與新增功能
class DeliveryStaffController extends Controller
{
    // 顯示送餐員新增表單與列表
    public function index()
    {
        // 建立送餐員 Model，準備查詢資料
        $staffModel = new DeliveryStaffModel();

        // 查詢送餐員，最新建立的顯示在前面
        $staffList = $staffModel
            ->orderBy('id', 'DESC')
            ->findAll();

        // 載入畫面，並將查詢結果交給 View
        return view('delivery_staff/index', [
            'staffList' => $staffList,
        ]);
    }

    // 接收新增送餐員表單
    public function store()
    {
        // 接收姓名與電話，並移除前後空白
        $data = [
            'staff_name' => trim(
                (string) $this->request->getPost('staff_name')
            ),
            'phone' => trim(
                (string) $this->request->getPost('phone')
            ),
        ];

        // 設定表單驗證規則
        $rules = [
            'staff_name' => 'required|max_length[100]',
            'phone' => 'permit_empty|max_length[30]',
        ];

        // 設定欄位驗證失敗時顯示的中文訊息
        $messages = [
            'staff_name' => [
                'required' => '請輸入送餐員姓名。',
                'max_length' => '姓名不可超過 100 個字。',
            ],
            'phone' => [
                'max_length' => '電話不可超過 30 個字。',
            ],
        ];

        // 驗證失敗時回到表單，保留輸入內容與錯誤訊息
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 建立 Model，準備新增資料
        $staffModel = new DeliveryStaffModel();

        // 寫入資料庫；is_active 使用資料表預設值 1
        $staffModel->insert($data);

        // 新增成功後回到列表並顯示訊息
        return redirect()
            ->to('/delivery-staff')
            ->with('success', '送餐員新增成功。');
    }
}