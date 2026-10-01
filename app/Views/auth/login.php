<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>登入</title>

    <!-- 載入 public/css/style.css -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body class="login-page">
    <main class="login-card">
        <h1>登入案主管理系統</h1>
        <p class="login-description">請輸入帳號與密碼。</p>

        <!-- 顯示登入失敗訊息 -->
        <?php if ($error = session()->getFlashdata('error')): ?>
            <p class="login-error"><?= esc($error) ?></p>
        <?php endif; ?>

        <!-- 登入表單使用 POST，並加入 CSRF Token -->
        <form action="<?= base_url('login') ?>" method="post" class="login-form">
            <?= csrf_field() ?>

            <div class="login-field">
                <label for="username">帳號</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="login-field">
                <label for="password">密碼</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="login-button">登入</button>
        </form>
    </main>
</body>
</html>