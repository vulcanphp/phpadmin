<?php

use VulcanPhp\Core\Auth\Auth;
use VulcanPhp\Core\Helpers\Arr;
use VulcanPhp\PhpAdmin\Models\Page;
use VulcanPhp\PhpAdmin\PhpAdminSettings;

// PhpAdmin helper functions <START>

if (!function_exists('gravatar')) {
    function gravatar(string $email, int $size = 64)
    {
        $hash = md5(strtolower(trim($email)));
        return sprintf("https://www.gravatar.com/avatar/%s.jpg?s=%s&d=mp", $hash, $size);
    }
}

if (!function_exists('thumb_size')) {
    function thumb_size(?string $file = null, ?string $size = null): ?string
    {
        if ($file !== null || $size !== null) {
            $div = explode('.', $file);
            $ext = array_pop($div);

            return sprintf("%s-%s.%s", join('.', $div), $size, $ext);
        }

        return null;
    }
}

if (!function_exists('load_json')) {
    $json_data = array();
    function load_json(string $name, ?string $key = null, $default = null)
    {
        global $json_data;

        if (!isset($json_data[$name])) {
            $filepath = sprintf('%s/../resources/json/%s.json', __DIR__, $name);

            if (!file_exists($filepath)) {
                throw new \Exception(sprintf('File Dosen\'t exists [%s] on (%s)', $name, $filepath));
            }

            $json_data[$name] = @json_decode(file_get_contents($filepath), true);
        }

        if ($key !== null && isset($json_data[$name]) && $json_data[$name] !== null) {
            return VulcanPhp\Core\Helpers\Arr::get($json_data[$name], $key, $default);
        }

        return $json_data[$name];
    }
}

if (!function_exists('icon')) {
    function icon(string $name, array $attrs = [])
    {
        if (strpos($name, '.') !== false) {
            $type = substr($name, 0, strpos($name, '.'));
            $name = trim(substr($name, strpos($name, '.')), '.');
            $name = $type . '-' . $name;
        }

        if (stripos($name, 'bx-') === false && stripos($name, 'bxs-') === false) {
            $name = 'bx-' . $name;
        }

        $attrs['class'] = 'bx ' . $name . (isset($attrs['class']) ? ' ' . $attrs['class'] : '');

        return sprintf('<i %s></i>', join(' ', array_map(fn ($key, $value) => sprintf('%s="%s"', $key, $value), array_keys($attrs), array_values($attrs))));
    }
}

if (!function_exists('inet_aton')) {
    function inet_aton(string $ip): int|false
    {
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return false;
        return sprintf("%u", ip2long($ip));
    }
}

if (!function_exists('inet_ntoa')) {
    function inet_ntoa(string $num): string|false
    {
        $num = intval(trim($num));
        if ($num < 1) return false;
        return long2ip(sprintf("%d", $num));
    }
}


if (!function_exists('settings_url')) {
    function settings_url($id)
    {
        return phpadmin_url('tools/settings/' . $id);
    }
}

if (!function_exists('is_current_setting')) {
    function is_current_setting($id)
    {
        return url()
            ->contains(phpadmin_prefix() . 'tools/settings/' . $id);
    }
}


if (!function_exists('phpadmin')) {
    function phpadmin()
    {
        return app()->getComponent('phpadmin');
    }
}

if (!function_exists('phpadmin_view')) {
    function phpadmin_view(string $template, array $params = [])
    {
        return view()
            ->getDriver()
            ->getEngine()
            ->resourceDir(dirname(__DIR__) . '/resources/views/')
            ->template($template)
            ->render($params);
    }
}

if (!function_exists('phpadmin_views_dir')) {
    function phpadmin_layout_dir(): string
    {
        return dirname(__DIR__) . '/resources/views/layout.php';
    }
}

if (!function_exists('phpadmin_views_dir')) {
    function phpadmin_views_dir(): string
    {
        return dirname(__DIR__) . '/resources/views';
    }
}

if (!function_exists('phpadmin_enabled')) {
    function phpadmin_enabled(string $key): bool
    {
        if (strpos($key, '.') !== false) {
            $stage  = substr($key, 0, strpos($key, '.'));
            $key    = substr($key, strpos($key, '.') + 1);
            $map    = config('phpadmin.disabled.' . $stage, []);
        } else {
            $map = config('phpadmin.disabled', []);
        }

        return !in_array($key, $map);
    }
}

if (!function_exists('phpadmin_resource')) {
    function phpadmin_resource(string $url)
    {
        return home_url('vendor/vulcanphp/phpadmin/src/resources/' . trim($url, '/'));
    }
}

if (!function_exists('phpadmin_prefix')) {
    function phpadmin_prefix()
    {
        return config('phpadmin.prefix') === '/' ? '/' : '/' . trim(config('phpadmin.prefix'), '/') . '/';
    }
}

if (!function_exists('phpadmin_url')) {
    function phpadmin_url(?string $suffix = ''): string
    {
        return home_url(phpadmin_prefix() . trim(strval($suffix), '/'));
    }
}

// PhpAdmin helper functions <END>

// ====================================== //

// SimpleAuth helper functions <START>

if (!function_exists('auth')) {
    function auth(): Auth
    {
        return app()->getComponent('auth');
    }
}

if (!function_exists('user')) {
    function user(?string $var = null)
    {
        return $var !== null ? auth()->getUser()?->{$var} : auth()->getUser();
    }
}

if (!function_exists('hasRights')) {
    function hasRights($rights)
    {
        return Arr::hasAllValues(config('auth.rights')[user()?->role] ?? [], (array) $rights);
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin()
    {
        return hasRights('super');
    }
}


if (!function_exists('authView')) {
    function authView(string $template, array $params = [])
    {
        return view()
            ->getDriver()
            ->getEngine()
            ->resourceDir(__DIR__ . '/../Extensions/SimpleAuth/resources/views/')
            ->template($template)
            ->render($params);
    }
}

if (!function_exists('auth_resource')) {
    function auth_resource(string $url)
    {
        return home_url('vendor/vulcanphp/phpadmin/src/Extensions/SimpleAuth/resources/' . trim($url, '/'));
    }
}

if (!function_exists('auth_prefix')) {
    function auth_prefix()
    {
        return '/' . trim(config('auth.prefix'), '/') . '/';
    }
}

if (!function_exists('auth_url')) {
    function auth_url(string $action): string
    {
        return home_url(auth_actions()[$action]);
    }
}

if (!function_exists('auth_actions')) {
    function auth_actions(): array
    {
        return array_map(fn ($action) => auth_prefix() . trim($action, '/') . '/', config('auth.urls'));
    }
}

if (!function_exists('auth_enabled')) {
    function auth_enabled(string $action): bool
    {
        return !in_array($action, (array) config('auth.disabled', ''));
    }
}

// SimpleAuth helper functions <END>

// ====================================== //

// Global Settings functions <START>

if (!function_exists('setting')) {
    function setting(...$args)
    {
        $setting = bucket()->load('settings', fn () => new PhpAdminSettings);

        if (empty($args)) {
            return $setting;
        }

        return $setting->GetOption(...$args);
    }
}

if (!function_exists('sitekit')) {
    function sitekit(...$args)
    {
        return setting()->get('sitekit', ...$args);
    }
}

if (!function_exists('save_settings')) {
    function save_settings(...$args): bool
    {
        return setting()->SaveOption(...$args);
    }
}

if (!function_exists('phpcm')) {
    function phpcm(...$args)
    {
        return setting()->getPhpCm(...$args);
    }
}

if (!function_exists('menu')) {
    function menu(...$args)
    {
        return setting()->getMenu(...$args);
    }
}

if (!function_exists('sub_menu_walker')) {
    function sub_menu_walker(...$args)
    {
        return setting()->getSubMenuWalker(...$args);
    }
}

if (!function_exists('parse_phpcm_menu_items')) {
    function parse_phpcm_menu_items(array $menu): array
    {
        $pages = [];

        foreach ($menu as &$item) {
            if (isset($item['page']) && __is_int($item['page'])) {
                $pages[] = $item['page'];
            }
        }

        $pages = Page::Cache()
            ->load(
                'cached_menu' . join('_', $pages),
                fn () => Page::select('id, title, slug')
                    ->whereIn('id', $pages)
                    ->fetch(\PDO::FETCH_ASSOC)
                    ->get()
                    ->all()
            );

        foreach ($menu as &$item) {
            if (isset($item['page']) && intval($item['page']) > 0) {
                foreach ($pages as $page) {
                    if ($page['id'] == $item['page']) {
                        $item = [
                            'menu_title' => $page['title'],
                            'menu_url' => $page['slug']
                        ];

                        break;
                    }
                }
            }
        }

        return $menu;
    }
}

// Global Settings functions <END>
