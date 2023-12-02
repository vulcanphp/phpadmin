<?php

use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPageConfig;

return [
    'container'        => '#gjs',
    'noticeOnUnload'   => false,
    'avoidInlineStyle' => true,
    'allowScripts'     => true,
    'storageManager'   => [
        'type'     => 'local',
        'autoload' => false,
        'autosave' => false,
    ],
    'canvasCss'        => 'body {height: auto;}',
    // prevent scrollbar jump on pasting in CKEditor
    'assetManager'     => [
        'upload'      => url()->relativeUrl() . '?_phppage_action=asset_manager&action=upload&_token=' . csrf_token(),
        'uploadName'  => 'files',
        'multiUpload' => false,
        'assets'      => $assets,
    ],
    'deviceManager'    => [
        'devices' => [
            [
                'name'  => 'Desktop',
                'width' => '',
            ],
            [
                'name'       => 'Laptop',
                'width'      => '1020px',
                'widthMedia' => '992px',
            ],
            [
                'name'       => 'Tablet',
                'width'      => '900px',
                'widthMedia' => '768px',
            ],
            [
                'name'       => 'Mobile',
                'width'      => '360px',
                'widthMedia' => '480px',
            ],
        ],
    ],
    'styleManager'     => [
        'sectors' => [
            [
                'id'         => 'position',
                'name'       => 'Position',
                'open'       => true,
                'buildProps' => ['width', 'height', 'min-width', 'min-height', 'max-width', 'max-height', 'padding', 'margin', 'text-align'],
                'properties' => [
                    [
                        'property' => 'text-align',
                        'list'     => [
                            ['value' => 'left', 'className' => 'fa fa-align-left'],
                            ['value' => 'center', 'className' => 'fa fa-align-center'],
                            ['value' => 'right', 'className' => 'fa fa-align-right'],
                            ['value' => 'justify', 'className' => 'fa fa-align-justify'],
                        ],
                    ]
                ],
            ],
            [
                'id'         => 'background',
                'name'       => 'Background',
                'open'       => false,
                'buildProps' => ['background-color', 'background'],
            ]
        ],
    ],
    'selectorManager'  => [
        'label'         => 'CSS classes',
        'statesLabel'   => 'Layout for',
        'selectedLabel' => 'Selected',
        'states'        => [
            ['name' => 'hover', 'label' => 'Element hover'],
            ['name' => 'active', 'label' => 'Element click'],
            ['name' => 'nth-of-type(2n)', 'label' => 'Even/odd element'],
        ],
    ],
    'traitManager'     => [
        'labelPlhText' => '',
        'labelPlhHref' => 'https://website.com',
    ],
    'panels'           => [
        'defaults' => [
            [
                'id'      => 'views',
                'buttons' => [
                    [
                        'id'         => 'open-blocks-button',
                        'className'  => 'fa fa-mouse-pointer',
                        'command'    => 'open-blocks',
                        'togglable'  => 0,
                        'attributes' => ['title' => 'Blocks'],
                        'active'     => true,
                    ],
                    [
                        'id'         => 'open-settings-button',
                        'className'  => 'fa fa-cog',
                        'command'    => 'open-tm',
                        'togglable'  => 0,
                        'attributes' => ['title' => 'Settings'],
                    ],
                    [
                        'id'         => 'open-style-button',
                        'className'  => 'fa fa-paint-brush',
                        'command'    => 'open-sm',
                        'togglable'  => 0,
                        'attributes' => ['title' => 'Style Manager'],
                    ],
                ],
            ],
        ],
    ],
    'canvas'           => [
        'styles' => [
            PhpPageConfig::assets('/resources/assets/css/page-injection.css'),
        ],
    ],
    'plugins'          => ['grapesjs-touch'],
    'pluginsOpts'      => [],
];
