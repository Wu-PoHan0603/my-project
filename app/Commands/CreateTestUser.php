<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateTestUser extends BaseCommand
{
    // 在終端機輸入此名稱執行指令
    protected $group = 'App';
    protected $name = 'user:create-test';
    protected $description = '建立一個測試登入帳號';

    public function run(array $params)
    {
        // 測試用帳號與密碼
        $username = 'admin';
        $plainPassword = 'Test1234!';

        // 檢查帳號是否已存在，避免重複新增
        $userModel = new UserModel();
        $existingUser = $userModel
            ->where('username', $username)
            ->first();

        if ($existingUser !== null) {
            CLI::write('測試帳號已存在，沒有新增。', 'yellow');
            return;
        }

        // 將原始密碼轉成雜湊字串
        $passwordHash = password_hash(
            $plainPassword,
            PASSWORD_DEFAULT
        );

        // 將帳號與雜湊後的密碼寫入資料庫
        $userModel->insert([
            'username'      => $username,
            'password_hash' => $passwordHash,
        ]);

        CLI::write('測試帳號建立成功。', 'green');
        CLI::write('帳號：admin');
        CLI::write('密碼：Test1234!');
    }
}