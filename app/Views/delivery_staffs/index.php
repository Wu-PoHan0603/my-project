<?php
// Controller 會傳入送餐員清單 $staffList
/** @var array $staffList */

// 確認送餐員清單是陣列；沒有收到資料時使用空陣列
$staffList = is_array($staffList ?? null) ? $staffList : [];

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
    <title>送餐員資料</title>
</head>

<body>
    <!-- 整頁版面容器：排列側邊欄與主要內容 -->
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

            <!-- 頁面麵包屑導覽 -->
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <!-- 點擊後回到首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 路徑分隔符號 -->
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <!-- 顯示目前管理分類 -->
                <span>送餐員管理</span>

                <!-- 路徑分隔符號 -->
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <!-- 標示目前所在頁面 -->
                <span aria-current="page">送餐員資料</span>
            </nav>

            <!-- 頁面主標題 -->
            <h1>送餐員資料</h1>

            <!-- 顯示新增成功訊息 -->
            <?php $successMessage = session()->getFlashdata('success'); ?>
            <?php if (is_string($successMessage) && $successMessage !== ''): ?>
                <p class="message-success">
                    <?= esc($successMessage) ?>
                </p>
            <?php endif; ?>

            <!-- 顯示操作錯誤訊息 -->
            <?php $errorMessage = session()->getFlashdata('error'); ?>
            <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
                <p class="message-error">
                    <?= esc($errorMessage) ?>
                </p>
            <?php endif; ?>

            <!-- 送餐員資料列表區 -->
            <section class="delivery-staff-section">

                <!-- 列表標題與新增按鈕並排 -->
                <div class="delivery-staff-list-heading">
                    <h2>送餐員資料列表</h2>

                    <!-- 前往獨立的新增送餐員頁面 -->
                    <div class="delivery-staff-toolbar">
                        <a
                            href="<?= base_url('delivery_staff/create') ?>"
                            class="btn btn-danger"
                        >
                            新增送餐員
                        </a>
                    </div>
                </div>

                <!-- 有送餐員資料時顯示表格 -->
                <?php if (! empty($staffList)): ?>
                    <table class="delivery-staff-table">
                        <thead>
                            <tr>
                                <th>編號</th>
                                <th>送餐員姓名</th>
                                <th>聯絡電話</th>
                                <th>地址</th>
                                <th>狀態</th>
                                <th>建立時間</th>
                                <th>修改時間</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 逐筆輸出 Controller 傳入的送餐員資料 -->
                            <?php foreach ($staffList as $staff): ?>
                                <?php
                                // 建立時間預設顯示「尚未記錄」
                                $createdAtTaipei = '尚未記錄';

                                // 有建立時間時，將資料庫 UTC 時間轉成台灣時間
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

                                // 有修改時間時，將資料庫 UTC 時間轉成台灣時間
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

                                <tr>
                                    <!-- 顯示送餐員編號 -->
                                    <td><?= esc((string) ($staff['id'] ?? '')) ?></td>

                                    <!-- 顯示送餐員姓名 -->
                                    <td><?= esc($staff['staff_name'] ?? '尚未填寫') ?></td>

                                    <!-- 顯示聯絡電話 -->
                                    <td><?= esc($staff['phone'] ?? '尚未填寫') ?></td>

                                    <!-- 顯示地址 -->
                                    <td><?= esc($staff['address'] ?? '尚未填寫') ?></td>

                                    <!-- 顯示啟用或停用狀態 -->
                                    <td><?= esc($statusLabel) ?></td>

                                    <!-- 顯示轉換成台灣時間的建立時間 -->
                                    <td><?= esc($createdAtTaipei) ?></td>

                                    <!-- 顯示轉換成台灣時間的修改時間 -->
                                    <td><?= esc($updatedAtTaipei) ?></td>

                                    <!-- 操作欄：查看、修改或刪除這位送餐員 -->
                                    <td>
                                        <div class="action-buttons">

                                            <!-- 前往這位送餐員的詳細資料頁 -->
                                            <a
                                                class="btn btn-primary"
                                                href="<?= base_url(
                                                    'delivery_staff/'
                                                    . $staff['id']
                                                    . '/detail'
                                                ) ?>"
                                            >
                                                詳細資料
                                            </a>

                                            <!-- 前往這位送餐員的修改表單 -->
                                            <a
                                                class="btn btn-warning"
                                                href="<?= base_url(
                                                    'delivery_staff/'
                                                    . $staff['id']
                                                    . '/edit'
                                                ) ?>"
                                            >
                                                修改
                                            </a>

                                            <!-- 刪除會改變資料庫，所以使用 POST 表單 -->
                                            <form
                                                action="<?= base_url(
                                                    'delivery_staff/delete/'
                                                    . $staff['id']
                                                ) ?>"
                                                method="post"
                                                class="action-form"
                                                onsubmit="return confirm('確定要永久刪除這位送餐員嗎？')"
                                            >
                                                <!-- 產生 CSRF Token，供系統驗證表單來源 -->
                                                <?= csrf_field() ?>

                                                <!-- 送出刪除請求 -->
                                                <button type="submit" class="btn btn-danger">
                                                    刪除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <!-- 清單為空時顯示提示文字 -->
                <?php else: ?>
                    <p>目前還沒有送餐員資料。</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>