<?php

use VulcanPhp\PhpAdmin\Models\Profile;
use VulcanPhp\PhpAdmin\Models\Settings;

// register default phpadmin routes
phpadmin()->registerRoutes(__DIR__ . '/routes.php');

$setting_index = url()->is(phpadmin_prefix() . 'tools/settings');

// register default settings
if (phpadmin_enabled('settings.general') && isSuperAdmin()) {
    phpadmin()->addSetting('general', is_current_setting('general') || $setting_index ? [
        'title' => 'General Settings',
        'callback' => [(new Settings)->load(setting()->Collect()->all()), 'SyncOptions'],
        'form_fields' => [
            ['field' => 'addInput', 'name' => 'site_title', 'label' => true],
            ['field' => 'addInput', 'name' => 'site_slogan', 'label' => true],
            ['field' => 'addTextarea', 'name' => 'site_description', 'label' => true],
            ['field' => 'addSelect', 'name' => 'site_language', 'options' => load_json('languages'), 'label' => true],
            ['field' => 'addMedia', 'name' => 'site_favicon', 'description' => 'Set a Primary Favicon for your Site', 'label' => true],
        ],
    ] : []);
}

if (phpadmin_enabled('settings.profile')) {
    phpadmin()->addSetting('profile', is_current_setting('profile') || $setting_index ? [
        'title' => 'My Profile Settings',
        'callback' => [(new Profile)->load(['name' => user('name'), 'username' => user('username'), 'email' => user('email'), 'language' => user()->meta('language'), 'avatar' => user()->meta('avatar')]), 'saveProfile'],
        'form_fields' => [
            ['field' => 'addMedia', 'name' => 'avatar', 'description' => 'Choose or Upload Avatar'],
            ['field' => 'addInput', 'name' => 'name'],
            ['field' => 'addInput', 'name' => 'username'],
            ['field' => 'addInput', 'type' => 'email', 'name' => 'email'],
            ['field' => 'addInput', 'type' => 'password', 'name' => 'oldPassword', 'label' => true, 'placeholder' => 'Old Password'],
            ['field' => 'addInput', 'type' => 'password', 'name' => 'password'],
            ['field' => 'addInput', 'type' => 'password', 'name' => 'confirmPassword'],
            ['field' => 'addSelect', 'name' => 'language', 'label' => 'Display Settings', 'options' => load_json('languages')],
        ]
    ] : []);
}

if (isSuperAdmin() && phpadmin_enabled('settings.analytics')) {
    phpadmin()->addSetting('analytics', is_current_setting('analytics') || $setting_index ? [
        'title' => 'Integrated Visitor Analytics Setting',
        'callback' => [(new Settings)->load(setting()->Collect()->all()), 'SyncOptions'],
        'form_fields' => [
            ['field' => 'addInput', 'type' => 'checkbox', 'label' => 'Enable/Disable Integrated Visitor Analytics: ', 'input_style' => 'flex select-none', 'class' => 'ml-2', 'name' => 'enabled_visitor_analytics'],
            ['field' => 'addInput', 'label' => true, 'name' => 'maxmind_api_key', 'placeholder' => 'Enter MaxMind License Key: '],
        ],
    ] : []);
}

// add default sidebar items
phpadmin()->addSidebarMenuItem(['title' => 'Overview', 'url' => phpadmin_prefix(), 'icon' => 'tachometer', 'order' => 1]);

$tools = array_filter([
    phpadmin_enabled('tools.cms') ? ['url' => phpadmin_prefix() . 'tools/cms/', 'rights' => ['edit'], 'title' => 'Content Manager'] : null,
    phpadmin_enabled('tools.menu') ? ['url' => phpadmin_prefix() . 'tools/menus/', 'rights' => ['edit'], 'title' => 'Menu Manager'] : null,
    !empty(phpadmin()->getSettings()) ? ['url' => phpadmin_prefix() . 'tools/settings/', 'rights' => ['super'], 'title' => 'Settings'] : null,
    phpadmin_enabled('tools.filemanager') ? ['url' => phpadmin_prefix() . 'tools/filemanager/', 'rights' => ['edit'], 'title' => 'File Manager'] : null,
    phpadmin_enabled('tools.i18n') ? ['url' => phpadmin_prefix() . 'tools/i18n/', 'rights' => ['edit'], 'title' => 'i18n'] : null,
    phpadmin_enabled('tools.sitekit') ? ['url' => phpadmin_prefix() . 'tools/sitekit/', 'rights' => ['super'], 'title' => 'SiteKit'] : null,
    phpadmin_enabled('tools.backup') ? ['url' => phpadmin_prefix() . 'tools/database-backup/', 'rights' => ['super'], 'title' => 'DB Backup'] : null,
    phpadmin_enabled('tools.reset') ? ['url' => phpadmin_prefix() . 'tools/factory-reset/', 'rights' => ['super'], 'title' => 'Factory Reset'] : null,
]);

if (!empty($tools)) {
    phpadmin()->addSidebarMenuItem([
        'title'    => 'Tools',
        'url'      => phpadmin_prefix() . 'tools/',
        'icon'     => 'wrench',
        'order'    => 4,
        'subitems' => $tools,
    ]);
}
