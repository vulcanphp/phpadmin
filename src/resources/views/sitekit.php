<?php

use VulcanPhp\Core\Helpers\Str;

$this->layout('layout')
    ->block('title', 'SiteKit');

?>

<div style="height:35px"></div>

<div class="flex flex-wrap justify-center">

    <div class="w-full lg:w-7/12">

        <?php if (!empty(input('edit'))) : ?>
            <div class="py-6 px-6 shadow-xl bg-white">
                <h2 class="mb-4 text-2xl font-semibold text-sky-600">
                    <a href="<?= phpadmin_url('tools/sitekit') ?>">&larr; Back to Sitekit</a>
                </h2>
                <form method="post">
                    <?= csrf() ?>
                    <input type="hidden" name="block" value="<?= input('edit') ?>">
                    <div class="tw-form-group">
                        <label for="content" class="tw-form-label">Edit <u><?= Str::read(input('edit')) ?></u> Block</label>
                        <textarea name="content" id="content" class="tw-input" rows="25" placeholder="Enter Html Content"><?= sitekit(input('edit')) ?></textarea>
                    </div>
                    <div class="tw-form-group tw-form-group-center pt-2">
                        <button class="tw-btn tw-btn-sky " type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        <?php else : ?>
            <div class="relative bg-slate-900" style="width: 100%; height: 725px;">
                <div class="absolute flex items-center" style="background: #1e293b; width: 100%; height: 55px;">
                    <a href="?edit=before_head_tag" class="mx-4 tw-btn tw-btn-sm tw-btn-sky"><?= translate('Before Head Tag') ?> &darr;</a>
                </div>
                <a href="?edit=before_content_start" style="top:75px; right: 0; left: 0; width: max-content;" class="mx-auto absolute flex justify-center tw-btn tw-btn-sm tw-btn-sky"><?= translate('Before Content Start') ?> &darr;</a>
                <div style="opacity: 0.10; position: absolute; width: 65%; top:125px; background: #9ca3af; height: 60px;" class="mx-4"></div>
                <div style="opacity: 0.10; position: absolute; width: 65%; top:200px; background: #9ca3af; height: 200px;" class="mx-4"></div>
                <div style="position: absolute; width: 65%; top:420px; height: 55px; text-align: center;" class="mx-4">
                    <a href="?edit=between_content" class="mx-auto tw-btn tw-btn-sm tw-btn-sky">&uarr; <?= translate('Between Content') ?> &darr;</a>
                </div>
                <div style="opacity: 0.10; position: absolute; width: 30%; top:470px; background: #9ca3af; height: 40px;" class="mx-4"></div>
                <div style="opacity: 0.10; position: absolute; width: 30%; top:470px; background: #9ca3af; height: 40px; left: 35%;" class="mx-4"></div>
                <div style="opacity: 0.10; position: absolute; width: 65%; top:525px; background: #9ca3af; height: 75px;" class="mx-4"></div>
                <a href="?edit=before_content_end" style="bottom: 75px; right: 0; left: 0; width: max-content;" class="mx-auto absolute flex justify-center tw-btn tw-btn-sm tw-btn-sky"><?= translate('Before Content End') ?> &uarr;</a>
                <div style="position: absolute; width: 26%; top:100px; text-align: center; height: 30px; right: 0;" class="mx-4">
                    <a href="?edit=before_sidebar" class="mx-auto tw-btn tw-btn-sm tw-btn-sky"><?= translate('Before Sidebar') ?> &darr;</a>
                </div>
                <div style="opacity: 0.10; position: absolute; width: 26%; top:140px; background: #9ca3af; height: 30px; right: 0;" class="mx-4"></div>
                <div style="opacity: 0.10; position: absolute; width: 26%; top:185px; background: #9ca3af; height: 140px; right: 0;" class="mx-4"></div>
                <div style="position: absolute; width: 26%; top:345px; text-align: center; height: 30px; right: 0;" class="mx-4">
                    <a href="?edit=between_sidebar" class="mx-auto tw-btn tw-btn-sm tw-btn-sky">&darr;<?= translate('Between Sidebar') ?> &uarr;</a>
                </div>
                <div style="opacity: 0.10; position: absolute; width: 26%; top:395px; background: #9ca3af; height: 30px; right: 0;" class="mx-4"></div>
                <div style="opacity: 0.10; position: absolute; width: 26%; top:440px; background: #9ca3af; height: 140px; right: 0;" class="mx-4"></div>
                <div style="position: absolute; width: 26%; top:600px; text-align: center; height: 30px; right: 0;" class="mx-4">
                    <a href="?edit=after_sidebar" class="mx-auto tw-btn tw-btn-sm tw-btn-sky"><?= translate('After Sidebar') ?> &uarr;</a>
                </div>
                <div class="absolute flex items-center bottom-0" style="background: #1e293b; width: 100%; height: 55px;">
                    <a href="?edit=before_footer_tag" class="mx-4 tw-btn tw-btn-sm tw-btn-sky"><?= translate('Before Footer Tag') ?> &uarr;</a>
                </div>
            </div>
        <?php endif ?>

    </div>

</div>

<div style="height:35px"></div>