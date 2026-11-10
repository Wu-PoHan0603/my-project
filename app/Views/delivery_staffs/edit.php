<?php
// Controller 會傳入目前要修改的送餐員資料
/** @var array $staff */

// 確認收到的資料是陣列
$staff = is_array($staff ?? null) ? $staff : [];

// 取得送餐員編號，供表單 action 使用
$staffId = (int) ($staff['id'] ?? 0);

// 取得登入帳號；Session 沒有帳號時顯示提示文字
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

// 取得目前角色；未知角色顯示預設文字
$currentRole = session()->get('role') ?? '';
$currentRoleLabel = $roleLabels[$currentRole] ?? '未設定角色';

// 取得欄位驗證錯誤；沒有錯誤時使用空陣列
$errors = session()->getFlashdata('errors') ?? [];

// 確認錯誤資料是陣列
$errors = is_array($errors) ? $errors : [];

// 驗證失敗返回時，優先保留剛才選擇的啟用狀態
// 第一次開啟時，使用資料庫內的狀態
$activeValue = (string) old(
    'is_active',
    $staff['is_active'] ?? '1'
);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 設定頁面在不同視窗尺寸下的顯示方式 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用 CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- 設定瀏覽器分頁標題 -->
    <title>修改送餐員資料</title>
</head>

<body>
    <!-- 整頁版面容器：排列側邊欄和主要內容 -->
    <div class="layout">

        <!-- 系統左側導覽欄 -->
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <!-- 系統主要導覽連結 -->
            <nav>
                <!-- 回到系統首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 案主管理的子選單 -->
                <details class="sidebar-menu-group">
                    <summary>案主管理</summary>

                    <div class="sidebar-submenu">
                        <!-- 前往案主資料列表 -->
                        <a href="<?= base_url('clients') ?>">案主資料</a>
                    </div>
                </details>

                <!-- 送餐員管理的子選單；目前頁面載入時展開 -->
                <details class="sidebar-menu-group" open>
                    <summary>送餐員管理</summary>

                    <div class="sidebar-submenu">
                        <!-- 前往送餐員資料列表 -->
                        <a href="<?= base_url('delivery_staff') ?>">送餐員資料</a>
                    </div>
                </details>

                <!-- 前往案主資源回收桶 -->
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>

            <!-- 側欄底部的個人設定入口 -->
            <div class="sidebar-footer">
                <details class="sidebar-settings">
                    <!-- 點擊後展開個人設定內容 -->
                    <summary class="sidebar-settings-trigger">個人設定</summary>

                    <!-- 個人設定展開內容 -->
                    <div class="sidebar-settings-panel">

                        <!-- 顯示目前登入者資訊 -->
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

                        <!-- 登出會改變登入狀態，因此使用 POST 表單 -->
                        <form
                            action="<?= base_url('logout') ?>"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            <!-- 產生 CSRF 欄位供系統驗證表單來源 -->
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

        <!-- 網頁右側的主要內容 -->
        <main class="content">

            <!-- 修改頁的麵包屑導覽 -->
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="<?= base_url('/') ?>">首頁</a>

                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <span>送餐員管理</span>

                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <a href="<?= base_url('delivery_staff') ?>">送餐員資料</a>

                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <span aria-current="page">修改資料</span>
            </nav>

            <!-- 頁面標題 -->
            <h1>修改送餐員資料</h1>

            <!-- 顯示資料驗證錯誤 -->
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

            <!-- 修改送餐員資料表單 -->
            <section class="delivery-staff-card">
                <h2>送餐員資料</h2>

                <!-- POST 送到更新路由，並帶入送餐員編號 -->
                <form
                    action="<?= base_url('delivery_staff/update/' . $staffId) ?>"
                    method="post"
                    class="client-detail-form"
                >
                    <!-- CSRF Token 用來驗證表單是從本站送出 -->
                    <?= csrf_field() ?>

                    <!-- 姓名、電話和啟用狀態的欄位排版區 -->
                    <div class="client-detail-form-grid">

                        <!-- 送餐員姓名欄位 -->
                        <div class="client-detail-form-field">
                            <label for="staff_name">送餐員姓名</label>

                            <input
                                type="text"
                                id="staff_name"
                                name="staff_name"
                                value="<?= esc(
                                    old('staff_name', $staff['staff_name'] ?? ''),
                                    'attr'
                                ) ?>"
                                maxlength="100"
                                required
                            >
                        </div>

                        <!-- 聯絡電話欄位；電話可以留空 -->
                        <div class="client-detail-form-field">
                            <label for="phone">聯絡電話</label>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?= esc(
                                    old('phone', $staff['phone'] ?? ''),
                                    'attr'
                                ) ?>"
                                maxlength="30"
                            >
                        </div>

                        <!-- 送餐員啟用狀態開關 -->
                        <div class="client-detail-form-field">
                            <label for="is_active">送餐員啟用</label>

                            <div class="staff-status-toggle">
                                <!-- 開關未勾選時送出 0，代表停用 -->
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >

                                <!-- 開關勾選時送出 1，代表啟用 -->
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    class="staff-switch-input"
                                    role="switch"
                                    aria-label="送餐員啟用狀態"
                                    <?= $activeValue === '1' ? 'checked' : '' ?>
                                >

                                <!-- 顯示開關和目前狀態文字 -->
                                <label class="staff-switch-label" for="is_active">
                                    <span class="staff-switch-track" aria-hidden="true">
                                        <span class="staff-switch-knob"></span>
                                    </span>

                                    <span class="staff-switch-text">
                                        <span class="staff-switch-on">啟用</span>
                                        <span class="staff-switch-off">停用</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                    </div>
                    <!-- 欄位排版區結束 -->

                    <!-- 表單操作按鈕 -->
                    <div class="client-detail-form-actions">
                        <!-- 送出表單，更新送餐員資料 -->
                        <button type="submit" class="btn btn-primary">
                            儲存修改
                        </button>

                        <!-- 取消修改並返回送餐員列表 -->
                        <a
                            href="<?= base_url('delivery_staff') ?>"
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