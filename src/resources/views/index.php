<?php

$this->layout('layout')
    ->block('title', 'Overview');

?>
<div style="height:35px"></div>

<style>
    @media (min-width: 980px) {
        .md\:w-3\/12 {
            width: 25%;
        }

        .md\:w-4\/12 {
            width: 33.33%;
        }

        .md\:w-8\/12 {
            width: 66.66%;
        }

        .md\:mr-6 {
            margin-right: 1.5rem;
        }

        .md\:mb-0 {
            margin-bottom: 0px;
        }
    }
</style>

<?php
if (phpadmin()->has('before_dashboard')) {
    echo call_user_func(phpadmin()->get('before_dashboard'));
}
?>

<div class="flex flex-wrap mx-[-0.75rem]">
    <?php
    if (phpadmin()->has('before_widget')) {
        echo call_user_func(phpadmin()->get('before_widget'));
    }
    ?>
    <?php foreach (phpadmin()->getWidgets() as $widget) : ?>
        <div class="md:w-3/12 w-full">
            <div class="flex mx-3 mb-6 bg-white shadow rounded items-center px-3 py-2 lg:px-6 lg:py-3">
                <div class="w-8/12">
                    <?php if (isset($widget['text'])) : ?>
                        <span class="text-slate-500 text-sm font-semibold"><?= translate($widget['text']) ?></span>
                    <?php endif ?>
                    <h2 class="text-sky-700 font-bold"><?= number_format($widget['count']) ?></h2>
                </div>
                <div class="w-4/12 text-right">
                    <?= icon($widget['icon'] ?? 'stats-chart', ['class' => 'ml-auto text-sky-600 bg-sky-100 p-1 rounded shadow-sm shadow-sky-400', 'style' => 'font-size: 40px;']) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php
    if (phpadmin()->has('after_widget')) {
        echo call_user_func(phpadmin()->get('after_widget'));
    }
    ?>
</div>

<?php
if (phpadmin()->has('before_analytics')) {
    echo call_user_func(phpadmin()->get('before_analytics'));
}
?>

<?php if (isSuperAdmin() && setting('enabled_visitor_analytics') === 'true') : ?>
    <div class="mb-6 flex flex-wrap">
        <div class="md:w-8/12 w-full">
            <div id="monthly-chart" class="md:mr-6 mb-6 md:mb-0 shadow rounded bg-white"></div>
        </div>
        <div class="md:w-4/12 w-full">
            <div id="referer-pie" class="shadow rounded bg-white p-4"></div>
        </div>
    </div>
<?php endif; ?>

<?php
if (phpadmin()->has('after_analytics')) {
    echo call_user_func(phpadmin()->get('after_analytics'));
}
?>

<div class="flex flex-wrap">
    <?php if (phpadmin_enabled('quicklinks')) : ?>
        <div class="md:w-8/12 w-full">
            <div class="shadow md:mr-6 mb-6 md:mb-0 rounded bg-white p-5 lg:p-8">
                <h3 class="mb-1"><?= translate('Welcome') ?>, <?= user()->getDisplayName() ?></h3>
                <p><?= translate('We have assembled some links to get you started quickly:') ?></p>
                <div class="flex flex-wrap mt-6">
                    <?php if (phpadmin_enabled('tools.cms')) : ?>
                        <div class="w-6/12">
                            <a href="<?= phpadmin_url('tools/cms') ?>" class="tw-btn tw-btn-sky tw-btn-lg"><?= translate('Customise Site') ?></a>
                        </div>
                    <?php endif ?>
                    <div class="w-6/12">
                        <?php if (phpadmin_enabled('tools.menu')) : ?>
                            <a href="<?= phpadmin_url('tools/menus') ?>" class="flex mb-2 text-sky-600 hover:underline hover:text-sky-700 items-center">
                                <?= icon('grid-alt', ['class' => 'text-lg']) ?>
                                <span class="ml-2"><?= translate('Customise Site Menu') ?></span>
                            </a>
                        <?php endif ?>
                        <?php if (phpadmin_enabled('pages')) : ?>
                            <a href="<?= url('admin.pages.create') ?>" class="flex mb-2 text-sky-600 hover:underline hover:text-sky-700 items-center">
                                <?= icon('bxs.file-plus', ['class' => 'text-lg']) ?>
                                <span class="ml-2"><?= translate('Create A New Page') ?></span>
                            </a>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
    <?php if (isSuperAdmin()) : ?>
        <div class="md:w-4/12 w-full">
            <?php if (setting('enabled_visitor_analytics') === 'true') : ?>
                <div class="rounded shadow bg-white p-4 lg:p-6">
                    <h3 class="mb-1"><?= translate('Visitors from Countries: ') ?></h3>
                    <div id="countries"></div>
                </div>
            <?php else : ?>
                <div class="rounded shadow bg-white opacity-80 hover:opacity-100 text-center p-4 lg:p-6">
                    <p class="text-lg text-gray-400 mb-4"><?= translate('Integrated Visitor Analytics is Disabled from Settings') ?>.</p>
                    <a href="<?= phpadmin_url('/tools/settings/analytics/') ?>"><?= translate('Click Here & Change it') ?></a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
if (phpadmin()->has('after_dashboard')) {
    echo call_user_func(phpadmin()->get('after_dashboard'));
}
?>

<div style="height:35px"></div>