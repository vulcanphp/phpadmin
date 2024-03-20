<?php

namespace VulcanPhp\PhpAdmin;

use VulcanPhp\PhpAdmin\Extensions\PhpCm\PhpCmMenu;
use VulcanPhp\PhpAdmin\Models\Option;

class PhpAdminSettings
{
    protected $collection = null, $phpcm = null, $other = null, $menus = null;

    public function __construct()
    {
        $this->Reload();
    }

    public function Reload(): void
    {
        $this->collection = Option::Cache()->load('settings', fn () => Option::getOptions('settings'));
    }

    public function Collect()
    {
        return $this->collection;
    }

    public function GetOption(...$args)
    {
        return $this->Collect()->get(...$args);
    }

    public function SaveOption(string $key, $value, string $type = 'settings'): bool
    {
        $model = new Option;
        return $model->load(['name' => $key, 'value' => encode_string($value), 'type' => $type])->save(true);
    }

    public function getPhpCm(string $section, string $key, $default = null)
    {
        if ($this->phpcm === null) {
            $this->phpcm = Option::Cache()->load('phpcm', fn () => Option::getOptions('phpcm'));
        }

        return $this->phpcm->get($section . '_' . $key, $default);
    }

    public function get(string $type, string $key, $default = null)
    {
        return $this->Load($type)->get($key, $default);
    }

    public function has(string $type, string $key): bool
    {
        return $this->get($type, $key) !== null;
    }

    public function Load(string $type)
    {
        if (!isset($this->other[$type])) {
            $this->other[$type] = Option::Cache()->load($type, fn () => Option::getOptions($type));
        }

        return $this->other[$type];
    }

    public function getMenu(string $location): array
    {
        // setup db menus
        if ($this->menus === null) {
            $this->menus = [];
            foreach (Option::Cache()->load('menus', fn () => Option::select()->where(['type' => 'menu'])->fetch(\PDO::FETCH_ASSOC)->get()->all()) as $menu) {
                $this->menus[$menu['name']][] = $menu;
            }
        }

        if (!isset($this->menus[$location])) {
            return [];
        }

        $phpcm = new PhpCmMenu([]);

        $menu = collect($this->menus[$location])->map(function ($menu) {
            $menu = array_merge($menu, decode_string($menu['value']));
            $menu['position'] = $menu['position'] ?? 0;
            $menu['url'] = is_url($menu['slug']) ? $menu['slug'] : home_url($menu['slug']);
            $menu['parent'] = isset($menu['parent']) && !empty(trim($menu['parent'])) ? intval($menu['parent']) : null;
            unset($menu['value'], $menu['type'], $menu['name'],);
            return $menu;
        })->multisort('position')->all();

        return $phpcm->prepare_menu_items($menu);
    }

    public function getSubMenuWalker(
        array $menu,
        array $config = [],
        array $filters = [],
        int $limitDept = 1,
        int &$dept = 1,
        string &$output = ''
    ) {
        $config = array_merge(['ul' => ['submenu'], 'li' => ['submenu-item'], 'a' => ['submenu-link']], $config);
        $output .= sprintf(
            '<ul class="%s %s" %s>',
            join(' ', (array)($config['ul'] ?? [])),
            $dept > 1 ? join(' ', (array)($config['ul:dropdown:class'] ?? [])) : '',
            $dept > 1 ? join(' ', (array)($config['ul:dropdown:attribute'] ?? [])) : '',
        );

        foreach ($menu as $item) {
            $submenu = isset($item['submenu']) && !empty($item['submenu']) && $limitDept > $dept;
            $output .= sprintf(
                '<li class="%s %s" %s>',
                join(' ', (array)($config['li'] ?? [])),
                $submenu ? join(' ', (array)($config['li:dropdown:class'] ?? [])) : '',
                $submenu ? join(' ', (array)($config['li:dropdown:attribute'] ?? [])) : '',
            );

            $output .= sprintf(
                '<a href="%s" class="%s %s" %s>%s</a>',
                isset($filters['url']) ? $filters['url']($item) : $item['url'],
                join(' ', (array)($config['a'] ?? [])),
                $submenu ? join(' ', (array)($config['a:dropdown:class'] ?? [])) : '',
                $submenu ? join(' ', (array)($config['a:dropdown:attribute'] ?? [])) : '',
                isset($filters['title']) ? $filters['title']($item) : $item['title']
            );

            if ($submenu) {
                $dept++;
                $this->getSubMenuWalker($item['submenu'], $config, $filters, $limitDept, $dept, $output);
            }

            $output .= '</li>';
            $dept = 1;
        }

        $output .= '</ul>';

        return $output;
    }
}
