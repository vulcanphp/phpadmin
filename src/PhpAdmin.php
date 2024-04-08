<?php

namespace VulcanPhp\PhpAdmin;

use VulcanPhp\PhpAdmin\Middlewares\PhpAdminMiddleware;
use VulcanPhp\Core\Helpers\Arr;
use VulcanPhp\PhpRouter\Route;

class PhpAdmin
{
    public function __construct(protected array $config = [])
    {
    }

    public function addSidebarMenuItem(array $item): self
    {
        if (!isset($item['order'])) {
            $item['order'] = 1;
        }

        $this->config['sidebar'][] = $item;

        return $this;
    }

    public function addSidebarSubMenuItem(string $url, array $item): self
    {
        foreach ($this->config['sidebar'] as &$menu) {
            if (trim($menu['url'], '/') == trim($url, '/')) {
                if (!isset($menu['subitems'])) {
                    $menu['subitems'] = [];
                }

                $menu['subitems'][] = $item;
            }
        }

        return $this;
    }

    public function getSidebarMenuItem(): array
    {
        return Arr::multisort($this->config['sidebar'], 'order');
    }

    public function getSettings(): array
    {
        return $this->config['settings'] ?? [];
    }

    public function addSetting(string $key, array $setting): self
    {
        $this->config['settings'][$key] = $setting;

        return $this;
    }

    public function addWidget(array $widget): self
    {
        $this->config['settings']['widgets'][] = $widget;
        return $this;
    }

    public function getWidgets(): array
    {
        return $this->config['settings']['widgets'] ?? [];
    }

    public function set($key, $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function addPhpCm(string $id, array $config): self
    {
        return $this->set('php_cm', array_merge($this->get('php_cm', []), [$id => $config]));
    }

    public function addFilter(string $id, ...$callbacks): self
    {
        return $this->set('filters_' . $id, array_merge($this->get('filters_' . $id, []), $callbacks));
    }

    public function applyFilter(string $id, $data)
    {
        foreach ($this->get('filters_' . $id, []) as $callback) {
            $data = call_user_func($callback, $data);
        }

        return $data;
    }

    public function registerSiteMenu(array $menu): self
    {
        return $this->set('site_menus', array_merge($this->get('site_menus', []), [$menu]));
    }

    public function registerRoutes($callback): void
    {
        Route::group(['middlewares' => [PhpAdminMiddleware::class], 'prefix' => phpadmin_prefix(), 'name' => 'admin.'], $callback);
    }
}
