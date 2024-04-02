<?php

use VulcanPhp\FileSystem\File;

$multiple = isset($multiple) && $multiple === true;
$value    = isset($value) && !empty($value) ? (array) decode_string($value) : [];

if (!function_exists('tw_media_output')) {
    function tw_media_output(string $resource): string
    {
        $file = File::choose(!is_url($resource) ? storage_dir($resource) : $resource);
        switch ($file->ext()) {
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'gif':
            case 'webp':
            case 'svg':
                $avatar = '<img src="' . $file->url() . '"/>';
                break;
            case 'folder':
                $avatar = '<i class="bx bxs-folder-open" style="font-size: 48px;"></i>';
                break;
            case 'text':
                $avatar = '<i class="bx bxs-file-txt" style="font-size: 48px;"></i>';
                break;
            default:
                $avatar = '<i class="bx bxs-file" style="font-size: 48px;"></i>';
                break;
        }

        return sprintf('<div>%s<small>%s</small></div>', $avatar, $file->name());
    }
}

?>

<div class="tw_media_select <?= $multiple ? 'tw_media_select-multiple' : '' ?>">

    <div class="tw_media_select-choose">

        <select name="<?= $name ?><?= $multiple ? '[]' : '' ?>" class="<?= $class ?? '' ?>" id="<?= $id ?? uniqid('media_select_') ?>" <?= $attributes ?? '' ?> style="display: none;" <?= $multiple ? 'multiple' : '' ?>>
            <?php foreach ($value as $option) : ?>
                <option value="<?= $option; ?>" selected></option>
            <?php endforeach ?>
        </select>

        <label tw_media_select-label style="width:100%">

            <span class="label-icon">
                <?= icon('file-find', ['style' => 'font-size: 30px']); ?>
            </span>

            <span class="label-description">
                <span style="display: block;">
                    <?= translate('Choose' . ($multiple ? ' Multiple' : '') . ' File') ?>
                </span>
                <small>
                    <?= translate($description ?? '') ?>
                </small>
            </span>

        </label>

    </div>

    <div class="tw_media_select-attachments" style="<?= empty($value) ? 'display: none;' : '' ?>">
        <?php foreach ($value as $resource) : ?>
            <?= tw_media_output($resource) ?>
        <?php endforeach ?>
    </div>

</div>