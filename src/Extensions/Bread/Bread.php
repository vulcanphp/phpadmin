<?php

namespace VulcanPhp\PhpAdmin\Extensions\Bread;

use VulcanPhp\Core\Helpers\Inflect;
use VulcanPhp\PhpRouter\Route;

class Bread
{
    public function __construct(protected string $url, protected string $model, protected array $config = [])
    {
    }

    public static function register(...$args)
    {
        return new static(...$args);
    }

    public function __destruct()
    {
        $route = Route::resource($this->url, BreadController::class);
        $config = $this->getBreadConfig();

        if (url()->contains($this->url)) {
            $route->bread = $config;
        }

        $tag = ucfirst(Inflect::singularize($this->config['sidebar']['title']));

        phpadmin()->addSidebarMenuItem(array_merge($this->config['sidebar'], [
            'order'    => 3,
            'url'      => phpadmin_prefix() . trim($this->url, '/'),
            'subitems' => $config->hasRight('create') ? [
                ['url' => phpadmin_prefix() . trim($this->url, '/'), 'rights' => ['read'], 'title' => $tag . ' List'],
                ['url' => phpadmin_prefix() . trim($this->url, '/') . '/create', 'rights' => ['create'], 'title' => 'New ' . $tag],
            ] : null
        ]));
    }

    public function getBreadConfig(): BreadConfig
    {
        return new BreadConfig(new $this->model, $this->config);
    }
}
