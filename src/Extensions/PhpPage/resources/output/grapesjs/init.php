<?php

use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPageConfig; ?>
<script type="text/javascript">
    window.toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "2500",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    window.languages = {
        "en": "English"
    };
    window.currentLanguage = "en";
    window.translations = {
        "loading-text": "<?= translate('Loading pagebuilder') ?>..",
        "style-no-element-selected": "<?= translate('Select an element to modify the style') ?>.",
        "trait-no-element-selected": "<?= translate('Select an element to modify any attributes') ?>.",
        "trait-settings": "<?= translate('Settings') ?>",
        "default-category": "<?= translate('General') ?>",
        "view-blocks": "<?= translate('Blocks') ?>",
        "view-settings": "<?= translate('Settings') ?>",
        "view-style-manager": "<?= translate('Style Manager') ?>",
        "save-page": "<?= translate('Save') ?>",
        "view-page": "<?= translate('View') ?>",
        "go-back": "<?= translate('Back') ?>",
        "page": "<?= translate('Page') ?>",
        "page-content": "<?= translate('Page contents') ?>",
        "toastr-changes-saved": "<?= translate('Changes saved') ?>",
        "toastr-saving-failed": "<?= translate('Error while saving changes') ?>",
        "toastr-component-update-failed": "<?= translate('Error while reloading component') ?>",
        "toastr-switching-language-failed": "<?= translate('Error while switching language') ?>",
        "yes": "<?= translate('Yes') ?>",
        "no": "<?= translate('No') ?>",
        "trait-manager": {
            "link": {
                "text": "<?= translate('Text') ?>",
                "target": "<?= translate('Open in new tab?') ?>"
            },
            "no-settings": "<?= translate('This block does not have settings') ?>."
        },
        "selector-manager": {
            "label": "<?= translate('CSS classes') ?>",
            "states-label": "<?= translate('Layout for') ?>",
            "selected-label": "<?= translate('Selected') ?>",
            "state-hover": "<?= translate('Element hover') ?>",
            "state-active": "<?= translate('Element click') ?>",
            "state-nth": "<?= translate('Even\/odd element') ?>"
        },
        "style-manager": {
            "sectors": {
                "position": "<?= translate('Position') ?>",
                "background": "<?= translate('Background') ?>",
                "advanced": "<?= translate('Advanced') ?>"
            },
            "properties": {
                "position": {
                    "width": "<?= translate('Width') ?>",
                    "min-width": "<?= translate('Minimum width') ?>",
                    "max-width": "<?= translate('Maximum width') ?>",
                    "height": "<?= translate('Height') ?>",
                    "min-height": "<?= translate('Minimum height') ?>",
                    "max-height": "<?= translate('Maximum height') ?>",
                    "padding": {
                        "name": "<?= translate('Padding') ?>",
                        "properties": {
                            "padding-top": "<?= translate('Padding top') ?>",
                            "padding-right": "<?= translate('Padding right') ?>",
                            "padding-bottom": "<?= translate('Padding bottom') ?>",
                            "padding-left": "<?= translate('Padding left') ?>"
                        }
                    },
                    "margin": {
                        "name": "<?= translate('Margin') ?>",
                        "properties": {
                            "margin-top": "<?= translate('Margin top') ?>",
                            "margin-right": "<?= translate('Margin right') ?>",
                            "margin-bottom": "<?= translate('Margin bottom') ?>",
                            "margin-left": "<?= translate('Margin left') ?>"
                        }
                    },
                    "text-align": {
                        "name": "<?= translate('Text align') ?>"
                    }
                },
                "background": {
                    "background-color": "<?= translate('Background color') ?>",
                    "background": "<?= translate('Background') ?>"
                }
            }
        },
        "asset-manager": {
            "modal-title": "<?= translate('Select Image') ?>",
            "drop-files": "<?= translate('Drop files here or click to upload') ?>",
            "add-image": "<?= translate('Add Image') ?>"
        }
    };

    window.contentContainerComponents = <?= json_encode($pageBuilder->getPageComponents()) ?>;
    window.themeBlocks = <?= json_encode($blocks) ?>;
    window.blockSettings = <?= json_encode($blockSettings) ?>;
    window.pageBlocks = <?= json_encode($pageRenderer->getPageBlocksData()) ?>;
    window.renderBlockUrl = '<?= url()->relativeUrl() ?>?_phppage_action=block_render&_token=<?= csrf_token(); ?>';
    window.injectionScriptUrl = '<?= PhpPageConfig::assets('/resources/assets/js/page-injection.js'); ?>';
    window.categoryOrder = {
        "dynamic": 1,
        "pages": 2,
        "blocks": 3,
        "layouts": 4,
        "bootstrap": 5
    };
    window.categoryIcon = {
        "dynamic": '<i class="fa fa-android" data-category="android" aria-hidden="true"></i> Dynamic',
        "pages": '<i class="fa fa-file-text-o" data-category="pages" aria-hidden="true"></i> Pages',
        "blocks": '<i class="fa fa-th-large" data-category="blocks" aria-hidden="true"></i> Blocks',
        "layouts": '<i class="fa fa-window-maximize" data-category="layouts" aria-hidden="true"></i> Layouts',
        "bootstrap": '<i class="fa fa-pencil" data-category="bootstrap" aria-hidden="true"></i> Utility'
    };

    let config = <?= json_encode(require __DIR__ . '/config.php') ?>;
    if (window.customConfig !== undefined) {
        config = $.extend(true, {}, window.customConfig, config);
    }

    window.initialComponents = <?= json_encode($pageRenderer->render()) ?>;
    window.initialStyle = <?= json_encode($pageBuilder->getPageStyleComponents()) ?>;
    window.grapesJSTranslations = {
        en: {
            styleManager: {
                empty: "<?= translate('Select an element to modify the style.') ?>"
            },
            traitManager: {
                empty: "<?= translate('Select an element to modify any attributes.') ?>",
                label: "<?= translate('Settings') ?>",
                traits: {
                    options: {
                        target: {
                            false: "<?= translate('No') ?>",
                            _blank: "<?= translate('Yes') ?>"
                        }
                    }
                }
            },
            assetManager: {
                addButton: "<?= translate('Add Image') ?>",
                inputPlh: 'http://path/to/the/image.jpg',
                modalTitle: "<?= translate('Select Image') ?>",
                uploadTitle: "<?= translate('Drop files here or click to upload') ?>"
            }
        }
    };

    window.grapesJSLoaded = false;
    window.editor = window.grapesjs.init(config);
    window.editor.on('load', function(editor) {
        window.grapesJSLoaded = true;
    });
    window.editor.I18n.addMessages(window.grapesJSTranslations);
    // load the default or earlier saved page css components
    editor.setStyle(window.initialStyle);
</script>