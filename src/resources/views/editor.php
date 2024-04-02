<?php

$this->layout('layout')
    ->block('title', 'Text Editor')
?>
<div style="height:35px"></div>

<div class="flex justify-center">

    <div class="w-10/12">
        <div class="py-6 px-6 shadow-xl bg-white">
            <h2 class="mb-5 text-2xl font-semibold text-sky-600">
                <a href="<?= url('admin.pages.edit', ['id' => $post->id]) ?>">&larr; <?= $post->getTitle() ?></a>
            </h2>

            <?php
            VulcanPhp\PhpAdmin\Extensions\QForm\Manager\HtmlEditor::place([
                'value' => is_array($post->getContent()) ? '' : $post->getContent(),
                'save_btn' => '#saveCKE',
                'url' => url()->relativeUrl(),
                'show_on_saving' => '#saveCKEPreloader',
                'hide_on_saving' => '#saveCKE',
                'message_box' => '#message_box'
            ], 'html')
            ?>

            <p style="display:none; margin-top: 10px;" id="message_box" class="tw-alert"></p>

            <div class="text-center mt-6 flex items-center justify-center">
                <a href="<?= $post->getPermalink() ?>?edit=true" class="tw-btn tw-btn-indigo tw-btn-lg mr-2"><?= translate('Inline Edit') ?></a>
                <button id="saveCKE" type="submit" class="tw-btn tw-btn-sky tw-btn-lg"><?= translate('Save') ?></button>
                <button id="saveCKEPreloader" style="display: none;" disabled class="opacity-75 tw-btn tw-btn-sky tw-btn-lg tw-btn-flex ml-2 cursor-progress">
                    <i class='bx bx-loader bx-spin'></i>
                    <span><?= translate('Saving...') ?></span>
                </button>
                <a target="_blank" href="<?= $post->getPermalink() ?>" class="tw-btn tw-btn-amber tw-btn-lg ml-2"><?= translate('Preview') ?></a>
            </div>

        </div>
    </div>
</div>

<div style="height:35px"></div>