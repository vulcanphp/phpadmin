<?php

$this->layout('layout')
    ->block('title', 'i18n');

$selected = 0;
?>

<div style="height:35px"></div>

<div class="bg-white lg:w-8/12 mx-auto p-6 rounded shadow-lg border">
    <?php if (isset($files) && !empty($files)) : ?>

        <div class="flex items-center justify-between border-b pb-4 mb-4">
            <div class="w-6/12">
                <form id="change_lang">
                    <select name="lang" class="tw-input tw-input-sm" onchange="document.querySelector('#change_lang').submit()">
                        <?php
                        foreach ($files as $key => $file) :
                            $selected = input('lang') == $file->name() ? $key : $selected;
                        ?>
                            <option <?= input('lang') == $file->name() ? 'selected' : '' ?> value="<?= $file->name() ?>"><?= $file->name() ?></option>
                        <?php endforeach ?>
                    </select>
                </form>
            </div>
            <div class="w-6/12 text-right">
                <button onclick="document.querySelector('#lang_form').submit()" class="tw-btn tw-btn-sm tw-btn-sky"><?= translate('Save Changes') ?></button>
            </div>
        </div>

        <form method="post" id="lang_form">
            <?= csrf() ?>
            <input type="hidden" name="lang" value="<?= $files[$selected]->name() ?>">
            <?php $json = json_decode($files[$selected]->getContent(), true);
            ksort($json); ?>
            <?php
            \VulcanPhp\PhpAdmin\Extensions\QForm\Manager\JsonEditor::place([
                'id' => 'i18n',
                'name' => 'json',
                'height' => 580,
                'value' => json_encode($json)
            ])
            ?>
        </form>

    <?php else : ?>
        <h3 class="text-gray-500 text-center">No Language File Detected..</h3>
    <?php endif ?>

</div>

<div style="height:35px"></div>