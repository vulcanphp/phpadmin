<!DOCTYPE html>
<html lang="<?= __lang() ?>" data-bs-theme="dark">

<head>

    <?= $this
        ->setupMeta([
            'title' => $post->title,
            'description' => $post->getValue('excerpt'),
            'image' => $post->getThumbnail() !== null ? $post->getThumbnail() : null,
            'language' => setting('site_language'),
            'sitename' => setting('site_title'),
            'url' => explode('?', url()->absoluteUrl())[0],
        ])
        ->siteMeta() ?>

    <link rel="shortcut icon" href="<?= storage_url(setting('site_favicon')) ?>" type="image/x-icon" />

    <meta name="_token" content="<?= csrf_token() ?>">

    <?= mixer()
        ->npm('css', 'bootstrap@5.3.2')
        ->enque('css', phpadmin_resource('assets/ckeditor.css'))
        ->deque('css') ?>

    <?= html_entity_decode(setting('google_analytics_code', '')) ?>
    <?= html_entity_decode(setting('widget_head', '')) ?>

</head>

<body>
    <?= html_entity_decode(setting('widget_before_body', '')) ?>

    <div class="container">

        <!-- Header Part <START> -->
        <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">

            <!-- Site Title <START> -->
            <a href="<?= home_url() ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                <span class="fs-4"><?= setting('site_title') ?></span>
            </a>
            <!-- Site Title <END> -->

            <!-- Site Menu <START> -->
            <ul class="nav nav-pills">
                <?php foreach (menu('primary') as $menu) : $submenu = isset($menu['submenu']) && !empty($menu['submenu']); ?>
                    <li class="nav-item <?= $submenu ? 'dropdown' : '' ?>">
                        <a class="<?= $submenu ? 'dropdown-toggle text-decoration-none' : '' ?> nav-link" <?= $submenu ? 'data-bs-toggle="dropdown"' : '' ?> href="<?= is_url($menu['slug']) ? $menu['slug'] : home_url($menu['slug']) ?>"><?= $menu['title'] ?></a>
                        <?php
                        if ($submenu) {
                            echo sub_menu_walker($menu['submenu'], [
                                'ul' => 'dropdown-menu',
                                'li' => '',
                                'a' => 'dropdown-item',
                            ], [
                                'url' => fn ($item) => is_url($item['slug']) ? $item['slug'] : home_url($item['slug'])
                            ]);
                        }
                        ?>
                    </li>
                <?php endforeach ?>
            </ul>
            <!-- Site Menu <END> -->

        </header>

        <!-- Header Part <END> -->

        <div class="row">
            <div class="col-12">

                <!-- Page Intro <START> -->
                <div class="px-4 my-5 text-center border-bottom">

                    <!-- Page Title & Excerpt <STARt -->
                    <h1 class="display-4 fw-bold text-body-emphasis"><?= $post->title ?></h1>
                    <div class="col-lg-6 mx-auto">
                        <p class="lead mb-4"><?= $post->getValue('excerpt') ?></p>
                    </div>
                    <!-- Page Title & Excerpt <END> -->

                    <?php if ($post->getThumbnail() !== null) : ?>
                        <!-- Page Thumbnail <START> -->
                        <div class="overflow-hidden" style="max-height: 30vh;">
                            <div class="container px-5">
                                <img src="<?= $post->getThumbnail() ?>" class="img-fluid border rounded-3 shadow-lg mb-4" alt="Image <?= $post->title ?>" width="700" height="500" loading="lazy">
                            </div>
                        </div>
                        <!-- Page Thumbnail <END> -->
                    <?php endif ?>

                </div>
                <!-- Page Intro <END> -->

            </div>

            <!-- Page Body <START> -->
            <div class="col-md-12 col-lg-10 col-xl-8 mx-auto ck-content">
                <?= html_entity_decode((string) $post->getValue('body')) ?>
            </div>
            <!-- Page Body <END> -->

        </div>

        <!-- Site Footer <START> -->

        <footer class="py-3 mt-5">

            <!-- Footer Menu <START> -->
            <ul class="nav justify-content-center border-bottom pb-3 mb-3">
                <?php foreach (parse_phpcm_menu_items((array) phpcm('footer', 'footer_menu')) as $menu) : ?>
                    <li class="nav-item"><a href="<?= is_url($menu['menu_url']) ? $menu['menu_url'] : home_url($menu['menu_url']) ?>" class="nav-link px-2 text-body-secondary"><?= $menu['menu_title'] ?></a></li>
                <?php endforeach ?>
            </ul>
            <!-- Footer Menu <END> -->

            <!-- Copyright Text <START> -->
            <div class="text-center text-body-secondary">
                <?= html_entity_decode(phpcm('footer', 'copyright_text', '&copy; 2023')) ?>
            </div>
            <!-- Copyright Text <END> -->

        </footer>
        <!-- Site Menu <END> -->

    </div>

    <?= html_entity_decode(setting('widget_after_body', '')) ?>

    <?= mixer()
        ->npm('js', 'jquery', 100)
        ->enque('js', 'https://cdn.jsdelivr.net/npm/bootstrap@latest/dist/js/bootstrap.bundle.min.js')
        ->enque('js', phpadmin_resource('assets/ckeditor.js'))
        ->deque('js') ?>

</body>

</html>