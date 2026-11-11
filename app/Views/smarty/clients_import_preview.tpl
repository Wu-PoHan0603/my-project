{* Excel 匯入預覽頁：目前只顯示資料，不寫入資料庫 *}
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {* 載入專案共用 CSS *}
    <link rel="stylesheet" href="{$cssUrl}">

    <title>Excel 匯入預覽</title>
</head>

<body>
    {* 整頁版面容器 *}
    <div class="layout">

        {* 左側導覽欄 *}
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <a href="{$homeUrl}">首頁</a>
                <a href="{$clientListUrl}">案主資料</a>
            </nav>
        </aside>

        {* 右側主要內容 *}
        <main class="content">

            {* 麵包屑導覽 *}
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="{$homeUrl}">首頁</a>
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
                <a href="{$clientListUrl}">案主資料</a>
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
                <span aria-current="page">Excel 匯入預覽</span>
            </nav>

            {* 頁面標題 *}
            <h1>Excel 匯入預覽</h1>

            {* 顯示檔案名稱與讀取筆數 *}
            <section class="delivery-staff-card">
                <p>檔案名稱：{$fileName}</p>
                <p>讀取資料筆數：{$totalRows}</p>

                {* 有資料時顯示預覽表格 *}
                {if !empty($previewRows)}
                    <table class="delivery-staff-table">
                        <thead>
                            <tr>
                                <th>Excel 列號</th>
                                <th>案主姓名</th>
                                <th>案主地址</th>
                                <th>路線編號</th>
                                <th>檢查結果</th>
                            </tr>
                        </thead>

                        <tbody>
                            {* 逐筆顯示 Excel 讀取結果 *}
                            {foreach $previewRows as $row}
                                <tr>
                                    <td>{$row.rowNumber}</td>
                                    <td>{$row.ct_name}</td>
                                    <td>{$row.ct_addr}</td>
                                    <td>{$row.route_no}</td>
                                    <td>
                                        {if $row.errorText}
                                            {$row.errorText}
                                        {else}
                                            通過基本檢查
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {else}
                    <p>Excel 中沒有可預覽的資料列。</p>
                {/if}

                {* 返回檔案選擇頁 *}
                <a href="{$importUrl}" class="btn btn-secondary">
                    返回重新選擇
                </a>

                {* 只有全部資料都通過檢查時，才顯示確認匯入按鈕 *}
                {if !$hasErrors}
                    <form
                        action="{$confirmImportUrl}"
                        method="post"
                        class="action-form"
                        onsubmit="return confirm('確定要更新這些案主資料嗎？')"
                    >
                        {* 輸出 CI4 產生的 CSRF 欄位 *}
                        {$csrfField nofilter}

                        <button type="submit" class="btn btn-primary">
                            確認匯入並更新
                        </button>
                    </form>
                {else}
                    <p class="message-error">
                        資料有錯誤，請返回修改 Excel 後重新上傳。
                    </p>
                {/if}
            </section>
        </main>
    </div>
</body>
</html>