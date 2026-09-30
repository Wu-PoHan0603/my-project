<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>登入</title>
</head>
<body>
    <h1>登入案主管理系統</h1>

    <!-- 顯示登入失敗訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <p style="color: red;">
            <?= esc(session()->getFlashdata('error')) ?>
        </p>
    <?php endif; ?>

    <!-- 使用 POST 傳送帳號密碼，並加入 CSRF Token -->
    <form action="<?= base_url('login') ?>" method="post">
        <?= csrf_field() ?>

        <div>
            <label for="username">帳號</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div>
            <label for="password">密碼</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">登入</button>
    </form>
</body>
</html>