<?php

$this->resourceDir(__DIR__ . '/../../resources/views')
    ->block('title', 'PhpCm: CMS')
    ->layout('layout');

?>
<div style="height: 50px;"></div>

<section id="phpcm_area">
    <?= $phpcm->render() ?>
</section>

<div style="height: 50px;"></div>
