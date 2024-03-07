<?php

$this->layout('layout')
    ->block('title', 'Backup Manager')
?>

<div style="height:35px"></div>

<div class="flex flex-wrap justify-center">

    <div class="w-full lg:w-8/12">

        <p class="tw-alert warning">
            <b>🙌 <?= translate('HANDS UP!') ?></b>
            <span>👉 <?= translate('it may erase everything about this website if any mistake.') ?></span>
        </p>

        <div class="border px-4 py-3 rounded shadow mt-6 bg-white" style="min-width: max-content;">
            <div class="border-b pb-2 mb-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold"><?= translate('Database Backup Manager') ?></h3>
                <form method="post" id="upload_backup" enctype="multipart/form-data">
                    <?= csrf() ?>
                    <input type="hidden" name="action" value="upload">
                    <input type="file" style="display:none" onchange="document.querySelector('#upload_backup').submit()" id="upload" name="upload">
                    <label for="upload" class="cursor-pointer flex items-center hover:underline text-teal-600 hover:text-teal-700 font-semibold">
                        <?= icon('cloud-upload', ['class' => 'text-xl']) ?>
                        <span class="ml-1"><?= translate('Upload') ?></span>
                    </label>
                </form>
            </div>
            <table class="border w-full">
                <thead>
                    <tr>
                        <th class="border bg-slate-50 text-left py-1 px-3"><?= translate('Backup') ?></th>
                        <th class="border bg-slate-50 text-left py-1 px-3"><?= translate('Action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup) : $is_storage = stripos($backup->name(), '.storage.zip') !== false; ?>
                        <tr>
                            <td class="border text-left py-1 px-3"><?= $backup->name() ?></td>
                            <td class="border text-left py-1 px-3">
                                <a href="?action=download&backup=<?= $backup->name() ?>" class="text-sm hover:underline"><?= translate('Download') ?></a>
                                <a href="?action=<?= $is_storage ? 'upload_storage' : 'rollback' ?>&backup=<?= $backup->name() ?>" onclick="return confirm('<?= translate('Are You Sure To Rollback With This Backup?') ?>')" class="ml-2 pl-2 border-l text-sm hover:underline text-amber-500 hover:text-amber-600"><?= translate($is_storage ? 'Upload' : 'Rollback') ?></a>
                                <a href="?action=delete&backup=<?= $backup->name() ?>" onclick="return confirm('<?= translate('Are You Sure To Delete This Backup?') ?>')" class="ml-2 pl-2 border-l text-sm hover:underline text-rose-500 hover:text-rose-600"><?= translate('Delete') ?></a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    <tr>
                        <td class="border text-center py-2 px-2" colspan="2">
                            <?php if (empty($backups)) : ?>
                                <span class="block text-gray-500 opacity-75 mb-1"><?= translate('No Backup has been created') ?></span>
                            <?php endif; ?>
                            <a href="?action=new" class="text-sm hover:underline"><?= translate('+ New Database Backup') ?></a>
                            <span class="mx-2 text-slate-400">|</span>
                            <a href="?action=new_storage" class="text-sm hover:underline"><?= translate('+ New Storage Backup') ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</div>

<div style="height:35px"></div>