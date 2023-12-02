<!DOCTYPE html>
<html lang="<?= __lang() ?>" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->getBlock('title') ?> &#187; phpAdmin</title>
    <link rel="stylesheet" href="<?= auth_resource('assets/auth.css'); ?>">
</head>

<body class="bg-gray-300 flex items-center h-full justify-center">

    <main id="content" class="rounded-b-md shadow w-11/12 md:w-8/12 lg:w-6/12 xl:w-4/12 max-w-[390px] backdrop-blur border-t-8 bg-slate-50 border-sky-500">
        {{content}}
    </main>

</body>

</html>