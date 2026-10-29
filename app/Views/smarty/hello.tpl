{* 這是 Smarty 模板註解，不會顯示在網頁上 *}
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
</head>
<body>
    {* 顯示 Controller 傳入的標題 *}
    <h1>{$title}</h1>

    <table>
        <thead>
            <tr>
                <th>編號</th>
                <th>案主姓名</th>
                <th>案主地址</th>
                <th>路線編號</th>
            </tr>
        </thead>
        <tbody>
            {* 逐筆讀取 clients 陣列，每筆資料暫存在 client 變數 *}
            {foreach $clients as $client}
                <tr>
                    {* 顯示目前這筆資料的 id 欄位 *}
                    <td>{$client.id}</td>

                    {* 顯示目前這筆資料的 name 欄位 *}
                    <td>{$client.name}</td>

                    {* 顯示目前這筆資料的 address 欄位 *}
                    <td>{$client.address}</td>

                    {* 顯示目前這筆資料的 route_no 欄位 *}
                    <td>{$client.route_no}</td>
                </tr>
            {foreachelse}
                {* clients 陣列是空的時，顯示這一列 *}
                <tr>
                    <td colspan="4">目前沒有測試案主資料</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</body>
</html>