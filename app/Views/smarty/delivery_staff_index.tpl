{* 送餐員列表頁面：由 Smarty 負責產生 HTML *}
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {* 載入專案共用 CSS *}
    <link rel="stylesheet" href="{$cssUrl}">

    <title>送餐員資料</title>
</head>

<body>
    {* 整頁版面容器：排列側邊欄與主要內容 *}
    <div class="layout">

        {* 系統左側導覽欄 *}
        <aside class="sidebar">
            <h2>案主管理系統</h2>

            {* 系統主要導覽選單 *}
            <nav aria-label="主要導覽">
                {* 前往首頁 *}
                <a href="{$homeUrl}">首頁</a>

                {* 案主管理選單 *}
                <details class="sidebar-menu-group">
                    <summary>案主管理</summary>
                    <div class="sidebar-submenu">
                        <a href="{$clientListUrl}">案主資料</a>

                        {* 管理員與工作人員可以使用 Excel 匯入 *}
                        {if $canManageStaff}
                            <a href="{$clientImportUrl}">匯入 Excel</a>
                        {/if}
                    </div>
                </details>

                {* 送餐員管理選單；目前頁面載入時展開 *}
                <details class="sidebar-menu-group" open>
                    <summary>送餐員管理</summary>
                    <div class="sidebar-submenu">
                        <a href="{$staffListUrl}">送餐員資料</a>
                        <a href="{$deliveryRoutesUrl}">路線指派</a>
                    </div>
                </details>

                {* 前往資源回收桶 *}
                <a href="{$trashUrl}">資源回收桶</a>
            </nav>

            {* 側欄底部的個人設定區 *}
            <div class="sidebar-footer">
                <details class="sidebar-settings">

                    {* 點選後展開個人設定 *}
                    <summary class="sidebar-settings-trigger">個人設定</summary>

                    <div class="sidebar-settings-panel">

                        {* 顯示登入者資訊 *}
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

                        {* 前往更新密碼頁面 *}
                        <a
                            href="{$changePasswordUrl}"
                            class="sidebar-settings-link"
                        >
                            更新密碼
                        </a>

                        {* 登出使用 POST 表單 *}
                        <form
                            action="{$logoutUrl}"
                            method="post"
                            class="sidebar-logout-form"
                        >
                            {* 輸出 CI4 產生的 CSRF 欄位 *}
                            {$logoutCsrfField nofilter}

                            <button type="submit" class="sidebar-logout-button">
                                登出
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        {* 網頁右側主要內容 *}
        <main class="content">

            {* 麵包屑導覽 *}
            {* 顯示目前頁面的導覽路徑 *}
            <nav class="page-breadcrumb" aria-label="麵包屑導覽">
                <a href="{$homeUrl}">首頁</a>

                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <span>送餐員管理</span>

                <span class="page-breadcrumb-separator" aria-hidden="true">&gt;</span>

                <span aria-current="page">送餐員資料</span>
            </nav>

            {* 頁面標題 *}
            <h1>送餐員資料</h1>

            {* 新增成功時顯示提示 *}
            {if $successMessage}
                <p class="delivery-message-success">
                    {$successMessage}
                </p>
            {/if}

            {* 有欄位錯誤時逐項顯示 *}
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

            {* 顯示送餐員資料列表 *}
            <section class="delivery-staff-card">
                <div class="delivery-staff-list-heading">
                    <h2>送餐員資料列表</h2>

                    {* 只有管理員與工作人員能新增送餐員 *}
                    {if $canManageStaff}
                        <a href="{$createStaffUrl}" class="btn btn-danger">
                            新增送餐員
                        </a>
                    {/if}
                </div>

                {* 有送餐員資料時顯示表格 *}
                {if !empty($staffList)}
                    <table class="delivery-staff-table">
                        <thead>
                            <tr>
                                <th>編號</th>
                                <th>送餐員姓名</th>
                                <th>聯絡電話</th>
                                <th>地址</th>
                                <th>狀態</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            {* 逐筆顯示 Controller 傳入的送餐員資料 *}
                            {foreach $staffList as $staff}
                                <tr>
                                    <td>{$staff.id}</td>
                                    <td>{$staff.staff_name}</td>
                                    <td>{$staff.phone|default:''}</td>
                                    <td>{$staff.address|default:''}</td>
                                    <td>
                                        {if $staff.is_active == 1}
                                            啟用
                                        {else}
                                            停用
                                        {/if}
                                    </td>
                                    <td>
                                        <div class="action-buttons">

                                            {* 所有可查看資料的使用者都能開啟詳細資料 *}
                                            <a
                                                href="{$staffListUrl}/{$staff.id}/detail"
                                                class="btn btn-primary"
                                            >
                                                詳細資料
                                            </a>

                                            {* 只有管理員與工作人員能執行管理操作 *}
                                            {if $canManageStaff}

                                                {* 前往該送餐員的修改頁面 *}
                                                <a
                                                    href="{$staffListUrl}/{$staff.id}/edit"
                                                    class="btn btn-warning"
                                                >
                                                    修改
                                                </a>

                                                {* 前往路線指派頁 *}
                                                <a
                                                    href="{$deliveryRoutesUrl}"
                                                    class="btn btn-primary"
                                                >
                                                    路線指派
                                                </a>

                                                {* 刪除會修改資料，因此使用 POST 表單送出 *}
                                                <form
                                                    action="{$staffListUrl}/delete/{$staff.id}"
                                                    method="post"
                                                    class="action-form"
                                                    onsubmit="return confirm('確定要永久刪除這位送餐員嗎？')"
                                                >
                                                    {* 輸出 CI4 產生的 CSRF 欄位 *}
                                                    {$csrfField nofilter}

                                                    <button type="submit" class="btn btn-danger">
                                                        刪除
                                                    </button>
                                                </form>
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {else}
                    <p>目前還沒有送餐員資料。</p>
                {/if}
            </section>
        </main>
    </div>
</body>
</html>