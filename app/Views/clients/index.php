<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>案主資料管理</title>
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
                <a href="#">資源回收桶</a>
                <a href="#">登出</a>
            </nav>
        </aside>
        
        <main class="content">
            <h1>案主資料管理</h1>
            
            <label for="keyword">搜索案主</label>
            <input type="text" name="keyword" id="keyword" placeholder="請輸入案主姓名">
            <button type="button">搜尋</button>
        </main>
    </div>
</body>
    
</html>