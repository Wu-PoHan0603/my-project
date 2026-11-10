<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\DeliveryStaffModel;
use CodeIgniter\Controller;

// 處理送餐員列表、新增頁面與新增資料
class DeliveryStaffController extends Controller
{
    // 顯示送餐員列表
    public function index()
    {
        // 建立送餐員 Model，準備查詢資料
        $staffModel = new DeliveryStaffModel();

        // 查詢送餐員，最新建立的顯示在前面
        $staffList = $staffModel
            ->orderBy('id', 'DESC')
            ->findAll();

        // 取得驗證錯誤訊息
        $errors = session()->getFlashdata('errors') ?? [];

        // 確保錯誤訊息是陣列
        $errors = is_array($errors) ? $errors : [];

        // 取得新增成功訊息
        $successMessage = session()->getFlashdata('success') ?? '';

        // 建立 Smarty 元件
        $smarty = new SmartyEngine();

        // 傳入側邊欄、網址與登入者資訊
        $this->assignCommonData($smarty);

        // 傳入列表中刪除表單需要的 CSRF 欄位
        $smarty->assign('csrfField', csrf_field());

        // 傳入送餐員列表頁需要的資料
        $smarty->assign('staffList', $staffList);
        $smarty->assign('errors', $errors);
        $smarty->assign('successMessage', $successMessage);

        // 執行 Smarty 模板並取得 HTML
        $html = $smarty->fetch('delivery_staff_index.tpl');

        // 回傳 UTF-8 HTML 頁面
        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    // 顯示獨立的新增送餐員頁面
    public function create()
    {
        // 取得欄位驗證錯誤
        $errors = session()->getFlashdata('errors') ?? [];

        // 確保錯誤訊息是陣列
        $errors = is_array($errors) ? $errors : [];

        // 建立 Smarty 元件
        $smarty = new SmartyEngine();

        // 傳入側邊欄、網址與登入者資訊
        $this->assignCommonData($smarty);

        // 傳入新增頁需要的資料
        $smarty->assign('errors', $errors);
        $smarty->assign('oldStaffName', old('staff_name', ''));
        $smarty->assign('oldPhone', old('phone', ''));
        $smarty->assign('oldAddress', old('address', ''));

        // 產生新增表單需要的 CSRF 欄位
        $smarty->assign('csrfField', csrf_field());

        // 執行新增頁 Smarty 模板
        $html = $smarty->fetch('delivery_staff_create.tpl');

        // 回傳 UTF-8 HTML 頁面
        return $this->response
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    // 接收並儲存新增送餐員表單
    public function store()
    {
        // 接收表單資料，並移除前後空白
        $data = [
            'staff_name' => trim(
                (string) $this->request->getPost('staff_name')
            ),
            'phone' => trim(
                (string) $this->request->getPost('phone')
            ),
            'address' => trim(
                (string) $this->request->getPost('address')
            ),
        ];

        // 設定表單驗證規則
        $rules = [
            'staff_name' => 'required|max_length[100]',
            'phone' => 'permit_empty|max_length[30]',
            'address' => 'permit_empty|max_length[255]',
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
            'address' => [
                'max_length' => '地址不可超過 255 個字。',
            ],
        ];

        // 驗證失敗時回到新增頁，保留欄位內容與錯誤訊息
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->to('/delivery_staff/create')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 建立 Model，準備新增資料
        $staffModel = new DeliveryStaffModel();

        // 將姓名、電話與地址寫入資料庫
        $staffModel->insert($data);

        // 新增成功後回到列表並顯示成功訊息
        return redirect()
            ->to('/delivery_staff')
            ->with('success', '送餐員新增成功。');
    }

    // 將共用資料交給 Smarty 模板
    private function assignCommonData(SmartyEngine $smarty): void
    {
        // 傳入共用 CSS 網址
        $smarty->assign('cssUrl', base_url('css/style.css'));

        // 傳入側邊欄和頁面導覽使用的網址
        $smarty->assign('homeUrl', base_url('/'));
        $smarty->assign('clientListUrl', base_url('clients'));
        $smarty->assign('staffListUrl', base_url('delivery_staff'));
        $smarty->assign('createStaffUrl', base_url('delivery_staff/create'));
        $smarty->assign('trashUrl', base_url('clients/trash'));
        $smarty->assign('deliveryRoutesUrl', base_url('delivery-routes'));
        $smarty->assign('changePasswordUrl', base_url('change-password'));
        $smarty->assign('logoutUrl', base_url('logout'));

        // 取得登入帳號；沒有帳號時使用提示文字
        $currentUsername = session()->get('username') ?? '未取得帳號';

        // 取得顯示名稱；沒有設定時使用登入帳號
        $currentDisplayName = session()->get('display_name')
            ?: $currentUsername;

        // 將角色代碼轉成中文名稱
        $roleLabels = [
            'admin'  => '系統管理員',
            'staff'  => '工作人員',
            'viewer' => '唯讀使用者',
        ];

        // 取得目前角色及對應中文名稱
        $currentRole = session()->get('role') ?? '';
        $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';

        // 傳入側欄個人設定需要顯示的登入者資訊
        $smarty->assign('currentUsername', $currentUsername);
        $smarty->assign('currentDisplayName', $currentDisplayName);
        $smarty->assign('currentRoleLabel', $currentRoleLabel);

        // 產生登出表單需要的 CSRF 欄位
        $smarty->assign('logoutCsrfField', csrf_field());
    }
}