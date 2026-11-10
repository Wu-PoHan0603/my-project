<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{$cssUrl}">
    <title>新增送餐員</title>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            <nav>
                <a href="{$homeUrl}">首頁</a>
                <a href="{$clientListUrl}">案主資料</a>
                <a href="{$staffListUrl}">送餐員資料</a>
                <a href="{$trashUrl}">資源回收桶</a>
            </nav>
        </aside>

        <main class="content">
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="{$homeUrl}">首頁</a>
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
                <a href="{$staffListUrl}">送餐員資料</a>
                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>
                <span aria-current="page">新增送餐員</span>
            </nav>

            <h1>新增送餐員</h1>

            {if !empty($errors)}
                <div class="delivery-message-error">
                    <p>請檢查以下欄位：</p>
                    <ul>
                        {foreach $errors as $message}
                            <li>{$message}</li>
                        {/foreach}
                    </ul>
                </div>
            {/if}

            <section class="delivery-staff-card">
                <form
                    action="{$staffListUrl}"
                    method="post"
                    class="delivery-staff-form"
                >
                    {$csrfField nofilter}

                    <div class="delivery-form-field">
                        <label for="staff_name">送餐員姓名</label>
                        <input
                            type="text"
                            id="staff_name"
                            name="staff_name"
                            value="{$oldStaffName}"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="delivery-form-field">
                        <label for="phone">聯絡電話</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{$oldPhone}"
                            maxlength="30"
                        >
                    </div>

                    <div class="delivery-form-field">
                        <label for="address">地址</label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{$oldAddress}"
                            maxlength="255"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        儲存送餐員
                    </button>

                    <a href="{$staffListUrl}" class="btn btn-secondary">
                        取消
                    </a>
                </form>
            </section>
        </main>
    </div>
</body>
</html>