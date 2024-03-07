<?php

$this->layout('layout')
    ->block('title', 'Factory Reset')
?>
<div style="height:35px"></div>
<div class="flex justify-center">

    <div class="w-6/12" style="min-width: max-content;">
        <div class="py-6 px-6 shadow-xl bg-white">
            <?php if (!empty(input('erase'))) : ?>
                <div class="text-center">
                    <b class="text-2xl text-red-600"><?= translate('Erasing...') ?></b>
                    <p class="text-sm mt-1"><?= translate('This will take a few seconds.') ?></p>
                </div>
                <script>
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                </script>
            <?php else : ?>
                <p class="tw-alert warning">
                    <b>🙌 <?= translate('HANDS UP!') ?></b>
                    <span>👉 <?= translate('it will erase everything about this website.') ?></span>
                </p>
                <p class="text-gray-700 font-bold mb-4 border-b pb-2"><?= translate('Factory Reset: step') ?> <?= input('step', 1) ?>/3</p>
                <?php if (input('step') == 3) : ?>
                    <form method="post" class="text-center">
                        <?= csrf() ?>
                        <div class="mb-2">
                            <label for="password" class="text-sm text-red-600 font-semibold block mb-1"><?= translate('Enter Password To Perform Factory RESET..') ?></label>
                            <input type="password" id="password" required name="password" placeholder="<?= translate('Password?') ?>" class="tw-input">
                        </div>
                        <input type="text" id="confirm" required name="confirm" placeholder="<?= translate('Type') ?>: ERASE" class="tw-input">
                        <button type="submit" class="tw-btn font-bold mt-3 tw-btn-red"><?= translate('ERASE EVERYTHING') ?></button>
                    </form>
                <?php elseif (input('step') == 2) : ?>
                    <div class="text-center mt-8">
                        <img src="<?= phpadmin_resource('assets/images/icon/storage-backup.png') ?>" width="165" class="mx-auto mb-4">
                        <h3 class="text-sky-600"><?= translate('Storage Backup') ?></h3>
                        <p class="font-semibold text-gray-700"><?= translate('Don\'t forget to take a new storage backup before erasing everything...') ?></p>
                    </div>
                    <div class="border-t mt-6 flex justify-end" style="padding-top: 18px;">
                        <a href="?step=3" class="tw-btn tw-btn-sm mr-2 tw-btn-amber"><?= translate('Skip') ?></a>
                        <a href="<?= phpadmin_url('/tools/database-backup') ?>?action=new_storage" class="tw-btn tw-btn-sm tw-btn-indigo"><?= translate('Next') ?></a>
                    </div>
                <?php else : ?>
                    <div class="text-center mt-8">
                        <img src="<?= phpadmin_resource('assets/images/icon/database-backup.png') ?>" width="165" class="mx-auto mb-4">
                        <h3 class="text-purple-500"><?= translate('Database Backup') ?></h3>
                        <p class="font-semibold text-gray-700"><?= translate('Make sure to create a new database backup before erasing everything...') ?></p>
                    </div>
                    <div class="border-t mt-6 flex justify-end" style="padding-top: 18px;">
                        <a href="?step=2" class="tw-btn tw-btn-sm mr-2 tw-btn-amber"><?= translate('Skip') ?></a>
                        <a href="<?= phpadmin_url('/tools/database-backup') ?>?action=new" class="tw-btn tw-btn-sm tw-btn-indigo"><?= translate('Next') ?></a>
                    </div>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</div>
<div style="height:35px"></div>