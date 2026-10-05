<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增案主</title>
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
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>
            <?php
            // 取得登入帳號；Session 沒有帳號時顯示提示文字
            $currentUsername = session()->get('username') ?? '未取得帳號';

            // 取得顯示名稱；沒有設定時使用登入帳號
            $currentDisplayName = session()->get('display_name')
                ?: $currentUsername;

            // 將角色代碼換成中文名稱
            $roleLabels = [
                'admin'  => '系統管理員',
                'staff'  => '工作人員',
                'viewer' => '唯讀使用者',
            ];

            // 取得目前角色；未知角色顯示預設文字
            $currentRole = session()->get('role') ?? '';
            $currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';
            ?>

            <!-- 側欄左下角只顯示「個人設定」入口 -->
            <div class="sidebar-footer">

                <!-- details/summary 可點開或收合設定內容，不需要另外寫 JavaScript -->
                <details class="sidebar-settings">

                    <!-- 收合時只會看到這個標題 -->
                    <summary class="sidebar-settings-trigger">
                        個人設定
                    </summary>

                    <!-- 點開後才顯示的設定內容 -->
                    <div class="sidebar-settings-panel">

                        <!-- 顯示登入者資訊 -->
                        <div class="sidebar-account-info">

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">登入帳號</span>
                                <strong class="sidebar-account-value">
                                    <?= esc($currentUsername) ?>
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">顯示名稱</span>
                                <strong class="sidebar-account-value">
                                    <?= esc($currentDisplayName) ?>
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">角色</span>
                                <strong class="sidebar-account-value">
                                    <?= esc($currentRoleLabel) ?>
                                </strong>
                            </p>
                        </div>

                        <!-- 前往目前已完成的更新密碼頁面 -->
                        <a
                            href="<?= base_url('change-password') ?>"
                            class="sidebar-settings-link"
                        >
                            更新密碼
                        </a>

                        <!-- 登出會改變登入狀態，所以使用 POST 表單 -->
                        <form
                            action="<?= base_url('logout') ?>"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            <!-- 產生 CSRF Token，供 CSRF Filter 驗證 -->
                            <?= csrf_field() ?>

                            <!-- 送出登出請求 -->
                            <button type="submit" class="sidebar-logout-button">
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        <main class="content">
            <h1>新增案主</h1>

            <?php 
            //取得Controller暫存在Session的驗證錯誤
            $errors = session()->getFlashdata('error') ?? [] ;
            ?>

            <?php if (! empty($errors)): ?>
                <div class="message-error">
                    <ul>
                        <?php foreach($errors as $error): ?>
                        <!-- esc()安全顯示錯誤文字 -->
                        <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <form action="<?= base_url('clients') ?>" method="post">
                <?= csrf_field() ?>
                <div>
                    <label for="ct_name">案主姓名</label>
                    <input type="text" id="ct_name" name="ct_name">
                </div>
                <br>

                <div>
                    <label for="ct_address">案主地址</label>
                    <input type="text" id="ct_address" name="ct_address">
                </div>
                <br>

                <div>
                    <label for="route_no">路線編號</label>
                    <input type="number" id="route_no" name="route_no">
                </div>
                <br>

                <button type="submit">新增案主</button>
            </form>
        </main>

    </div>
</body>

</html>