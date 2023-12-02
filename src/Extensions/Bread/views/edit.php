<?php

$this->resourceDir($layout['dir'])
    ->layout($layout['name'])
    ->block('title', 'Create: ' . $title);

?>

<div style="height:35px"></div>

<div class="flex justify-center">
    <div class="<?= ['sm' => 'w-full lg:w-5/12', 'md' => 'w-full lg:w-7/12', 'lg' => 'w-full lg:w-10/12', 'xl' => 'w-full lg:w-10/12', 'full' => 'w-full'][$config['width'] ?? 'sm'] ?>">
        <div class="py-4 px-6 shadow-xl bg-white">
            <h2 class="mb-4 text-2xl text-center font-semibold text-sky-600"><?= translate($config['create_title'] ?? 'Update Record') ?></h2>
            <?php $form->showSessionMessages()->render() ?>
        </div>
    </div>
</div>

<div style="height:35px"></div>