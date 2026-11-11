{* 案主 Excel 匯入頁：由 Smarty 負責產生 HTML *}
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {* 載入專案共用 CSS *}
    <link rel="stylesheet" href="{$cssUrl}">

    <title>匯入案主 Excel</title>
</head>

<body>
    {* 整頁容器：排列左側導覽欄與主要內容 *}
    <div class="layout">

        {* 左側系統導覽欄 *}
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                {* 回到首頁 *}
                <a href="{$homeUrl}">首頁</a>

                {* 前往案主資料列表 *}
                <a href="{$clientListUrl}">案主資料</a>

                {* 前往送餐員資料列表 *}
                <a href="{$staffListUrl}">送餐員資料</a>

                {* 前往資源回收桶 *}
                <a href="{$trashUrl}">資源回收桶</a>
            </nav>

            {* 側欄底部的個人設定區 *}
            <div class="sidebar-footer">
                <details class="sidebar-settings">
                    <summary class="sidebar-settings-trigger">
                        個人設定
                    </summary>

                    <div class="sidebar-settings-panel">
                        {* 顯示目前登入者資訊 *}
                        <div class="sidebar-account-info">
                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">登入帳號</span>
                                <strong class="sidebar-account-value">
                                    {$currentUsername}
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">顯示名稱</span>
                                <strong class="sidebar-account-value">
                                    {$currentDisplayName}
                                </strong>
                            </p>

                            <p class="sidebar-account-row">
                                <span class="sidebar-account-label">角色</span>
                                <strong class="sidebar-account-value">
                                    {$currentRoleLabel}
                                </strong>
                            </p>
                        </div>

                        {* 前往修改密碼頁面 *}
                        <a
                            href="{$changePasswordUrl}"
                            class="sidebar-settings-link"
                        >
                            更新密碼
                        </a>

                        {* 使用 POST 表單登出 *}
                        <form
                            action="{$logoutUrl}"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            {* CSRF 欄位本身是 HTML，因此使用 nofilter 輸出 *}
                            {$logoutCsrfField nofilter}

                            <button type="submit" class="sidebar-logout-button">
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        {* 右側主要內容 *}
        <main class="content">

            {* 顯示頁面所在位置 *}
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="{$homeUrl}">首頁</a>

                <span class="page-breadcrumb-separator" aria-hidden="true">
                    &gt;
                </span>

                <a href="{$clientListUrl}">案主資料</a>

                <span class="page-breadcrumb-separator" aria-hidden="true">
                    &gt;
                </span>

                <span aria-current="page">匯入 Excel</span>
            </nav>

            {* 頁面標題 *}
            <h1>匯入案主 Excel</h1>

            {* 顯示匯入錯誤訊息 *}
            {if $errorMessage}
                <p class="message-error">
                    {$errorMessage}
                </p>
            {/if}

            {* Excel 匯入表單區塊 *}
            <section class="delivery-staff-card">
                <h2>選擇匯入檔案</h2>

                {* 使用 multipart/form-data 才能上傳檔案 *}
                <form
                    action="{$importPreviewUrl}"
                    method="post"
                    enctype="multipart/form-data"
                    class="delivery-staff-form"
                >
                    {* 輸出 CI4 產生的 CSRF 欄位 *}
                    {$csrfField nofilter}

                    <div class="delivery-form-field">
                        <label for="excel_file">Excel 檔案</label>

                        {* 限制選擇 Excel 格式檔案 *}
                        <input
                            type="file"
                            id="excel_file"
                            name="excel_file"
                            accept=".xlsx,.xls"
                            required
                        >

                        <small>
                            請選擇包含案主資料的 .xlsx 或 .xls 檔案。
                        </small>
                    </div>

                    {* 送出後檢查欄位並顯示資料預覽 *}
                    <button type="submit" class="btn btn-primary">
                        上傳並預覽
                    </button>

                    {* 返回案主資料列表 *}
                    <a href="{$clientListUrl}" class="btn btn-secondary">
                        取消
                    </a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>