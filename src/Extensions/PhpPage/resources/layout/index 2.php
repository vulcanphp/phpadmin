<!doctype html>
<html lang="<?= __lang() ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $post->getTitle(); ?></title>
    <?= mixer()
        ->enque('css', __DIR__ . '/css/layout.css')
        ->deque('css')
    ?>
</head>

<body id="phppage_body">

    <?= $body ?>

    <script>
        for (const element of document.querySelectorAll('#phppage_body [phppage_class]')) {
            element.classList.add(element.getAttribute('phppage_class'));
        }
    </script>

    <?= mixer()
        ->enque('js', __DIR__ . '/js/layout.js')
        ->deque('js')
    ?>

</body>

</html>