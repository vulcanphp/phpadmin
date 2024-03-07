<?php

use VulcanPhp\Core\Helpers\Str;
use VulcanPhp\Core\Helpers\Time;
use App\Models\User;
use VulcanPhp\PhpAdmin\Extensions\Bread\Bread;
use VulcanPhp\PhpAdmin\Models\Page;
use VulcanPhp\PhpAdmin\Controllers\ToolController;
use VulcanPhp\PhpAdmin\Controllers\MediaController;
use VulcanPhp\PhpAdmin\Controllers\EditorController;
use VulcanPhp\PhpAdmin\Controllers\PhpAdminController;
use VulcanPhp\PhpRouter\Route;

// Setup Admin Routes
Route::get('/', [PhpAdminController::class, 'index'])->setName('home');
Route::form('/tools/settings/{setting?}', [ToolController::class, 'setting'])->setName('setting');

if (phpadmin_enabled('pages')) {
    Route::options('/pages/clone/{id}/', [PhpAdminController::class, 'clone'])->setName('pages.clone');
    Route::form('/editor/{slug?}', [EditorController::class, 'index'])->setName('editor');
}

if (phpadmin_enabled('tools.i18n')) {
    Route::form('/tools/i18n/', [ToolController::class, 'i18n']);
}

if (phpadmin_enabled('tools.cms')) {
    Route::form('/tools/cms/', [ToolController::class, 'cms']);
}

if (phpadmin_enabled('tools.menu')) {
    Route::form('/tools/menus/', [ToolController::class, 'menus']);
}

if (phpadmin_enabled('tools.backup')) {
    Route::form('/tools/database-backup/', [ToolController::class, 'DBBackup']);
}

if (phpadmin_enabled('tools.reset')) {
    Route::form('/tools/factory-reset/', [ToolController::class, 'factoryReset']);
}

if (phpadmin_enabled('tools.filemanager')) {
    Route::form('/tools/filemanager/', [MediaController::class, 'filemanager']);
    Route::form('/media/{action}/', [MediaController::class, 'index'])->setName('media');
}

// Setup Admin Breads
if (phpadmin_enabled('users') && isSuperAdmin()) {
    Bread::register(
        '/users',
        User::class,
        [
            'sidebar'  => [
                'title'    => 'Users',
                'icon'     => 'user',
            ],
            'columns'   => ['id', 'photo', 'name', 'email', 'role', 'joinded_at'],
            'formatter' => [
                'photo'      => ['email', fn ($email, $rows, $ssp) => $ssp->module('avatar', gravatar($email))],
                'joinded_at' => ['created_at', fn ($datetime) => Time::format($datetime)]
            ],
            'form_fields' => url()->contains('/users/') ? [
                ['field' => 'addInput', 'name' => 'name', 'label' => 'User\'s Information'],
                ['field' => 'addInput', 'name' => 'username'],
                ['field' => 'addInput', 'type' => 'email', 'name' => 'email'],
                ['field' => 'addInput', 'type' => 'password', 'name' => 'password', 'value' => ''],
                ['field' => 'addSelect', 'name' => 'role', 'options' => collect(config('auth.roles'))->mapWithKeys(fn ($role) => [$role => Str::read($role)])->all()],
                ['field' => 'addSelect', 'name' => 'status', 'options' => array_combine(array_values(config('auth.status')), array_map(fn ($role) => ucwords($role), array_keys(config('auth.status'))))],
            ] : []
        ]
    );
}

if (phpadmin_enabled('pages')) {
    Bread::register(
        '/pages',
        Page::class,
        [
            'sidebar' => [
                'title' => 'Pages',
                'icon' => 'file-blank',
            ],
            'columns' => ['id', 'pause:title', 'slug', 'editor'],
            'formatter' => [
                'slug' => fn ($slug, $rows) => sprintf('<a href="%s" target="_blank">%s</a>', home_url($slug), $rows[1]),
                'editor' => ['id', function ($editor, $rows) {
                    return sprintf(
                        '<a href="%s" class="items-center" style="display:inline-flex; text-decoration:underline">%s <span class="ml-1">%s</span></a>',
                        phpadmin_url('/editor/' . trim($rows[2], '/')),
                        icon('link-external'),
                        translate('Open: Editor')
                    );
                }]
            ],
            'filters' => [
                'show' => function ($model) {
                    $model->load(['body' => translate('(Can\'t Shown)')]);
                    return $model;
                },
            ],
            'action_before' => ['clone' => true],
            'form_fields' => url()->contains('/pages/') ? [
                ['field' => 'addInput', 'name' => 'title', 'label' => 'Page\'s Information'],
                ['field' => 'addInput', 'name' => 'slug'],
                ['field' => 'addTextarea', 'name' => 'excerpt'],
                ['field' => 'addTextarea', 'name' => 'body', 'type' => 'hidden'],
                ['field' => 'addSelect', 'name' => 'editor', 'options' => collect(Page::EDITORS)->mapWithKeys(fn ($name, $key) => [$key => translate($name)])->all()],
                ['field' => 'addMedia', 'name' => 'thumbnail', 'description' => 'Choose & Upload Page Thumbnail']
            ] : []
        ]
    );
}
