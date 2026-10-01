<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    // 顯示登入頁
    public function login()
    {
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

        // 登入成功後，將使用者 id 存進 Session
        session()->regenerate();
        session()->set([
            'user_id'  => $user['id'],
            'username' => $user['username'],
            'isLoggedIn' => true,
        ]);

        // 成功後回到案主資料列表
        return redirect()->to('/clients');
    }
}