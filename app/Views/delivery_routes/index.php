<?php
// 確保 Controller 傳入的路線與送餐員清單都是陣列
/** @var array $routes */
$routes = is_array($routes ?? null) ? $routes : [];
/** @var array $staffList */
$staffList = is_array($staffList ?? null) ? $staffList : [];

// 取得成功或錯誤訊息
$successMessage = session()->getFlashdata('success');
$errorMessage = session()->getFlashdata('error');
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用 CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <title>路線指派</title>
</head>
<body>
    <main class="delivery-route-page">
        <!-- 頁面標題 -->
        <h1>路線指派</h1>

        <!-- 前往送餐員設定 -->
        <a href="<?= base_url('delivery-staff') ?>" class="btn btn-secondary">
            送餐員設定
        </a>

        <!-- 顯示指派成功訊息 -->
        <?php if ($successMessage): ?>
            <p class="delivery-message-success">
                <?= esc($successMessage) ?>
            </p>
        <?php endif; ?>

        <!-- 顯示錯誤訊息 -->
        <?php if ($errorMessage): ?>
            <p class="delivery-message-error">
                <?= esc($errorMessage) ?>
            </p>
        <?php endif; ?>

        <!-- 路線清單 -->
        <section class="delivery-route-card">
            <h2>路線與送餐員</h2>

            <?php if (! empty($routes)): ?>
                <table class="delivery-route-table">
                    <thead>
                        <tr>
                            <th>路線編號</th>
                            <th>目前送餐員</th>
                            <th>指派送餐員</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <!-- 顯示路線編號 -->
                                <td><?= esc($route['route_no']) ?></td>

                                <!-- 顯示目前已指派的送餐員 -->
                                <td>
                                    <?= esc($route['staff_name'] ?? '尚未指派') ?>
                                </td>

                                <!-- 每條路線各自有一個指派表單 -->
                                <td>
                                    <form
                                        action="<?= base_url(
                                            'delivery-routes/assign/' . $route['id']
                                        ) ?>"
                                        method="post"
                                        class="delivery-route-form"
                                    >
                                        <!-- CSRF 欄位驗證表單來源 -->
                                        <?= csrf_field() ?>

                                        <!-- 選擇送餐員；空白選項代表取消指派 -->
                                        <select name="delivery_staff_id">
                                            <option
                                                value=""
                                                <?= empty($route['delivery_staff_id']) ? 'selected' : '' ?>
                                            >
                                                未指派
                                            </option>

                                            <!-- 列出所有啟用中的送餐員 -->
                                            <?php foreach ($staffList as $staff): ?>
                                                <option
                                                    value="<?= esc(
                                                        (string) $staff['id'],
                                                        'attr'
                                                    ) ?>"
                                                    <?= (int) ($route['delivery_staff_id'] ?? 0)
                                                        === (int) $staff['id']
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    <?= esc($staff['staff_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <!-- 送出目前這條路線的指派資料 -->
                                        <button type="submit" class="btn btn-primary">
                                            儲存
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>目前沒有路線資料。</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>