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
            ->select(
                'delivery_staff.*,
                users.username AS app_account,
                GROUP_CONCAT(
                    delivery_routes.route_no
                    ORDER BY delivery_routes.route_no
                    SEPARATOR ", "
                ) AS route_numbers'
            )
            ->join(
                'users',
                'users.id = delivery_staff.user_id',
                'left'
            )
            ->join(
                'delivery_routes',
                'delivery_routes.delivery_staff_id = delivery_staff.id',
                'left'
            )
            ->groupBy('delivery_staff.id')
            ->orderBy('delivery_staff.id', 'DESC')
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
        $oldStaffInput = session()->getFlashdata('oldStaffInput') ?? [];
        $oldStaffInput = is_array($oldStaffInput) ? $oldStaffInput : [];

        $smarty->assign('errors', $errors);
        $smarty->assign('oldStaffName', $oldStaffInput['staff_name'] ?? '');
        $smarty->assign('oldUsername', $oldStaffInput['username'] ?? '');
        $smarty->assign('oldGender', $oldStaffInput['gender'] ?? '');
        $smarty->assign('oldAge', $oldStaffInput['age'] ?? '');
        $smarty->assign('oldPhone', $oldStaffInput['phone'] ?? '');
        $smarty->assign('oldAddress', $oldStaffInput['address'] ?? '');
        $smarty->assign('oldHireDate', $oldStaffInput['hire_date'] ?? '');
        $smarty->assign('errorMessage',session()->getFlashdata('error') ?? '');

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
        // 取得並整理表單資料
        $data = [
            'staff_name' => trim((string) $this->request->getPost('staff_name')),
            'username'   => strtoupper(trim((string) $this->request->getPost('username'))),
            'password'   => (string) $this->request->getPost('password'),
            'gender'     => trim((string) $this->request->getPost('gender')),
            'age'        => trim((string) $this->request->getPost('age')),
            'phone'      => trim((string) $this->request->getPost('phone')),
            'address'    => trim((string) $this->request->getPost('address')),
            'hire_date'  => trim((string) $this->request->getPost('hire_date')),
        ];

        // 帳號轉大寫後再次確認格式；密碼不放入舊輸入資料
        $oldStaffInput = $data;
        unset($oldStaffInput['password']);

        // 後端驗證不可省略，避免使用者繞過瀏覽器檢查
        $rules = [
            'staff_name' => 'required|max_length[100]',
            'username'   => 'required|regex_match[/^A[0-9]{2}-[0-9]{3}$/]|is_unique[users.username]',
            'password'   => 'required|min_length[8]|max_length[255]',
            'gender'     => 'permit_empty|in_list[男,女,其他]',
            'age'        => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[120]',
            'phone'      => 'permit_empty|max_length[30]',
            'address'    => 'permit_empty|max_length[255]',
            'hire_date'  => 'permit_empty|valid_date[Y-m-d]',
        ];

        // 驗證失敗時顯示中文提示
        $messages = [
            'staff_name' => [
                'required'   => '請輸入送餐員姓名。',
                'max_length' => '姓名不可超過 100 個字。',
            ],
            'username' => [
                'required'    => '請輸入 APP 帳號。',
                'regex_match' => 'APP 帳號格式須為 A 加兩位數字、連字號、三位數字，例如 A10-111。',
                'is_unique'   => '這個 APP 帳號已經有人使用。',
            ],
            'password' => [
                'required'   => '請輸入初始密碼。',
                'min_length' => '初始密碼至少需要 8 個字元。',
                'max_length' => '初始密碼不可超過 255 個字元。',
            ],
            'gender' => [
                'in_list' => '請選擇有效的性別。',
            ],
            'age' => [
                'integer'                => '年齡必須是整數。',
                'greater_than_equal_to'  => '年齡不可小於 0。',
                'less_than_equal_to'     => '年齡不可大於 120。',
            ],
            'phone' => [
                'max_length' => '手機號碼不可超過 30 個字元。',
            ],
            'address' => [
                'max_length' => '地址不可超過 255 個字元。',
            ],
            'hire_date' => [
                'valid_date' => '到職日格式不正確。',
            ],
        ];

        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->to('/delivery_staff/create')
                ->with('errors', $this->validator->getErrors())
                ->with('oldStaffInput', $oldStaffInput);
        }

        $db = db_connect();
        $staffModel = new DeliveryStaffModel();

        // 用交易確保 users 和 delivery_staff 同時成功或同時取消
        $db->transBegin();

        $db->table('users')->insert([
            'username'      => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'display_name'  => $data['staff_name'],
            'role'          => 'staff',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($db->transStatus() === false) {
            $db->transRollback();

            return redirect()
                ->to('/delivery_staff/create')
                ->with('error', '登入帳號建立失敗，請確認 APP 帳號是否已使用。')
                ->with('oldStaffInput', $oldStaffInput);
        }

        // 取得剛建立的 users.id，作為送餐員帳號關聯
        $userId = $db->insertID();

        // 空白的可選欄位存入資料庫時改為 NULL
        $staffData = [
            'user_id'   => $userId,
            'staff_name' => $data['staff_name'],
            'gender'     => $data['gender'] !== '' ? $data['gender'] : null,
            'age'        => $data['age'] !== '' ? $data['age'] : null,
            'phone'      => $data['phone'] !== '' ? $data['phone'] : null,
            'address'    => $data['address'] !== '' ? $data['address'] : null,
            'hire_date'  => $data['hire_date'] !== '' ? $data['hire_date'] : null,
            'is_active'  => 1,
        ];

        if (! $staffModel->insert($staffData) || $db->transStatus() === false) {
            $db->transRollback();

            return redirect()
                ->to('/delivery_staff/create')
                ->with('error', '送餐員資料新增失敗，請稍後再試。')
                ->with('oldStaffInput', $oldStaffInput);
        }

        $db->transCommit();

        return redirect()
            ->to('/delivery_staff')
            ->with('success', '送餐員與登入帳號新增成功。');
    }

    // 顯示指定送餐員的修改表單
    public function edit(int $id)
    {
        $staffModel = new DeliveryStaffModel();

        // 查詢送餐員資料，並讀取關聯的 APP 登入帳號
        $staff = $staffModel
            ->select('delivery_staff.*, users.username AS app_account')
            ->join(
                'users',
                'users.id = delivery_staff.user_id',
                'left'
            )
            ->find($id);

        // 找不到送餐員資料時顯示 404
        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound(
                '找不到指定送餐員'
            );
        }

        // 查詢尚未指派，或目前已指派給這位送餐員的餐線
        $routes = db_connect()
            ->table('delivery_routes')
            ->select('id, route_no, delivery_staff_id')
            ->groupStart()
                ->where('delivery_staff_id', null)
                ->orWhere('delivery_staff_id', $id)
            ->groupEnd()
            ->orderBy('route_no', 'ASC')
            ->get()
            ->getResultArray();

        // 整理目前已指派給這位送餐員的餐線資料編號
        $selectedRouteIds = [];

        foreach ($routes as $route) {
            if ((int) ($route['delivery_staff_id'] ?? 0) === $id) {
                $selectedRouteIds[] = (int) $route['id'];
            }
        }

        // 將送餐員、餐線清單和目前選取的餐線交給修改頁
        return view('delivery_staffs/edit', [
            'staff'            => $staff,
        ]);
    }

    // 接收並更新指定送餐員資料
    public function update(int $id)
    {
        $staffModel = new DeliveryStaffModel();
        $staff = $staffModel->find($id);

        if ($staff === null) {
            throw PageNotFoundException::forPageNotFound('找不到指定送餐員');
        }

        // 修改 APP 帳號需要找到關聯的 users 資料
        if (empty($staff['user_id'])) {
            return redirect()
                ->to('/delivery_staff/' . $id . '/edit')
                ->with('error', '這位送餐員尚未連結登入帳號，無法修改 APP 帳號。');
        }

        // 整理表單資料；APP 帳號統一轉成大寫
        $data = [
            'user_id'   => (int) $staff['user_id'],
            'username'  => strtoupper(trim((string) $this->request->getPost('username'))),
            'staff_name' => trim((string) $this->request->getPost('staff_name')),
            'password'  => (string) $this->request->getPost('password'),
            'gender'    => trim((string) $this->request->getPost('gender')),
            'age'       => trim((string) $this->request->getPost('age')),
            'phone'     => trim((string) $this->request->getPost('phone')),
            'address'   => trim((string) $this->request->getPost('address')),
            'hire_date' => trim((string) $this->request->getPost('hire_date')),
            'is_active' => trim((string) $this->request->getPost('is_active')),
        ];

        // 驗證基本欄位；帳號唯一性檢查會排除這位送餐員目前使用的帳號
        $rules = [
            'user_id' => 'required|integer',
            'username' => 'required|regex_match[/^A[0-9]{2}-[0-9]{3}$/]|is_unique[users.username,id,{user_id}]',
            'staff_name' => 'required|max_length[100]',
            'password' => 'permit_empty|min_length[8]|max_length[255]',
            'gender' => 'permit_empty|in_list[男,女,其他]',
            'age' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[120]',
            'phone' => 'permit_empty|max_length[30]',
            'address' => 'permit_empty|max_length[255]',
            'hire_date' => 'permit_empty|valid_date[Y-m-d]',
            'is_active' => 'required|in_list[0,1]',
        ];

        $messages = [
            'username' => [
                'required' => '請輸入 APP 帳號。',
                'regex_match' => 'APP 帳號格式須為 A10-111 這種格式。',
                'is_unique' => '這個 APP 帳號已經有人使用。',
            ],
            'staff_name' => [
                'required' => '請輸入送餐員姓名。',
                'max_length' => '姓名不可超過 100 個字。',
            ],
            'password' => [
                'min_length' => '新密碼至少需要 8 個字元。',
                'max_length' => '新密碼不可超過 255 個字元。',
            ],
            'gender' => [
                'in_list' => '請選擇有效的性別。',
            ],
            'age' => [
                'integer' => '年齡必須是整數。',
                'greater_than_equal_to' => '年齡不可小於 0。',
                'less_than_equal_to' => '年齡不可大於 120。',
            ],
            'phone' => [
                'max_length' => '手機號碼不可超過 30 個字元。',
            ],
            'address' => [
                'max_length' => '地址不可超過 255 個字元。',
            ],
            'hire_date' => [
                'valid_date' => '到職日格式不正確。',
            ],
            'is_active' => [
                'required' => '請選擇送餐員啟用狀態。',
                'in_list' => '送餐員啟用狀態資料不正確。',
            ],
        ];

        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->to('/delivery_staff/' . $id . '/edit')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // route_ids[] 是多選欄位；未勾選任何路線時會是空陣列
        $routeIds = $this->request->getPost('route_ids') ?? [];

        if (! is_array($routeIds)) {
            return redirect()
                ->to('/delivery_staff/' . $id . '/edit')
                ->withInput()
                ->with('error', '路線資料格式不正確。');
        }

        // 確認選取的路線目前未指派，或原本就屬於這位送餐員
        $availableRoutes = db_connect()
            ->table('delivery_routes')
            ->select('id')
            ->groupStart()
                ->where('delivery_staff_id', null)
                ->orWhere('delivery_staff_id', $id)
            ->groupEnd()
            ->get()
            ->getResultArray();

        $availableRouteIds = array_map(
            'intval',
            array_column($availableRoutes, 'id')
        );

        foreach ($routeIds as $routeId) {
            if (! ctype_digit((string) $routeId)
                || ! in_array((int) $routeId, $availableRouteIds, true)
            ) {
                return redirect()
                    ->to('/delivery_staff/' . $id . '/edit')
                    ->withInput()
                    ->with('error', '所選路線無法指派，請重新選擇。');
            }
        }

        $routeIds = array_values(array_unique(array_map('intval', $routeIds)));

        $db = db_connect();
        $db->transBegin();

        // 更新登入帳號名稱；有填新密碼時才更新密碼
        $userData = [
            'username' => $data['username'],
            'display_name' => $data['staff_name'],
        ];

        if ($data['password'] !== '') {
            $userData['password_hash'] = password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            );
        }

        $db->table('users')
            ->where('id', $staff['user_id'])
            ->update($userData);

        // 更新送餐員個人資料
        $staffData = [
            'staff_name' => $data['staff_name'],
            'gender' => $data['gender'] !== '' ? $data['gender'] : null,
            'age' => $data['age'] !== '' ? (int) $data['age'] : null,
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'address' => $data['address'] !== '' ? $data['address'] : null,
            'hire_date' => $data['hire_date'] !== '' ? $data['hire_date'] : null,
            'is_active' => (int) $data['is_active'],
        ];

        $staffUpdated = $staffModel->update($id, $staffData);

        // 先清除這位送餐員原本的路線，再套用表單中勾選的路線
        $db->table('delivery_routes')
            ->where('delivery_staff_id', $id)
            ->update(['delivery_staff_id' => null]);

        if (! empty($routeIds)) {
            $db->table('delivery_routes')
                ->whereIn('id', $routeIds)
                ->update(['delivery_staff_id' => $id]);
        }

        if (! $staffUpdated || $db->transStatus() === false) {
            $db->transRollback();

            return redirect()
                ->to('/delivery_staff/' . $id . '/edit')
                ->withInput()
                ->with('error', '送餐員資料更新失敗，請稍後再試。');
        }

        $db->transCommit();

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