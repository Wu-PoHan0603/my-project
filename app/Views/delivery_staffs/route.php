<?php

// Controller 的 DeliveryRouteController::index() 會傳入路線清單
/** @var array $routes */

// Controller 也會傳入可供指派的啟用中送餐員清單
/** @var array $staffList */

// 確認路線資料是陣列；未收到資料時使用空陣列
$routes = is_array($routes ?? null) ? $routes : [];

// 確認送餐員資料是陣列；未收到資料時使用空陣列
$staffList = is_array($staffList ?? null) ? $staffList : [];

// 取得指派成功訊息；沒有訊息時使用空字串
$successMessage = session()->getFlashdata('success') ?? '';

// 取得指派錯誤訊息；沒有訊息時使用空字串
$errorMessage = session()->getFlashdata('error') ?? '';

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">

    <!-- 讓頁面在不同尺寸的視窗中正確顯示 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用 CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- 設定瀏覽器分頁標題 -->
    <title>路線指派</title>
</head>

<body>
    <!-- 整頁容器：排列左側導覽欄與主要內容 -->
    <div class="layout">

        <!-- 左側系統導覽欄 -->
        <aside class="sidebar">

            <!-- 顯示系統名稱 -->
            <h2>案主管理系統</h2>

            <!-- 系統導覽選單 -->
            <nav>

                <!-- 前往系統首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 前往案主資料列表 -->
                <a href="<?= base_url('clients') ?>">案主資料</a>

                <!-- 前往送餐員資料列表 -->
                <a href="<?= base_url('delivery_staff') ?>">送餐員資料</a>

                <!-- 前往資源回收桶 -->
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>
        </aside>

        <!-- 網頁右側主要內容 -->
        <main class="content">

            <!-- 麵包屑：顯示目前頁面所在位置 -->
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">

                <!-- 點擊後回到首頁 -->
                <a href="<?= base_url('/') ?>">首頁</a>

                <!-- 麵包屑分隔符號 -->
                <span
                    class="page-breadcrumb-separator"
                    aria-hidden="true"
                >&gt;</span>

                <!-- 標示目前所在頁面 -->
                <span aria-current="page">路線指派</span>
            </nav>

            <!-- 頁面標題 -->
            <h1>路線指派</h1>

            <!-- 有成功訊息時顯示 -->
            <?php if (is_string($successMessage) && $successMessage !== ''): ?>
                <p class="message-success">
                    <?= esc($successMessage) ?>
                </p>
            <?php endif; ?>

            <!-- 有錯誤訊息時顯示 -->
            <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
                <p class="message-error">
                    <?= esc($errorMessage) ?>
                </p>
            <?php endif; ?>

            <!-- 路線資料區 -->
            <section class="delivery-staff-section">

                <!-- 有路線資料時顯示表格 -->
                <?php if (! empty($routes)): ?>

                    <!-- 顯示路線與送餐員指派狀態 -->
                    <table class="delivery-staff-table">

                        <!-- 表格標題列：每筆資料對應三個欄位 -->
                        <thead>
                            <tr>
                                <th>路線編號</th>
                                <th>目前指派送餐員</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <!-- 表格資料列 -->
                        <tbody>

                            <!-- 逐筆顯示 Controller 傳入的路線 -->
                            <?php foreach ($routes as $route): ?>
                                <tr>

                                    <!-- 顯示路線編號 -->
                                    <td>
                                        <?= esc($route['route_no'] ?? '') ?>
                                    </td>

                                    <!-- 顯示目前已指派的送餐員姓名 -->
                                    <td>
                                        <?= esc($route['staff_name'] ?? '尚未指派') ?>
                                    </td>

                                    <!-- 操作欄：選擇送餐員並儲存指派 -->
                                    <td>
                                        <form
                                            action="<?= base_url(
                                                'delivery-routes/assign/'
                                                . (int) ($route['id'] ?? 0)
                                            ) ?>"
                                            method="post"
                                            class="action-buttons"
                                        >
                                            <!-- 產生 CSRF 欄位，供 CI4 驗證表單來源 -->
                                            <?= csrf_field() ?>

                                            <!-- 選擇要指派給這條路線的送餐員 -->
                                            <select
                                                name="delivery_staff_id"
                                                aria-label="選擇送餐員"
                                            >
                                                <!-- 空值代表取消這條路線的指派 -->
                                                <option value="">
                                                    尚未指派
                                                </option>

                                                <!-- 列出啟用中的送餐員 -->
                                                <?php foreach ($staffList as $staff): ?>
                                                    <option
                                                        value="<?= esc(
                                                            (string) ($staff['id'] ?? ''),
                                                            'attr'
                                                        ) ?>"
                                                        <?= (int) ($route['delivery_staff_id'] ?? 0)
                                                            === (int) ($staff['id'] ?? 0)
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        <?= esc($staff['staff_name'] ?? '') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <!-- 送出表單，儲存這條路線的指派 -->
                                            <button
                                                type="submit"
                                                class="btn btn-primary"
                                            >
                                                儲存指派
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>

                <!-- 沒有路線資料時顯示提示 -->
                <?php else: ?>
                    <p>目前沒有路線資料。</p>
                <?php endif; ?>

            </section>
        </main>
    </div>
</body>
</html>