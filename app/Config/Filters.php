<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /**
     * 設定 Filter 簡稱與實際類別的對應。
     *
     * 例如：
     * auth 代表 App\Filters\AuthFilter
     * role 代表 App\Filters\RoleFilter
     *
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,

        // 檢查使用者是否已登入
        'auth'          => \App\Filters\AuthFilter::class,

        // 檢查使用者角色是否有權限
        'role'          => \App\Filters\RoleFilter::class,
    ];

    /**
     * 設定每個請求都必須執行的 Filter。
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /**
     * 設定全站執行的 Filter。
     *
     * @var array{
     *     before: array<string, array{except: list<string>|string}>|list<string>,
     *     after: array<string, array{except: list<string>|string}>|list<string>
     * }
     */
    public array $globals = [
        'before' => [
            // 所有請求都先執行 CSRF 驗證
            'csrf',
        ],
        'after' => [],
    ];

    /**
     * 依 HTTP 方法套用的 Filter。
     *
     * 目前沒有使用方法層級的 Filter。
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * 依網址套用的 Filter。
     *
     * 進入案主相關網址時，先確認使用者已登入。
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        'auth' => [
            'before' => [
                'clients',
                'clients/*',
            ],
        ],
    ];
}