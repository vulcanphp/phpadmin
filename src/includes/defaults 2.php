<?php

use VulcanPhp\PhpAdmin\Models\Page;
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
            ['field' => 'addMedia', 'name' => 'site_logo', 'description' => 'Upload 145x45 size Logo for your site', 'label' => true],
            ['field' => 'addMedia', 'name' => 'site_photo', 'description' => 'Choose or Upload a Banner Image for your Site', 'label' => true],
            ['field' => 'addMedia', 'name' => 'site_favicon', 'description' => 'Set a Primary Favicon for your Site', 'label' => true],
        ],
    ] : []);
}

if (phpadmin_enabled('settings.profile')) {
    phpadmin()->addSetting('profile', is_current_setting('profile') || $setting_index ? [
        'title' => 'My Profile Settings',
        'callback' => [(new Profile)->load(['name' => user('name'), 'username' => user('username'), 'email' => user('email'), 'language' => user()->meta('language')]), 'saveProfile'],
        'form_fields' => [
            ['field' => 'addInput', 'name' => 'name', 'input_after' => '<a target="_blank" rel="noreferrer nofollow" href="https://en.gravatar.com/connect/" class="mt-2 text-sm block">' . translate('Click here to change profile picture.') . '</a>'],
            ['field' => 'addInput', 'name' => 'username'],
            ['field' => 'addInput', 'type' => 'email', 'name' => 'email'],
            ['field' => 'addInput', 'type' => 'password', 'name' => 'oldPassword', 'label' => true, 'placeholder' => 'Old Password'],
            ['field' => 'addInput', 'type' => 'password', 'name' => 'password'],
            ['field' => 'addInput', 'type' => 'password', 'name' => 'confirmPassword'],
            ['field' => 'addSelect', 'name' => 'language', 'label' => 'Display Settings', 'options' => load_json('languages')],
        ]
    ] : []);
}

if (isSuperAdmin()) {
    if (phpadmin_enabled('settings.analytics')) {
        phpadmin()->addSetting('analytics', is_current_setting('analytics') || $setting_index ? [
            'title' => 'Integrated & Google Analytics Settings',
            'callback' => [(new Settings)->load(setting()->Collect()->all()), 'SyncOptions'],
            'form_fields' => array_filter([
                phpadmin_enabled('analytics') ? ['field' => 'addInput', 'type' => 'checkbox', 'label' => 'Enable/Disable Integrated Visitor Analytics: ', 'input_style' => 'flex select-none', 'class' => 'ml-2', 'name' => 'enabled_visitor_analytics'] : null,
                ['field' => 'addInput', 'label' => true, 'name' => 'maxmind_api_key', 'placeholder' => 'Enter MaxMin Api Key: '],
                ['field' => 'addTextarea', 'label' => true, 'name' => 'google_analytics_code', 'placeholder' => 'Enter Google or Third Party Analytics Code here: '],
            ]),
        ] : []);
    }

    if (phpadmin_enabled('settings.widget')) {
        phpadmin()->addSetting('widget', is_current_setting('widget') || $setting_index ? [
            'title' => 'Add Html Code on Website Frontend',
            'callback' => [(new Settings)->load(setting()->Collect()->all()), 'SyncOptions'],
            'form_fields' => [
                ['field' => 'addTextarea', 'name' => 'widget_head', 'label' => 'Before Head Tag'],
                ['field' => 'addTextarea', 'name' => 'widget_before_body', 'label' => 'Before Body Tag'],
                ['field' => 'addTextarea', 'name' => 'widget_middle_body_1', 'label' => 'Middle Body Tag 1'],
                ['field' => 'addTextarea', 'name' => 'widget_middle_body_2', 'label' => 'Middle Body Tag 2'],
                ['field' => 'addTextarea', 'name' => 'widget_before_sidebar', 'label' => 'Before Sidebar'],
                ['field' => 'addTextarea', 'name' => 'widget_middle_sidebar', 'label' => 'Middle Sidebar'],
                ['field' => 'addTextarea', 'name' => 'widget_after_sidebar', 'label' => 'After Sidebar'],
                ['field' => 'addTextarea', 'name' => 'widget_after_body', 'label' => 'After Body Tag'],
                ['field' => 'addTextarea', 'name' => 'widget_footer', 'label' => 'After Footer Tag'],
            ]
        ] : []);
    }
}

if (hasRights('edit')) {

    if (phpadmin_enabled('tools.cms')) {
        // register PhpCm default options
        phpadmin()
            ->addPhpCm('homepage', [
                'icon' => 'home',
                'title' => 'Homepage',
                'selected' => true,
                'heading' => 'Homepage Settings',
                'description' => 'Manage Site Homepage Content Settings',
                'options' => [
                    ['field' => 'addInput', 'name' => 'heading'],
                    ['field' => 'addTextarea', 'height' => 100, 'name' => 'intro'],
                    ['field' => 'addMedia', 'name' => 'banner', 'description' => 'Choose & Upload Homepage Hero Banner'],
                ]
            ])
            ->addPhpCm('footer', [
                'icon' => 'bxs.dock-bottom',
                'title' => 'Footer',
                'heading' => 'Footer Settings',
                'description' => 'Manage Site Footer Content Settings',
                'options' => [
                    ['field' => 'addEditor', 'height' => 100, 'name' => 'copyright_text'],
                    ['field' => 'addPhpCmTable', 'name' => 'footer_menu', 'columns' => ['page'], 'fields' => [['type' => 'select', 'name' => 'page', 'options' => Page::list()]], 'config' => ['title' => 'Footer Menu Items']],
                ]
            ]);
    }

    if (phpadmin_enabled('tools.menu')) {
        // register default site menu
        phpadmin()
            ->registerSiteMenu([
                'id' => 'primary',
                'title' => translate('Primary Menu'),
                'selected' => true,
            ]);
    }
}

// add default sidebar items
phpadmin()->addSidebarMenuItem(['title' => 'Overview', 'url' => phpadmin_prefix(), 'icon' => 'tachometer', 'order' => 1]);

$tools = array_filter([
    phpadmin_enabled('tools.cms') ? ['url' => phpadmin_prefix() . 'tools/cms/', 'rights' => ['edit'], 'title' => 'Content Manager'] : null,
    phpadmin_enabled('tools.menu') ? ['url' => phpadmin_prefix() . 'tools/menus/', 'rights' => ['edit'], 'title' => 'Menu Manager'] : null,
    !empty(phpadmin()->getSettings()) ? ['url' => phpadmin_prefix() . 'tools/settings/', 'rights' => ['super'], 'title' => 'Settings'] : null,
    phpadmin_enabled('tools.filemanager') ? ['url' => phpadmin_prefix() . 'tools/filemanager/', 'rights' => ['edit'], 'title' => 'File Manager'] : null,
    phpadmin_enabled('tools.i18n') ? ['url' => phpadmin_prefix() . 'tools/i18n/', 'rights' => ['edit'], 'title' => 'i18n'] : null,
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
