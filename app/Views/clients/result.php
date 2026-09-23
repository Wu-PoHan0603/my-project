<?php /** @var int|null $route_no */ ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增案主結果</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">    
</head>

<body>
    <div class="layout">

        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <a href="#">首頁</a>
                <a href="<?= base_url('clients') ?>">案主資料</a>
                <a href="<?= base_url('clients/create') ?>">新增案主</a>
                <a href="<?php base_url('clients/trash') ?>">資源回收桶</a>
                <a href="#">登出</a>
            </nav>
        </aside>

        <main class="content">
            <h1>接收到的案主資料</h1>

            <p>案主姓名：<?= esc($ct_name ?? '') ?></p>
            <p>案主地址：<?= esc($ct_address ?? '') ?></p>
            <p>路線編號：<?= esc($route_no ?? '') ?></p>

            <a href="<?= base_url('clients/create') ?>">返回新增畫面</a>
        </main>

    </div>
</body>

</html>