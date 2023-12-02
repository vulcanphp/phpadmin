<?php
$this->layout('layout')
    ->block('title', 'Reset Password');

?>

<div class="p-4 lg:px-8 lg:py-4 xl:px-10 xl:py-8 text-center text-slate-700">
    <h2 class="text-4xl font-medium mb-8"><?= translate('Reset Password') ?></h2>
    <?php $model->getQForm()->showSessionMessages()->render() ?>
</div>

<?php if (auth_enabled('login')) : ?>

    <div class="bg-slate-200 p-2 text-center rounded-bl-md rounded-br-md">
        <span><?= translate('Already have an account') ?></span>
        <a href="<?= auth_url('login') ?>"><?= translate('Login!') ?></a>
    </div>

<?php endif ?>