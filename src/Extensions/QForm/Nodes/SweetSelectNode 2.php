<?php

$placeholder = translate(isset($placeholder) ? $placeholder : 'Select Option');
$is_ajax     = isset($ajax) && !empty($ajax);
$multiple    = isset($multiple) && $multiple === true;
$taggable    = isset($taggable) && $taggable === true;
$value       = isset($value) && (!empty($value) || $value === 0) ? (array) $value : [];
$options     = isset($options) ? $options : [];

if ($is_ajax && empty($options) && !empty($value)) {
    $options = $value;
    $value   = array_keys($value);
}

if ($taggable && $multiple) {
    $value   = array_combine(array_values($value), array_values($value));
    $options = $value;
}


?>
<div class="tw_select <?= $multiple ? 'tw_select-multiple' : '' ?> <?= $taggable ? 'tw_select-taggable' : '' ?> <?= $is_ajax ? 'tw_select-ajax' : '' ?>" <?php if ($is_ajax) : ?> data-ajax="<?= $ajax ?>" data-ajaxmethod="<?= $method ?? 'get' ?>" data-ajaxvalue="<?= join(',', $value) ?>" <?php endif; ?>>

    <select style="display: none;" <?= $attributes ?? '' ?> name="<?= $name ?? '' ?><?= $multiple ? '[]' : '' ?>" data-value="<?= join(',', $value) ?>" class="<?= $class ?? '' ?>" <?php if (isset($id)) :  ?> id="<?= $id ?>" <?php endif; ?> <?= $multiple ? 'multiple' : '' ?>>
        <option></option>
        <?php if (isset($options) && !empty($options)) : ?>
            <?php foreach (array_keys($options) as $key) : ?>
                <option <?= in_array($key, $value) ? 'selected' : '' ?> value="<?= $key ?>"></option>
            <?php endforeach ?>
        <?php endif ?>

    </select>

    <span class="tw_select-placeholder" data-placeholder="<?= $placeholder ?>">
        <?php if ($multiple && !empty($value)) : ?>
            <?php foreach ($value as $key) : ?>
                <span class="tw_multiple-selected tw_popup_ignore_blur" data-value="<?= $key ?>">
                    <span class="tw_select-placeholder-icon">
                        <i class='bx bx-check text-2xl text-sky-600'></i>
                    </span>
                    <span class="tw_multiple-selected-text"><?= $options[$key] ?></span>
                </span>
            <?php endforeach ?>
        <?php elseif (!$multiple && !empty($value)) : ?>
            <span class="tw_select-placeholder-icon">
                <i class='bx bx-check text-2xl text-sky-600'></i>
            </span>
            <?= $options[$value[0]] ?? 'Undefined Value :)' ?>
        <?php else : ?>
            <span class="text-slate-400"><?= $placeholder ?></span>
        <?php endif ?>
    </span>

    <?php if (!$multiple) : ?>
        <span class="tw_select-placeholder-navigator">
            <i class='bx bx-chevron-down opacity-75'></i>
        </span>
    <?php endif ?>

    <div class="tw_select-options">

        <div class="tw_select-item-search">
            <div class="tw_select-new-tag">
                <input type="text" placeholder="<?= translate($taggable ? 'Type new & search...' : 'Search...') ?>">
            </div>
        </div>

        <?php if (isset($options) && !empty($options)) : ?>
            <?php foreach ($options as $key => $option) : ?>
                <div class="tw_select-item <?= in_array($key, $value) ? 'active' : '' ?>" data-value="<?= $key ?>">
                    <span class="tw_select-item-icon">
                        <?= in_array($key, $value) ? '<i class="bx bx-check text-2xl text-sky-600"></i>' : '' ?>
                    </span>
                    <span class="tw_select-item-text"><?= $option ?></span>
                </div>
            <?php endforeach ?>
        <?php endif ?>

    </div>

</div>