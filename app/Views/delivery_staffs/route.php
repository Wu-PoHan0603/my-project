<?php
/** @var array $routes */
/** @var array $staffList */
// Controller 傳入路線和啟用中的送餐員清單
$routes = is_array($routes ?? null) ? $routes : [];
$staffList = is_array($staffList ?? null) ? $staffList : [];

// 取得操作結果訊息
$successMessage = session()->getFlashdata('success');
$errorMessage = session()->getFlashdata('error');
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <title>路線指派</title>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <a href="<?= base_url('/') ?>">首頁</a>
                <a href="<?= base_url('clients') ?>">案主資料</a>
                <a href="<?= base_url('delivery_staff') ?>">送餐員資料</a>
                <a href="<?= base_url('delivery-routes') ?>">路線指派</a>
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
            </nav>
        </aside>

        <main class="content">
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="<?= base_url('/') ?>">首頁</a>
                <span aria-hidden="true">&gt;</span>
                <span>路線指派</span>
            </nav>

            <h1>路線指派</h1>

            <?php if (is_string($successMessage) && $successMessage !== ''): ?>
                <p class="message-success"><?= esc($successMessage) ?></p>
            <?php endif; ?>

            <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
                <p class="message-error"><?= esc($errorMessage) ?></p>
            <?php endif; ?>

            <?php if (! empty($routes)): ?>
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>路線編號</th>
                            <th>目前送餐員</th>
                            <th>指派送餐員</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td><?= esc($route['route_no'] ?? '') ?></td>
                                <td><?= esc($route['staff_name'] ?? '尚未指派') ?></td>
                                <td>
                                    <form
                                        action="<?= base_url('delivery-routes/assign/' . (int) $route['id']) ?>"
                                        method="post"
                                    >
                                        <?= csrf_field() ?>

                                        <select name="delivery_staff_id">
                                            <option value="">尚未指派</option>

                                            <?php foreach ($staffList as $staff): ?>
                                                <option
                                                    value="<?= esc((string) $staff['id'], 'attr') ?>"
                                                    <?= (int) ($route['delivery_staff_id'] ?? 0) === (int) $staff['id']
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    <?= esc($staff['staff_name'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>
                                <td>
                                        <button type="submit" class="btn btn-primary">
                                            儲存指派
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
        </main>
    </div>
</body>
</html>