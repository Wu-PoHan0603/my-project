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
                <a href="<?= base_url('clients/trash') ?>">資源回收桶</a>
                
                <!-- 用 POST 表單取代原本的登出連結 -->
                <form action="<?= base_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-secondary">登出</button>
                </form>
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
                <p class="message-success">
                    <!-- 使用 esc() 安全顯示成功訊息 -->
                    <?= esc(session()->getFlashdata('success')) ?>
                </p>
            <?php endif ?>

            <?php if (session()->getFlashdata('error')):  ?>
                <p class="message-error">
                    <!-- 顯示 Controller 傳來的一次性錯誤訊息esc() 防止訊息被當成 HTML 執行 -->
                    <?= esc(session()->getFlashdata('error')) ?>
                </p>
            <?php endif ?>

            <!-- 搜尋案主區域 -->
            <!-- 搜尋表單使用GET action指定送回/clients -->
            <form action="<?= base_url('clients') ?>" class="search-form" method="get">
                <!-- label的for對應輸入框id 點擊文字時，游標會進入輸入框 -->
                <label for="keyword">搜索案主</label>

                <!-- name="keyword" 是送給Controller 的欄位名稱 value 顯示目前搜尋關鍵字 搜尋後輸入框不會變回空白 -->
                <input type="text" id="keyword" name="keyword" value="<?= esc(old('keyword', $keyword ?? '')) ?>" placeholder="請輸入案主名稱">

                <!-- 送出GET搜尋請求 -->
                <button type="submit" class="btn btn-primary">搜尋</button>

                <!-- 清除搜尋條件 直接回到沒有keyword的/clients -->
                <a href="<?= base_url('clients') ?>" class="btn btn-secondary">清除</a>
            </form>

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
                                        <!-- action-buttons是按鈕排列容器 CSS會讓容器裡的按鈕水平並排 -->
                                        <div class="action-buttons">
                                            
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
                                            <!-- 刪除會改變資料庫所以使用 POST 表單，不能使用一般 GET 超連結 -->
                                            <form 
                                                action="<?= base_url('clients/delete/' . $client['id']) ?>" 
                                                class="delete-from" 
                                                method="post" 
                                                onsubmit="return confirm('確定要刪除這位案主嗎?')">
    
                                            <!-- 產生 CSRF Token防止其他網站冒用使用者身分送出刪除請求 -->
                                            <?= csrf_field() ?>
    
                                            <!-- 送出刪除表單 -->
                                            <button type="submit" class="btn btn-danger">刪除</button> 
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                        </table>
                        <!-- 顯示 clients 這組分頁連結 -->
                        <?= isset($pager) ? $pager->links('clients') : '' ?>
                    <?php else: ?>
                        <!-- $clients 沒有資料時顯示提示文字 -->
                        <p>目前沒有案主資料</p>
                    <?php endif; ?>
            </section>
        </main>
    </div>
</body>
    
</html>