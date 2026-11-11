<?php
// 取得一次性錯誤訊息；沒有錯誤時使用空字串
$errorMessage = session()->getFlashdata('error') ?? '';

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

// 管理員和工作人員可以使用案主資料管理功能
$canManageClients = in_array(
    $currentRole,
    ['admin', 'staff'],
    true
);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 讓頁面配合不同裝置的畫面寬度 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用 CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- 設定瀏覽器分頁標題 -->
    <title>匯入案主 Excel</title>
</head>

<body>
    <!-- 整頁容器：排列側邊欄與主要內容 -->
    <div class="layout">

        <!-- 左側系統導覽欄 -->
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <!-- 系統主要導覽選單 -->
            <nav aria-label="主要導覽">

                <!-- 前往系統首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 案主管理選單；目前在匯入頁，所以預設展開 -->
                <details class="sidebar-menu-group" open>
                    <summary>案主管理</summary>

                    <div class="sidebar-submenu">
                        <!-- 前往案主資料列表 -->
                        <a href="<?= base_url('clients') ?>">案主資料</a>

                        <!-- 前往案主 Excel 匯入頁 -->
                        <?php if ($canManageClients): ?>
                            <a href="<?= base_url('clients/import') ?>">
                                匯入 Excel
                            </a>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- 送餐員管理選單 -->
                <details class="sidebar-menu-group">
                    <summary>送餐員管理</summary>

                    <div class="sidebar-submenu">
                        <!-- 前往送餐員資料列表 -->
                        <a href="<?= base_url('delivery_staff') ?>">
                            送餐員資料
                        </a>

                        <!-- 前往路線指派頁 -->
                        <a href="<?= base_url('delivery-routes') ?>">
                            路線指派
                        </a>
                    </div>
                </details>

                <!-- 前往案主資源回收桶 -->
                <a href="<?= base_url('clients/trash') ?>">
                    資源回收桶
                </a>
            </nav>

            <!-- 側欄底部的個人設定區 -->
            <div class="sidebar-footer">
                <details class="sidebar-settings">
                    <!-- 點擊後展開個人設定 -->
                    <summary class="sidebar-settings-trigger">
                        個人設定
                    </summary>

                    <!-- 展開後顯示登入者資訊與帳號操作 -->
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

                        <!-- 前往修改密碼頁 -->
                        <a
                            href="<?= base_url('change-password') ?>"
                            class="sidebar-settings-link"
                        >
                            更新密碼
                        </a>

                        <!-- 登出使用 POST 表單，避免用 GET 改變登入狀態 -->
                        <form
                            action="<?= base_url('logout') ?>"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            <!-- 產生 CSRF 欄位供系統驗證 -->
                            <?= csrf_field() ?>

                            <!-- 送出登出請求 -->
                            <button
                                type="submit"
                                class="sidebar-logout-button"
                            >
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        <!-- 右側主要內容 -->
        <main class="content">

            <!-- 顯示目前頁面的導覽路徑 -->
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="<?= base_url('/') ?>">首頁</a>

                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <a href="<?= base_url('clients') ?>">案主資料</a>

                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <span aria-current="page">匯入 Excel</span>
            </nav>

            <!-- 頁面標題 -->
            <h1>匯入案主 Excel</h1>

            <!-- 有錯誤訊息時顯示 -->
            <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
                <p class="message-error">
                    <?= esc($errorMessage) ?>
                </p>
            <?php endif; ?>

            <!-- Excel 檔案上傳表單 -->
            <section class="delivery-staff-card">
                <h2>選擇匯入檔案</h2>

                <form
                    action="<?= base_url('clients/import/preview') ?>"
                    method="post"
                    enctype="multipart/form-data"
                >
                    <!-- 產生 CSRF 欄位，供 CI4 驗證表單來源 -->
                    <?= csrf_field() ?>

                    <!-- 提示使用者選擇 Excel 檔案 -->
                    <label for="excel_file">選擇 Excel 檔案</label>

                    <!-- 接受 .xlsx 或 .xls 檔案 -->
                    <input
                        type="file"
                        id="excel_file"
                        name="excel_file"
                        accept=".xlsx,.xls"
                        required
                    >

                    <!-- 送出檔案並前往資料預覽 -->
                    <button type="submit" class="btn btn-primary">
                        上傳並預覽
                    </button>

                    <!-- 取消匯入並返回案主列表 -->
                    <a
                        href="<?= base_url('clients') ?>"
                        class="btn btn-secondary"
                    >
                        取消
                    </a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>