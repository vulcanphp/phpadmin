<?php

$height = isset($height) ? intval($height) : 250;

?>
<textarea class="html_minimal_editors <?= $class ?? '' ?>" style="width: 100%;height:<?= $height + 30; ?>px; overflow:hidden;" name="<?= $name ?? '' ?>" data-height="<?= $height ?>" <?= $attributes ?? ''; ?> editor="<?= $editor ?? 'tiny' ?>">
    <?= $value ?? '' ?>
</textarea>