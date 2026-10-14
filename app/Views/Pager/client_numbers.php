<?php
/**
 * CI4 載入分頁模板時會提供 $pager。
 * 這行註解是告訴 Intelephense $pager 的型別。
 * CI4 官方 API 說明，PagerRenderer 會傳給分頁模板使用。
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
// 顯示目前頁碼前後各 3 個數字連結
$pager->setSurroundCount(3);
?>

<nav aria-label="案主列表頁碼">
    <ul class="client-page-list">
        <?php foreach ($pager->links() as $link): ?>
            <li class="<?= $link['active'] ? 'active' : '' ?>">
                <a
                    href="<?= esc($link['uri'], 'attr') ?>"
                    <?= $link['active'] ? 'aria-current="page"' : '' ?>
                >
                    <?= esc((string) $link['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>