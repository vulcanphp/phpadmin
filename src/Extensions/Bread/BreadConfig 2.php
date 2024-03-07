<?php

namespace VulcanPhp\PhpAdmin\Extensions\Bread;

use VulcanPhp\SimpleDb\Model;

class BreadConfig
{
    const DEFAULT_CONFIG = [
        'sidebar'   => [
            'title' => 'Bread',
            'icon'  => 'cube'
        ],
        'where'     => [],
        'joins'     => [],
        'view_map'  => [],
        'columns'   => ['id'],
        'override'  => [],
        'filters'   => [],
        'editor_config' => [
            'width' => 'sm',
            'create_title' => 'Create New Record',
            'edit_title' => 'Update Record',
            'show_title' => 'Record Information'
        ]
    ];

    public function __construct(protected Model $model, protected array $settings = [])
    {
        $this->settings = array_merge(self::DEFAULT_CONFIG, $this->settings);
    }

    public function hasOverride(string $key): bool
    {
        return isset($this->getConfig('override')[$key]);
    }

    public function applyOverride(string $key, ...$args)
    {
        return call_user_func($this->getConfig('override')[$key], ...$args);
    }

    public function hasFilter(string $key): bool
    {
        return isset($this->getConfig('filters')[$key]);
    }

    public function applyFilter(string $key, ...$args)
    {
        return call_user_func($this->getConfig('filters')[$key], ...$args);
    }

    public function getViewMap(string $key): ?string
    {
        return $this->getConfig('view_map')[$key] ?? null;
    }

    public function getCondition(): ?array
    {
        return $this->getConfig('where');
    }

    public function getJoins(): ?array
    {
        return $this->getConfig('joins');
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
