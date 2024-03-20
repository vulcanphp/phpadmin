<?php

$this->layout('layout')
    ->block('title', 'Settings: ' . $setting['title']);

?>
<div class="pt-2 w-max mx-auto flex items-center justify-center">
    <?php foreach (array_keys($settings) as $id) : ?>
        <a href="<?= settings_url($id) ?>" class="py-4 border-b-[3px] hover:border-sky-600 mx-3 px-2 font-semibold <?= $id == $active ? 'border-sky-600 text-sky-600' : 'text-gray-500' ?>"><?= translate(VulcanPhp\Core\Helpers\Str::read($id)) ?></a>
    <?php endforeach ?>
</div>

<div class="bg-white w-full lg:w-7/12 mx-auto p-6 rounded shadow-lg border">
    <h2 class="text-2xl font-semibold"><?= translate($setting['title']) ?></h2>
    <?php if (isset($setting['description'])) : ?>
        <p class="mt-2 text-gray-400"><?= translate($setting['description']) ?></p>
    <?php endif ?>

    <div class="px-4 pt-7">
        <?= $form->render() ?>
    </div>

</div>

<div style="height:35px"></div>