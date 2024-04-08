<!DOCTYPE html>
<html lang="<?= cookie('language', 'en') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="_token" content="<?= csrf_token() ?>">
    <title><?= $this->getBlock('title') ?> &#187; phpAdmin</title>
    <link rel="stylesheet" href="<?= phpadmin_resource('assets/admin.css') ?>">
    <link rel="shortcut icon" href="<?= storage_url(setting('site_favicon')) ?>" type="image/x-icon" />
    <?= mixer()
        ->npm('css', 'boxicons@2.1.4')
        ->deque('css')
    ?>
</head>

<body class="bg-gray-200">
    <?php if (session()->hasFlash('success')) : ?>
        <div tw_push_alert class="success">
            <p><?= translate(session()->getFlash('success')) ?></p>
            <span tw_push_alert-dismiss>
                <?= icon('x', ['class' => 'text-xl']) ?>
            </span>
        </div>
    <?php endif; ?>
    <?php if (session()->hasFlash('warning')) : ?>
        <div tw_push_alert class="warning">
            <p><?= translate(session()->getFlash('warning')) ?></p>
            <span tw_push_alert-dismiss>
                <?= icon('x', ['class' => 'text-xl']) ?>
            </span>
        </div>
    <?php endif; ?>
    <?php if (session()->hasFlash('error')) : ?>
        <div tw_push_alert class="error">
            <p><?= translate(session()->getFlash('error')) ?></p>
            <span tw_push_alert-dismiss>
                <?= icon('x', ['class' => 'text-xl']) ?>
            </span>
        </div>
    <?php endif ?>
    <?php $toggleSidebar = cookie('toogleSidebar') == 'true'; ?>

    <!-- Header -->
    <header id="header" class="w-full h-[50px] fixed top-0 left-0 right-0 z-30 backdrop-blur flex select-none <?= $toggleSidebar ? 'toggle' : '' ?> bg-white/25">
        <div class="backdrop-blur-sm px-3" style="<?= $toggleSidebar ? 'width:69px;' : 'width:235px;' ?>" id="header-logo">
            <a href="" class="text-slate-100 hover:text-slate-200 text-lg border-t-2 inline-flex border-sky-500 items-center h-[50px]" style="border-bottom:2px solid transparent;">
                <span class="flex items-center">
                    <i class='bx bxl-php text-sky-400 animate-pulse' style="font-size: 38px;"></i>
                    <span class="toggle-hide ml-1"><?= translate('Admin') ?></span>
                </span>
            </a>
        </div>
        <div class="flex items-center h-full px-2 md:px-4 lg:px-8" style="width: calc(100% - <?= $toggleSidebar ? '69px' : '235px' ?>)" id="header-nav">
            <div class="w-8/12 flex items-center">
                <span class="cursor-pointer text-gray-700 p-2 toggle-hide" tw-toggle-class tw-target="#sidebar, #header" tw-class="toggle" tw-callback="checkSidebarWidth">
                    <?= icon('left-arrow-alt', ['class' => 'text-2xl']) ?>
                </span>
                <span class="cursor-pointer text-gray-700 p-2 <?= $toggleSidebar == false ? 'hidden' : '' ?>" tw-toggle-class tw-target="#sidebar, #header" tw-class="toggle" tw-callback="checkSidebarWidth" id="backtoWidthSidebar">
                    <?= icon('right-arrow-alt', ['class' => 'text-2xl']) ?>
                </span>
                <nav class="ml-2 md:ml-4 lg:ml-8 px-1 md:px-2 py-1 rounded-full text-sm flex items-center" style="background: rgba(255, 255, 255, 0.35);">
                    <?php
                    $pages = explode('/', trim(url()->getPath(), "/"));
                    $prefix = '';
                    foreach ($pages as $key => $page) :
                        $prefix .= '/' . $page;
                        $page = VulcanPhp\Core\Helpers\Str::read($page)
                    ?>
                        <?php if ($key == 0) : ?>
                            <a href="<?= phpadmin_url() ?>" class="flex items-center"><?= icon('home') ?> <span class="ml-1"><?= translate('Dashboard') ?></span></a>
                        <?php else : ?>
                            <?= icon('chevron-right', ['class' => 'mx-2 opacity-75 text-gray-600']) ?>
                        <?php endif ?>

                        <?php if ($key == 0) {
                            continue;
                        } ?>

                        <?php if (($key + 1) == count($pages)) : ?>
                            <span class="text-gray-600"><?= translate($page) ?></span>
                        <?php else : ?>
                            <a href="<?= home_url($prefix) ?>"><?= translate($page) ?></a>
                        <?php endif ?>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="w-4/12">
                <div class="flex items-center w-max mr-0 ml-auto p-2">
                    <?php if (!empty(phpadmin()->getSettings()) && isSuperAdmin()) : ?>
                        <div class="ml-auto mr-6 relative">
                            <a href="<?= phpadmin_url('tools/settings') ?>" class="text-gray-700 cursor-pointer flex items-center">
                                <?= icon('cog', ['class' => 'text-2xl']) ?>
                            </a>
                        </div>
                    <?php endif ?>
                    <div class="relative">
                        <div class="cursor-pointer flex items-center" tw-toggle-class tw-is-blur tw-target="#profile-drop" tw-class="hidden">
                            <img src="<?= user()->meta('avatar') != null ? storage_url(user()->meta('avatar')) : gravatar(user('email')) ?>" class="rounded-full h-[35px] w-[35px]">
                            <span class="ml-[5px] text-gray-700"><?= icon('caret-down', ['class' => 'text-sm opacity-75']) ?></span>
                        </div>
                        <div class="w-max absolute z-20 right-0 top-[100%] mt-2 hidden" id="profile-drop">
                            <div class="rounded p-4 shadow-lg" style="background:rgb(255 255 255 / 90%)">
                                <div class="flex">
                                    <img style="background: #ddd;" src="<?= user()->meta('avatar') != null ? storage_url(user()->meta('avatar')) : gravatar(user('email'), 120) ?>" class="rounded-full h-[45px] w-[45px]">
                                    <div class="ml-2">
                                        <p class="font-semibold text-sm uppercase"><?= user()->getDisplayName() ?></p>
                                        <p class="text-sm text-gray-600">@<?= VulcanPhp\Core\Helpers\Str::read(user('role')) ?></p>
                                        <div class="flex mt-3">
                                            <?php if (phpadmin_enabled('settings.profile')) : ?>
                                                <a href="<?= settings_url('profile') ?>" class="mr-2 bg-amber-400 px-2 p-1 rounded inline-block text-white text-xs hover:text-white hover:bg-amber-500"><?= translate('Edit') ?></a>
                                            <?php endif ?>
                                            <form action="<?= auth_url('logout') ?>" method="post">
                                                <?= csrf() ?>
                                                <button type="submit" class="bg-red-400 px-2 p-1 rounded inline-block text-white text-xs hover:text-white hover:bg-red-500"><?= translate('Logout') ?></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="h-[50px]"></div>
    <!-- Sidebar -->
    <aside id="sidebar" class="bg-slate-900 text-slate-400 min-h-screen h-full fixed z-10 left-0 top-0 bottom-0 tw-preetyscroll-y select-none <?= $toggleSidebar ? 'toggle' : '' ?>" style="<?= $toggleSidebar ? 'width:69px;' : 'width:235px;' ?> padding-top: 50px;" tw-preetyscroll-except="50">
        <span class="font-semibold px-6 py-4 block uppercase text-sm toggle-hide"><?= translate('Menu') ?></span>
        <?php foreach (phpadmin()->getSidebarMenuItem() as $menu) : ?>
            <?php if (isset($menu['rights']) && hasRights($menu['rights']) === false) continue; ?>
            <?php if (isset($menu['subitems'])) : ?>
                <div tw-toggleparent>
                    <?php $currentMenu = isset($menu['url']) && url()->contains($menu['url']) ?>
                    <div class="hover:opacity-85 px-6 py-3 hover:text-slate-100 flex items-center cursor-pointer select-none <?= $currentMenu ? 'text-slate-200' : 'text-slate-300' ?>" tw-slidetoggle="subitems">
                        <?= icon(sprintf('%s.%s', $currentMenu ? 'bxs' : 'bx', $menu['icon']), ['class' => 'text-xl']) ?>
                        <span class="ml-2 flex justify-between items-center toggle-hide">
                            <span class="mr-2 inline-block"><?= translate($menu['title']) ?></span>
                            <i class='bx bx-chevron-down text-xl opacity-75'></i>
                        </span>
                    </div>
                    <div tw-childnode="subitems" class="pl-6 pb-2 toggle-hide <?= !$currentMenu ? 'hidden' : '' ?>">
                        <?php foreach ($menu['subitems'] as $submenu) : ?>
                            <?php if (isset($submenu['rights']) && hasRights($submenu['rights']) === false) continue; ?>
                            <a href="<?= home_url($submenu['url']) ?>" class="hover:opacity-85 py-[5px] hover:text-slate-100 text-sm flex <?= url()->is($submenu['url']) ? 'font-semibold text-slate-200' : 'text-slate-400' ?>">
                                <span style="width:18px;"></span>
                                <span class="flex items-center">
                                    <i class='bx bx-chevron-right text-xl opacity-75'></i>
                                    <span class="inline-block"><?= translate($submenu['title']) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <?php $currentMenu = url()->is($menu['url']) ?>
                <a href="<?= home_url($menu['url']) ?>" class="hover:opacity-85 px-6 py-3 hover:text-slate-100 flex items-center <?= $currentMenu ? 'font-semibold text-slate-200' : 'text-slate-300' ?>">
                    <?= icon(sprintf('%s.%s', $currentMenu ? 'bxs' : 'bx', $menu['icon']), ['class' => 'text-xl']) ?>
                    <span class="ml-2 inline-block toggle-hide"><?= translate($menu['title']) ?></span>
                </a>
            <?php endif ?>
        <?php endforeach ?>
    </aside>

    <!-- Main Content -->
    <main id="main" style="width: calc(100% - <?= $toggleSidebar ? '69px' : '235px' ?>);margin-left:auto;">
        <section style="height:calc(100vh - 50px); max-width: 1080px;margin:auto;" class="loader-parent">
            <div tw_admin_loader class="loading">
                <div class="loader loader-lg"></div>
            </div>
            {{content}}
        </section>
    </main>

    <?= mixer()
        ->npm('js', 'jquery', 100)
        ->enque('js', phpadmin_resource('assets/dashboard.js'))
        ->deque('js')
    ?>
</body>

</html>