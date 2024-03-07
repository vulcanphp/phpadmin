<?php

use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPageConfig;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9, user-scalable=no">
    <title>
        <?= $post->getTitle() ?> &#187; PhpPage - PHP Drag & Drop Page Builder
    </title>

    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/337/337947.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/grapesjs@0.15.9/dist/css/grapes.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?= PhpPageConfig::assets('resources/assets/css/phppage.css'); ?>">
    <?= $pageBuilder->customStyle(); ?>

    <script src="https://cdn.jsdelivr.net/npm/grapesjs@0.15.9/dist/grapes.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.10.2/beautify-html.min.js"></script>
    <script src="<?= PhpPageConfig::assets('/resources/assets/js/grapesjs-touch-v0.1.1.min.js'); ?>"></script>

    <?= $pageBuilder->customScripts('head'); ?>
</head>

<body>

    <div id="phpb-loading">
        <div class="circle">
            <div class="spinner-border"><span class="sr-only">
                    <?= translate('Loading') ?>...
                </span></div>
            <div class="text">
                <?= translate('PhpPage Loading') ?>..
            </div>
        </div>
    </div>

    <div id="sidebar-bottom-buttons" class="row justify-content-between">
        <div class="col-md-4 text-left">
            <button id="toggle-sidebar" class="btn mr-3"><i class="fa fa-bars"></i></button>
            <button id="editor-undo" class="btn"><i class="fa fa-undo"></i></button>
            <button id="editor-redo" class="btn"><i class="fa fa-repeat"></i></button>
        </div>
        <div class="col-md-4 text-center" id="device-swtich">
            <button data-device="Desktop" class="btn active"><i class="fa fa-desktop"></i></button>
            <button data-device="Laptop" class="btn"><i class="fa fa-laptop"></i></button>
            <button data-device="Tablet" class="btn"><i class="fa fa-tablet"></i></button>
            <button data-device="Mobile" class="btn"><i class="fa fa-mobile"></i></button>
        </div>
        <div class="col-md-4 text-right">
            <a id="view-page" href="<?= home_url($post->getSlug()); ?>" target="_blank" class="btn"><?= translate('Preview') ?></a>
            <button id="save-page" class="btn" data-url="<?= url()->relativeUrl() ?>?_token=<?= csrf_token(); ?>">
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <i class="fa fa-save"></i>
                <?= translate('Save') ?>
            </button>
        </div>
    </div>

    <div id="block-search">
        <i class="fa fa-search"></i>
        <input type="text" class="form-control" placeholder="Filter">
    </div>

    <div id="gjs"></div>

    <?php
    require __DIR__ . '/grapesjs/init.php';
    require __DIR__ . '/grapesjs/asset-manager.php';
    require __DIR__ . '/grapesjs/component-type-manager.php';
    require __DIR__ . '/grapesjs/style-manager.php';
    require __DIR__ . '/grapesjs/trait-manager.php';
    ?>

    <script src="<?= PhpPageConfig::assets('/resources/assets/js/phppage.js'); ?>"></script>
    <script>
        $('#editor-undo').on('click', function() {
            window.editor.UndoManager.undo();
        });
        $('#editor-redo').on('click', function() {
            window.editor.UndoManager.redo();
        });
        $('#device-swtich button').on('click', function() {
            $('#device-swtich button.active').removeClass('active');
            $(this).addClass('active');
            window.editor.setDevice($(this).data('device'));
        });
    </script>
    <?= $pageBuilder->customScripts('body'); ?>
</body>

</html>