<?php
// 取得登入帳號；沒有帳號時顯示提示文字
$currentUsername = session()->get('username') ?? '未取得帳號';

// 取得顯示名稱；未設定時使用登入帳號
$currentDisplayName = session()->get('display_name')
    ?: $currentUsername;

// 將角色代碼轉成中文名稱
$roleLabels = [
    'admin'  => '系統管理員',
    'staff'  => '工作人員',
    'viewer' => '唯讀使用者',
];

// 取得目前角色及中文名稱
$currentRole = session()->get('role') ?? '';
$currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';

// 管理員與工作人員可以新增、匯入及匯出案主資料
$canManageClients = in_array(
    $currentRole,
    ['admin', 'staff'],
    true
);

// 取得 Controller 傳回的驗證錯誤
$errors = session()->getFlashdata('error') ?? [];

// 確認錯誤資料是陣列，避免 foreach 發生錯誤
$errors = is_array($errors) ? $errors : [];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 讓頁面在不同視窗尺寸下正確顯示 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用 CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- 設定瀏覽器分頁標題 -->
    <title>新增案主</title>
</head>

<body>
    <!-- 整頁容器：排列側邊欄和主要內容 -->
    <div class="layout">

        <!-- 左側系統導覽欄 -->
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <!-- 系統主要導覽選單 -->
            <nav aria-label="主要導覽">
                <!-- 回到系統首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 案主管理選單；新增案主頁載入時展開 -->
                <details class="sidebar-menu-group" open>
                    <summary>案主管理</summary>

                    <div class="sidebar-submenu">
                        <!-- 前往案主資料列表 -->
                        <a href="<?= base_url('clients') ?>">案主資料</a>

                        <!-- 只有管理角色可以使用 Excel 匯入 -->
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

                <!-- 前往資源回收桶 -->
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>

            <!-- 側欄底部的個人設定區 -->
            <div class="sidebar-footer">
                <details class="sidebar-settings">
                    <!-- 點擊後展開個人設定 -->
                    <summary class="sidebar-settings-trigger">
                        個人設定
                    </summary>

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

                        <!-- 登出使用 POST 表單 -->
                        <form
                            action="<?= base_url('logout') ?>"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            <!-- 產生 CSRF 欄位，供系統驗證表單來源 -->
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

                <span>案主管理</span>

                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <a href="<?= base_url('clients') ?>">案主資料</a>

                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <span aria-current="page">新增案主</span>
            </nav>

            <!-- 頁面標題 -->
            <h1>新增案主</h1>

            <!-- 有驗證錯誤時顯示錯誤清單 -->
            <?php if (! empty($errors)): ?>
                <div class="detail-alert detail-alert-error">
                    <p>請檢查以下欄位：</p>

                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= esc($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- 案主新增表單區 -->
            <section class="delivery-staff-card">
                <h2>案主基本資料</h2>

                <!-- 表單以 POST 傳送至 clients，由 ClientController::store() 處理 -->
                <form
                    action="<?= base_url('clients') ?>"
                    method="post"
                    class="client-detail-form"
                >
                    <!-- 產生 CSRF 欄位，供系統驗證表單來源 -->
                    <?= csrf_field() ?>

                    <div class="client-detail-form-grid">
                        <!-- 案主姓名 -->
                        <div class="client-detail-form-field">
                            <label for="ct_name">案主姓名</label>

                            <input
                                type="text"
                                id="ct_name"
                                name="ct_name"
                                value="<?= esc(old('ct_name', ''), 'attr') ?>"
                                maxlength="100"
                                required
                            >
                        </div>

                        <!-- 案主地址 -->
                        <div class="client-detail-form-field">
                            <label for="ct_addr">案主地址</label>

                            <input
                                type="text"
                                id="ct_addr"
                                name="ct_addr"
                                value="<?= esc(old('ct_addr', ''), 'attr') ?>"
                                maxlength="255"
                                required
                            >
                        </div>

                        <!-- 路線編號必須是整數 -->
                        <div class="client-detail-form-field">
                            <label for="route_no">路線編號</label>

                            <input
                                type="number"
                                id="route_no"
                                name="route_no"
                                value="<?= esc(old('route_no', ''), 'attr') ?>"
                                step="1"
                                required
                            >
                        </div>
                    </div>

                    <!-- 表單操作按鈕 -->
                    <div class="client-detail-form-actions">
                        <!-- 送出表單並新增案主 -->
                        <button type="submit" class="btn btn-primary">
                            儲存案主
                        </button>

                        <!-- 取消新增並回到案主列表 -->
                        <a
                            href="<?= base_url('clients') ?>"
                            class="btn btn-secondary"
                        >
                            取消
                        </a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>