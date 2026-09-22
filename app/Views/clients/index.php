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
        
        <!-- 網頁右側的主要內容 -->
        <main class="content">

            <!-- 頁面標題 -->
            <h1>案主資料管理</h1>

            <!--
                判斷 Session 中是否存在新增成功訊息
                Flashdata 只會顯示一次
            -->
            <?php if (session()->getFlashdata('success')): ?>
                <p>
                    <!-- 使用 esc() 安全顯示成功訊息 -->
                    <?= esc(session()->getFlashdata('success')) ?>
                </p>
            <?php endif ?>

            <!-- 搜尋案主區域 -->
            <section class="search-section">
                <!--
                    for="keyword" 對應輸入框的 id="keyword"
                    點擊文字時，游標會進入輸入框
                -->

                <label for="keyword">搜索案主</label>
                <!-- 案主姓名搜尋輸入框 -->

                <input type="text" name="keyword" id="keyword" placeholder="請輸入案主姓名">

                <!--
                    目前按鈕只有畫面
                    搜尋功能會在後續章節實作
                -->
                <button type="button">搜尋</button>
            </section>

            <!-- 案主資料表格區域 -->
            <section class="client-table-section">

                    <h2>案主資料列表</h2>

                    <?php if (!empty($clients)): ?>
                        <!--
                        $clients 有資料時顯示表格
                        empty() 用來判斷變數是否為空
                        ! 代表「不是」
                        所以 ! empty($clients) 代表 clients 不是空的
                        -->
                        <table class="client-table">

                            <thead>
                                <tr>
                                    <th>編號</th>
                                    <th>案主姓名</th>
                                    <th>案主地址</th>
                                    <th>路線編號</th>
                                    <th>建立時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($clients as $client): ?>

                                <?php
                                // 每次 foreach 取得一位案主後
                                // 先準備建立時間的顯示內容
                                $createdAtTaipei = '';

                                // 確認這一筆案主有 created_at
                                // 避免空值傳入 Time::parse()
                                if (! empty($client['created_at'])) {
                                    // 將資料庫的 created_at 解讀為 UTC 時間
                                    $createdAtUtc = \CodeIgniter\I18n\Time::parse(
                                        $client['created_at'],
                                        'UTC'
                                    );

                                    // 將 UTC 時間轉換成台灣時區
                                    // 這只會轉換畫面顯示，不會修改資料庫
                                    $createdAtTaipeiObject = $createdAtUtc->setTimezone(
                                        'Asia/Taipei'
                                    );

                                    // 將時間物件整理成年月日時分秒
                                    $createdAtTaipei = $createdAtTaipeiObject->format(
                                        'Y-m-d H:i:s'
                                    );
                                }
                                ?>

                                <!-- 每一筆案主建立一個表格資料列 -->
                                <tr>
                                    <!-- 第一欄：案主編號 -->
                                    <td>
                                        <?= esc($client['id']) ?>
                                    </td>

                                    <!-- 第二欄：案主姓名 -->
                                    <td>
                                        <?= esc($client['ct_name']) ?>
                                    </td>

                                    <!-- 第三欄：案主地址 -->
                                    <td>
                                        <?= esc($client['ct_address']) ?>
                                    </td>

                                    <!-- 第四欄：路線編號 -->
                                    <td>
                                        <?= esc($client['route_no']) ?>
                                    </td>

                                    <!-- 第五欄：轉換完成的台灣建立時間 -->
                                    <td>
                                        <?= esc($createdAtTaipei) ?>
                                    </td>

                                    <!-- 第六欄：操作按鈕 -->
                                    <td>
                                        <!--
                                            將案主 id 放進修改網址
                                            例如 id 是 2，網址就是 clients/edit/2
                                        -->
                                        <a
                                            class="btn btn-warning"
                                            href="<?= base_url(
                                                'clients/edit/' . $client['id']
                                            ) ?>"
                                        >
                                            修改
                                        </a>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    <?php else: ?>
                        <!-- $clients 沒有資料時顯示提示文字 -->
                        <p>目前沒有案主資料</p>
                    <?php endif; ?>
            </section>
        </main>
    </div>
</body>
    
</html>