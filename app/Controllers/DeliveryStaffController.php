<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\DeliveryStaffModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

// 處理送餐員列表、新增、詳細資料、修改與刪除功能
class DeliveryStaffController extends Controller
{
    // 顯示送餐員列表
    public function index()
    {
        // 建立送餐員 Model，準備查詢資料
        $staffModel = new DeliveryStaffModel();

        // 查詢送餐員，最新建立的資料顯示在前面
        $staffList = $staffModel
            ->orderBy('id', 'DESC')
            ->findAll();

        // 取得欄位驗證錯誤訊息
        $errors = session()->getFlashdata('errors') ?? [];

        // 確認錯誤訊息是陣列
        $errors = is_array($errors) ? $errors : [];

        // 取得新增或修改成功訊息
        $successMessage = session()->getFlashdata('success') ?? '';

        // 取得一般操作錯誤訊息
        $errorMessage = session()->getFlashdata('error') ?? '';

        // 建立 Smarty 元件
        $smarty = new SmartyEngine();

        // 傳入共用網址、登入者資訊和操作權限
        $this->assignCommonData($smarty);

        // 傳入刪除表單需要的 CSRF 欄位
        $smarty->assign('csrfField', csrf_field());

        // 傳入列表頁需要的資料與訊息
        $smarty->assign('staffList', $staffList);
        $smarty->assign('errors', $errors);
        $smarty->assign('successMessage', $successMessage);
        $smarty->assign('errorMessage', $errorMessage);

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
        // 取得欄位驗證錯誤訊息
        $errors = session()->getFlashdata('errors') ?? [];

        // 確認錯誤訊息是陣列
        $errors = is_array($errors) ? $errors : [];

        // 建立 Smarty 元件
        $smarty = new SmartyEngine();

        // 傳入共用網址、登入者資訊和操作權限
        $this->assignCommonData($smarty);

        // 傳入新增頁需要的錯誤訊息與舊輸入資料
        $smarty->assign('errors', $errors);
        $smarty->assign('oldStaffName', old('staff_name', ''));
        $smarty->assign('oldPhone', old('phone', ''));
        $smarty->assign('oldAddress', old('address', ''));

        // 產生新增表單需要的 CSRF 欄位
        $smarty->assign('csrfField', csrf_field());

        // 執行新增頁 Smarty 模板並取得 HTML
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

        // 設定驗證失敗時顯示的中文訊息
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

        // 驗證失敗時回到新增頁，保留輸入內容和錯誤訊息
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->to('/delivery_staff/create')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 建立 Model，準備新增資料
        $staffModel = new DeliveryStaffModel();

        // 將姓名、電話與地址寫入資料庫
        if (! $staffModel->insert($data)) {
            return redirect()
                ->to('/delivery_staff/create')
                ->withInput()
                ->with('error', '送餐員新增失敗，請稍後再試。');
        }

        // 新增成功後回到列表並顯示成功訊息
        return redirect()
            ->to('/delivery_staff')
            ->with('success', '送餐員新增成功。');
    }

    // 顯示指定送餐員的詳細資料
    public function detail(int $id)
    {
        // 建立 Model，查詢指定送餐員
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($id);

        // 找不到資料時顯示 404
        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound(
                '找不到指定送餐員'
            );
        }

        // 載入詳細資料畫面並傳入送餐員資料
        return view('delivery_staffs/detail', [
            'staff' => $staff,
        ]);
    }

    // 顯示指定送餐員的修改表單
    public function edit(int $id)
    {
        // 建立 Model，查詢指定送餐員
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($id);

        // 找不到資料時顯示 404
        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound(
                '找不到指定送餐員'
            );
        }

        // 載入修改表單並傳入送餐員資料
        return view('delivery_staffs/edit', [
            'staff' => $staff,
        ]);
    }

    // 接收並更新指定送餐員資料
    public function update(int $id)
    {
        // 建立 Model，確認送餐員存在
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($id);

        // 找不到資料時顯示 404
        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound(
                '找不到指定送餐員'
            );
        }

        // 取得表單送來的地址
        $postedAddress = $this->request->getPost('address');

        // 整理要更新的資料
        // 表單未送出地址欄位時，保留資料庫中的原地址
        $data = [
            'staff_name' => trim(
                (string) $this->request->getPost('staff_name')
            ),
            'phone' => trim(
                (string) $this->request->getPost('phone')
            ),
            'address' => trim(
                (string) ($postedAddress ?? ($staff['address'] ?? ''))
            ),
            'is_active' => trim(
                (string) $this->request->getPost('is_active')
            ),
        ];

        // 設定表單驗證規則
        $rules = [
            'staff_name' => 'required|max_length[100]',
            'phone' => 'permit_empty|max_length[30]',
            'address' => 'permit_empty|max_length[255]',
            'is_active' => 'required|in_list[0,1]',
        ];

        // 設定驗證失敗時顯示的中文訊息
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
            'is_active' => [
                'required' => '請選擇送餐員啟用狀態。',
                'in_list' => '送餐員啟用狀態資料不正確。',
            ],
        ];

        // 驗證失敗時回到修改頁，保留輸入內容與錯誤訊息
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->to('/delivery_staff/' . $id . '/edit')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 更新資料庫中的送餐員資料
        if (! $staffModel->update($id, $data)) {
            return redirect()
                ->to('/delivery_staff/' . $id . '/edit')
                ->withInput()
                ->with('error', '送餐員資料更新失敗。');
        }

        // 更新成功後回到列表並顯示成功訊息
        return redirect()
            ->to('/delivery_staff')
            ->with('success', '送餐員資料更新成功。');
    }

    // 永久刪除指定送餐員
    public function delete(int $id)
    {
        // 建立 Model，確認送餐員存在
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($id);

        // 找不到資料時顯示 404
        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound(
                '找不到指定送餐員'
            );
        }

        // 刪除送餐員資料
        // 資料表外鍵會將相關路線的送餐員指派清空
        if (! $staffModel->delete($id)) {
            return redirect()
                ->to('/delivery_staff')
                ->with('error', '送餐員刪除失敗。');
        }

        // 刪除成功後回到列表並顯示訊息
        return redirect()
            ->to('/delivery_staff')
            ->with('success', '送餐員已刪除。');
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

        // 取得登入帳號；沒有帳號時顯示提示文字
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

        // 管理員與工作人員可以操作送餐員資料
        $canManageStaff = in_array(
            $currentRole,
            ['admin', 'staff'],
            true
        );

        // 傳入側欄個人設定需要顯示的登入者資訊
        $smarty->assign('currentUsername', $currentUsername);
        $smarty->assign('currentDisplayName', $currentDisplayName);
        $smarty->assign('currentRoleLabel', $currentRoleLabel);

        // 傳入模板，用來控制送餐員管理按鈕是否顯示
        $smarty->assign('canManageStaff', $canManageStaff);

        // 產生登出表單需要的 CSRF 欄位
        $smarty->assign('logoutCsrfField', csrf_field());
    }
}