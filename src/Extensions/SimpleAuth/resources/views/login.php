<?php
$this->layout('layout')
    ->block('title', 'Login');

?>

<div class="p-4 lg:px-8 lg:py-4 xl:px-10 xl:py-8 text-center text-slate-700">
    <h2 class="text-4xl font-medium mb-8"><?= translate('Sign in') ?></h2>
    <?php $model->getQForm()->showSessionMessages()->render() ?>
</div>


<div class="bg-slate-200 p-2 text-center rounded-bl-md rounded-br-md">
    <?php if (auth_enabled('forget')) : ?>
        <a href="<?= auth_url('forget') ?>"><?= translate('Forget Password') ?></a>
    <?php endif ?>

    <?php if (auth_enabled('forget') && auth_enabled('register')) : ?>
        <span><?= translate('Or') ?></span>
    <?php endif ?>

    <?php if (auth_enabled('register')) : ?>
        <a href="<?= auth_url('register') ?>"><?= translate('Register!') ?></a>
    <?php endif ?>
</div>