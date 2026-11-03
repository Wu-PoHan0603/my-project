<?php
// 確保 Controller 傳入的送餐員清單是陣列
$staffList = is_array($staffList ?? null) ? $staffList : [];

// 取得欄位驗證錯誤
$errors = session()->getFlashdata('errors') ?? [];

// 取得新增成功訊息
$successMessage = session()->getFlashdata('success');
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 載入專案共用 CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <title>送餐員設定</title>
</head>
<body>
    <main class="delivery-staff-page">
        <!-- 顯示頁面標題 -->
        <h1>送餐員設定</h1>

        <!-- 返回案主列表 -->
        <a href="<?= base_url('clients') ?>" class="btn btn-secondary">
            返回案主列表
        </a>

        <!-- 新增成功時顯示提示 -->
        <?php if ($successMessage): ?>
            <p class="delivery-message-success">
                <?= esc($successMessage) ?>
            </p>
        <?php endif; ?>

        <!-- 有欄位錯誤時逐項顯示 -->
        <?php if (! empty($errors)): ?>
            <div class="delivery-message-error">
                <p>請檢查以下欄位：</p>
                <ul>
                    <?php foreach ($errors as $message): ?>
                        <li><?= esc($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- 新增送餐員表單區 -->
        <section class="delivery-staff-card">
            <h2>新增送餐員</h2>

            <form
                action="<?= base_url('delivery-staff') ?>"
                method="post"
                class="delivery-staff-form"
            >
                <!-- CSRF 欄位用來驗證表單來源 -->
                <?= csrf_field() ?>

                <!-- 輸入送餐員姓名 -->
                <div class="delivery-form-field">
                    <label for="staff_name">送餐員姓名</label>
                    <input
                        type="text"
                        id="staff_name"
                        name="staff_name"
                        value="<?= esc(old('staff_name', ''), 'attr') ?>"
                        maxlength="100"
                        required
                    >
                </div>

                <!-- 輸入電話，這個欄位可以留空 -->
                <div class="delivery-form-field">
                    <label for="phone">聯絡電話</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?= esc(old('phone', ''), 'attr') ?>"
                        maxlength="30"
                    >
                </div>

                <!-- 送出表單新增送餐員 -->
                <button type="submit" class="btn btn-primary">
                    新增送餐員
                </button>
            </form>
        </section>

        <!-- 顯示送餐員列表 -->
        <section class="delivery-staff-card">
            <h2>送餐員列表</h2>

            <?php if (! empty($staffList)): ?>
                <table class="delivery-staff-table">
                    <thead>
                        <tr>
                            <th>編號</th>
                            <th>姓名</th>
                            <th>電話</th>
                            <th>狀態</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffList as $staff): ?>
                            <tr>
                                <td><?= esc($staff['id']) ?></td>
                                <td><?= esc($staff['staff_name']) ?></td>
                                <td><?= esc($staff['phone'] ?? '') ?></td>
                                <td>
                                    <?= (int) $staff['is_active'] === 1 ? '啟用' : '停用' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>目前還沒有送餐員資料。</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>