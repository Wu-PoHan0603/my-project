<?php /** @var int|null $route_no */ ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增案主結果</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">    
</head>

<body>
    <div class="layout">

        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <a href="#">首頁</a>
                <a href="<?= base_url('clients') ?>">案主資料</a>
                <a href="<?= base_url('clients/create') ?>">新增案主</a>
                <a href="<?php base_url('clients/trash') ?>">資源回收桶</a>
            </nav>
            <?php
            // 從登入 Session 取得顯示名稱；沒有時退回帳號名稱
            $currentDisplayName = session()->get('display_name')
                ?: session()->get('username')
                ?: '使用者';

            // 將資料庫角色代碼轉成畫面上的中文名稱
            $roleLabels = [
                'admin'  => '系統管理員',
                'staff'  => '工作人員',
                'viewer' => '唯讀使用者',
            ];

            // 找不到角色代碼時顯示預設文字
            $currentRole = session()->get('role') ?? '';
            $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';
            ?>

            <!-- 這個區塊放在側欄底部，顯示登入者資訊和操作 -->
            <div class="sidebar-footer">

                <!-- 顯示登入者名稱與角色 -->
                <div class="sidebar-account">
                    <p class="sidebar-account-name">
                        <?= esc($currentDisplayName) ?>
                    </p>

                    <p class="sidebar-account-role">
                        <?= esc($currentRoleLabel) ?>
                    </p>
                </div>

                <!-- 個人設定目前連到已完成的更新密碼頁面 -->
                <a
                    href="<?= base_url('change-password') ?>"
                    class="sidebar-settings-link"
                >
                    個人設定／更新密碼
                </a>

                <!-- 登出會改變登入狀態，所以使用 POST 表單 -->
                <form
                    action="<?= base_url('logout') ?>"
                    method="post"
                    class="sidebar-logout-form"
                >
                    <!-- 產生 CSRF Token，讓 CSRF Filter 驗證登出請求 -->
                    <?= csrf_field() ?>

                    <!-- 送出登出請求 -->
                    <button type="submit" class="sidebar-logout-button">
                        登出
                    </button>
                </form>
            </div>
        </aside>

        <main class="content">
            <h1>接收到的案主資料</h1>

            <p>案主姓名：<?= esc($ct_name ?? '') ?></p>
            <p>案主地址：<?= esc($ct_address ?? '') ?></p>
            <p>路線編號：<?= esc($route_no ?? '') ?></p>

            <a href="<?= base_url('clients/create') ?>">返回新增畫面</a>
        </main>

    </div>
</body>

</html>