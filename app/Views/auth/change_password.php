<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改密碼</title>

    <!-- 載入 public/css/style.css -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>

<body class="password-page">
    <main class="password-card">
        <header class="password-header">
            <p class="password-eyebrow">帳號安全</p>
            <h1>修改密碼</h1>
            <p class="password-description">請輸入目前密碼，再設定新的登入密碼。</p>
        </header>

        <!-- 顯示目前密碼錯誤等一般訊息 -->
        <?php if ($error = session()->getFlashdata('error')): ?>
            <p class="password-alert password-alert-error">
                <?= esc($error) ?>
            </p>
        <?php endif; ?>

        <!-- 逐條顯示伺服器端驗證錯誤 -->
        <?php $errors = session()->getFlashdata('errors') ?? []; ?>
        <?php if (! empty($errors)): ?>
            <ul class="password-alert password-alert-error">
                <?php foreach ($errors as $message): ?>
                    <li><?= esc($message) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- 使用 POST 傳送密碼，並加入 CSRF Token -->
        <form
            action="<?= base_url('change-password') ?>"
            method="post"
            class="password-form"
        >
            <?= csrf_field() ?>

            <div class="password-field">
                <label for="current_password">目前密碼</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="password-field">
                <label for="new_password">新密碼</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                    required
                >
                <small>新密碼至少需要 8 個字元。</small>
            </div>

            <div class="password-field">
                <label for="confirm_password">再次輸入新密碼</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="password-actions">
                <button type="submit" class="password-button password-button-primary">
                    更新密碼
                </button>

                <a
                    href="<?= base_url('clients') ?>"
                    class="password-button password-button-secondary"
                >
                    返回列表
                </a>
            </div>
        </form>
    </main>
</body>
</html>