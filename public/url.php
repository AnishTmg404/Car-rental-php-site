<?php

// if (!function_exists('base_url')) {
//     function base_url(string $path = ''): string {
//         if (php_sapi_name() === 'cli') {
//             return $path;
//         }
//         if (defined('BASE_URL') && is_string(BASE_URL) && BASE_URL !== '') {
//             $base = rtrim(BASE_URL, '/') . '/';
//             return $base . ltrim($path, '/');
//         }

//         $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
//                  || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
//         $scheme = $https ? 'https' : 'http';
//         $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

//         $script = $_SERVER['SCRIPT_NAME'] ?? '/';
//         $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');

//         $base = $scheme . '://' . $host . ($dir === '' ? '/' : $dir . '/');
//         $path = ltrim($path, '/');

//         return $base . $path;
//     }
// }


if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        if (php_sapi_name() === 'cli') {
            return $path;
        }
        if (defined('BASE_URL') && is_string(BASE_URL) && BASE_URL !== '') {
            $base = rtrim(BASE_URL, '/') . '/';
            return $base . ltrim($path, '/');
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // 🔧 FIX: Force base to the /public folder instead of the current script directory
        $base = $scheme . '://' . $host . '/Car-rental-php/public/';

        $path = ltrim($path, '/');
        return $base . $path;
    }
}



/**
 * asset_url('css/styles.css') => base_url('assets/css/styles.css')
 */
function asset_url(string $path = ''): string {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Returns the current request path relative to the public folder.
 * Examples: '/', '/admin', '/cars/123'
 */
function current_path(): string {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    if ($scriptDir !== '' && strpos($uri, $scriptDir) === 0) {
        $uri = substr($uri, strlen($scriptDir));
    }

    $clean = '/' . trim($uri, '/');
    return $clean === '/' ? '/' : $clean;
}

/**
 * Simple route matcher.
 * - Exact match: route_match('/admin')
 * - Prefix wildcard: route_match('/admin/*') matches '/admin', '/admin/dashboard', '/admin/users/1'
 */
function route_match(string $pattern, ?string $path = null): bool {
    $path = $path ?? current_path();
    $pattern = rtrim($pattern, '/');

    if (substr($pattern, -2) === '/*') {
        $prefix = rtrim(substr($pattern, 0, -2), '/');
        if ($prefix === '') return true;
        return $path === '/' . $prefix || strpos($path, '/' . $prefix . '/') === 0;
    }

    return $path === ('/' . ltrim($pattern, '/')) || $path === $pattern;
}

/**
 * Redirect helper. Accepts absolute URL or relative path (relative to base_url).
 */
function url_redirect(string $to, int $status = 302): void {
    $location = (strpos($to, '://') !== false) ? $to : base_url(ltrim($to, '/'));
    header('Location: ' . $location, true, $status);
    exit;
}
?>