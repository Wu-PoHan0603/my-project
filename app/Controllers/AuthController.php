<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    // 顯示登入頁
    public function login()
    {
        // 已登入就直接回案主列表，不再顯示登入表單
        if (session()->get('isLoggedIn') === true) {
            return redirect()->to('/clients');
        }

        // 未登入才顯示登入頁
        return view('auth/login');
    }

    // 接收並驗證登入表單
    public function attemptLogin()
    {
        // 取得使用者輸入的帳號與密碼
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        // 用帳號查詢 users 資料表
        $userModel = new UserModel();
        $user = $userModel
            ->where('username', $username)
            ->first();

        // 帳號不存在，或密碼和雜湊不相符，就拒絕登入
        if (
            $user === null
            || ! password_verify($password, $user['password_hash'])
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '帳號或密碼錯誤');
        }

        // 登入成功後更新 Session ID，降低 Session 固定攻擊的風險
        session()->regenerate(true);
        session()->set([
            'user_id'  => $user['id'],
            'username' => $user['username'],
            'isLoggedIn' => true,

            // 新增：側欄要顯示的名稱；名稱空白時使用帳號名稱
            'display_name' => $user['display_name'] ?: $user['username'],

            // 新增：從 users 資料表讀取角色代碼
            'role' => $user['role'],
        ]);

        // 成功後回到案主資料列表
        return redirect()->to('/clients');
    }

    // 清除登入 Session，然後回到登入頁
    public function logout()
    {
        session()->destroy();

        return redirect()
            ->to('/login')
            ->with('success', '已成功登出');
    }

    // 顯示修改密碼表單
    public function changePassword()
    {
        return view('auth/change_password');
    }

    // 接收表單並更新密碼
    public function updatePassword()
    {
        // 取得表單資料
        // 密碼不要 trim，因為空白可能是密碼的一部分
        $data = [
            'current_password' => (string) $this->request->getPost('current_password'),
            'new_password'     => (string) $this->request->getPost('new_password'),
            'confirm_password' => (string) $this->request->getPost('confirm_password'),
        ];

        // 設定伺服器端驗證規則
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        // 設定中文錯誤訊息
        $messages = [
            'current_password' => [
                'required' => '請輸入目前密碼。',
            ],
            'new_password' => [
                'required'   => '請輸入新密碼。',
                'min_length' => '新密碼至少需要 8 個字元。',
            ],
            'confirm_password' => [
                'required' => '請再次輸入新密碼。',
                'matches'  => '兩次輸入的新密碼不一致。',
            ],
        ];

        // 驗證失敗就返回表單並顯示錯誤
        // 不保留密碼欄位內容，避免密碼被重新填回網頁
        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()
                ->back()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();

        // 檢查 Session 是否有使用者編號
        $userId = session()->get('user_id');

        if (empty($userId)) {
            // Session 資料不完整時，清除登入狀態並回登入頁
            session()->destroy();

            return redirect()->to('/login');
        }

        // 依照目前登入者的 id 查詢 users 資料表
        $user = $userModel->find((int) $userId);

        if ($user === null) {
            // 找不到這個使用者時，清除登入狀態
            session()->destroy();

            return redirect()->to('/login');
        }

        // 用表單輸入的目前密碼，比對資料庫裡的密碼雜湊
        if (! password_verify($data['current_password'], $user['password_hash'])) {
            return redirect()
                ->back()
                ->with('error', '目前密碼不正確。');
        }

        // 將新密碼轉成雜湊字串，不直接儲存明文密碼
        $newPasswordHash = password_hash(
            $data['new_password'],
            PASSWORD_DEFAULT
        );

        // 只更新目前登入使用者的 password_hash 欄位
        $userModel->update((int) $userId, [
            'password_hash' => $newPasswordHash,
        ]);

        // 密碼更新後重新產生 Session ID
        session()->regenerate(true);

        // 回案主列表並顯示成功訊息
        return redirect()
            ->to('/clients')
            ->with('success', '密碼修改成功，請使用新密碼登入。');
    }
}