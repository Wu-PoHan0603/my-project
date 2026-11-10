<?php
// Controller 傳入指定送餐員的資料
/** @var array $staff */

// 確認收到的資料是陣列
$staff = is_array($staff ?? null) ? $staff : [];

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

// 建立時間預設顯示「尚未記錄」
$createdAtTaipei = '尚未記錄';

// 有建立時間時，將資料庫時間轉成台灣時間
if (! empty($staff['created_at'])) {
    $createdAtTaipei = \CodeIgniter\I18n\Time::parse(
        $staff['created_at'],
        'UTC'
    )
        ->setTimezone('Asia/Taipei')
        ->format('Y-m-d H:i:s');
}

// 修改時間預設顯示「尚未記錄」
$updatedAtTaipei = '尚未記錄';

// 有修改時間時，將資料庫時間轉成台灣時間
if (! empty($staff['updated_at'])) {
    $updatedAtTaipei = \CodeIgniter\I18n\Time::parse(
        $staff['updated_at'],
        'UTC'
    )
        ->setTimezone('Asia/Taipei')
        ->format('Y-m-d H:i:s');
}

// 將資料庫的 1/0 狀態轉成中文
$statusLabel = (int) ($staff['is_active'] ?? 0) === 1
    ? '啟用'
    : '停用';
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 設定頁面在不同視窗尺寸下的顯示方式 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用樣式 -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- 設定瀏覽器分頁標題 -->
    <title>送餐員詳細資料</title>
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

            <!-- 顯示目前頁面的導覽路徑 -->
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <!-- 點擊後回到首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 路徑分隔符號 -->
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <!-- 顯示目前管理分類 -->
                <span>送餐員管理</span>

                <!-- 路徑分隔符號 -->
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <!-- 點擊後回到送餐員列表 -->
                <a href="<?= base_url('delivery_staff') ?>">送餐員資料</a>

                <!-- 路徑分隔符號 -->
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <!-- 標示目前所在頁面 -->
                <span aria-current="page">詳細資料</span>
            </nav>

            <!-- 顯示頁面標題和送餐員姓名 -->
            <h1>
                送餐員詳細資料：<?= esc($staff['staff_name'] ?? '未命名送餐員') ?>
            </h1>

            <!-- 送餐員資料卡片 -->
            <section class="delivery-staff-card">
                <h2>基本資料</h2>

                <!-- 使用資料格顯示欄位名稱和內容 -->
                <dl class="client-detail-grid">

                    <!-- 顯示送餐員編號 -->
                    <div class="client-detail-item">
                        <dt>送餐員編號</dt>
                        <dd><?= esc((string) ($staff['id'] ?? '')) ?></dd>
                    </div>

                    <!-- 顯示送餐員姓名 -->
                    <div class="client-detail-item">
                        <dt>送餐員姓名</dt>
                        <dd><?= esc($staff['staff_name'] ?? '尚未填寫') ?></dd>
                    </div>

                    <!-- 顯示聯絡電話 -->
                    <div class="client-detail-item">
                        <dt>聯絡電話</dt>
                        <dd><?= esc($staff['phone'] ?? '尚未填寫') ?></dd>
                    </div>

                    <!-- 顯示啟用或停用狀態 -->
                    <div class="client-detail-item">
                        <dt>狀態</dt>
                        <dd><?= esc($statusLabel) ?></dd>
                    </div>

                    <!-- 顯示轉換成台灣時間的建立時間 -->
                    <div class="client-detail-item">
                        <dt>建立時間</dt>
                        <dd><?= esc($createdAtTaipei) ?></dd>
                    </div>

                    <!-- 顯示轉換成台灣時間的修改時間 -->
                    <div class="client-detail-item">
                        <dt>修改時間</dt>
                        <dd><?= esc($updatedAtTaipei) ?></dd>
                    </div>
                </dl>

                <!-- 返回送餐員資料列表 -->
                <div class="client-detail-form-actions">
                    <a
                        href="<?= base_url('delivery_staff') ?>"
                        class="btn btn-secondary"
                    >
                        返回送餐員列表
                    </a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>