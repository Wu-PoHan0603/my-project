<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index() {
        
        // 使用台灣時區 UTC
        // format('G') 會取得0~23的小時
        $hour = (int) \CodeIgniter\I18n\Time::now('Asia/Taipei')->format('G');

        //依照台灣時間決定首頁顯示問候語
        if ($hour >= 5 && $hour < 12) {
            $greeting = '早上好';
        }elseif ($hour >= 12 && $hour < 18) {
            $greeting = '中午好';
        }else {
            $greeting = '晚上好';
        }

        // 取得登入帳號；Session 沒有帳號時顯示預設文字。
        $currentUsername = session()->get('username') ?? '未取得帳號';

        // 有設定顯示名稱就使用它，否則改用登入帳號。
        $currentDisplayName = session()->get('display_name') ?: $currentUsername;

        // 將角色代碼轉換成首頁要顯示的中文名稱。
        $roleLabels = [
            'admin' => '系統管理員',
            'staff' => '工作人員',
            'viewer' => '唯獨使用者',
        ];

        // 取得登入者角色；沒有設定時使用空字串。
        $currentRole = session()->get('role') ?? '';

        // 找不到角色對應名稱時，顯示'未設定角色'。
        $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';

        // 將資料交給下一步建立的首頁View使用。
        return view('home/index', [
            'greeting' => $greeting,
            'username' => $currentUsername,
            'displayName' => $currentDisplayName,
            'roleLabel' => $currentRoleLabel,
        ]);
    }
}
