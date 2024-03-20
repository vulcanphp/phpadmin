<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer: PhpAdmin Dashboard</title>
    <link rel="stylesheet" href="<?= phpadmin_resource('assets/installer.css') ?>">
</head>

<body>

    <?php

    use App\Models\User;
    use VulcanPhp\PhpAdmin\Models\Option;
    use VulcanPhp\Core\Database\Migration;
    use VulcanPhp\SimpleDb\Database;

    sleep(1);
    config('app', null, true);
    config('phpadmin', null, true);

    if (!function_exists('js_safe_redirect')) {
        function js_safe_redirect(string $url): void
        {
            echo "<script>window.location = '" . $url . "';</script>";

            exit;
        }
    }

    if (config('phpadmin.installer', false) !== true) {
        return js_safe_redirect(auth_url('login'));
    }

    if (!is_writable(root_dir('/config/')) && !chmod(root_dir('/config/'), 0777)) {
        echo "Cannot change the mode of config file..";
        exit;
    }

    if (!is_writable(root_dir('/config/database.php')) && !chmod(root_dir('/config/database.php'), 0777)) {
        echo "Cannot change the mode of config file..";
        exit;
    }

    if (!is_writable(root_dir('/config/phpadmin.php')) && !chmod(root_dir('/config/phpadmin.php'), 0777)) {
        echo "Cannot change the mode of config file..";
        exit;
    }

    function show_config_error($config, $file, $url)
    {
    ?>
        <div style="text-align: left;">
            <h2 class="text-yellow-500" style="font-size:20px; font-weight: 600;">Failed to writing on configuration files</h2>
            <textarea style="height: 480px;margin-top: 10px;"><?= $config ?></textarea>
            <p>Update This into on file manually: <?= $file ?></p>
            <div style="text-align: center; margin-top:25px;">
                <a href="<?= $url ?>" class="btn">Reload</a>
            </div>
        </div>
    <?php
    }

    if ((request()->isMethod('post') && input('action', 'database') === 'database') || input('action') === 'sqlite') {
        $file   = root_dir('/config/database.php');
        $driver = input('action') === 'sqlite' ? 'sqlite' : 'mysql';
        $dbname = input('name');
        $sqlite_file = config('database.file', '');

        if ($driver == 'sqlite' && empty($sqlite_file)) {
            $sqlite_file = root_dir('/database/main.db');
        }

        $config = str_ireplace(
            [
                "'driver' => '" . config('database.driver', '') . "'",
                "'file' => '" . config('database.file', '') . "'",
                "'name' => '" . config('database.name', '') . "'",
                "'host' => '" . config('database.host', '') . "'",
                "'port' => '" . config('database.port', '') . "'",
                "'user' => '" . config('database.user', '') . "'",
                "'password' => '" . config('database.password', '') . "'"
            ],
            [
                "'driver' => '" . $driver . "'",
                "'file' => '" . $sqlite_file . "'",
                "'name' => '" . $dbname . "'",
                "'host' => '" . input('host') . "'",
                "'port' => '" . input('port') . "'",
                "'user' => '" . input('user') . "'",
                "'password' => '" . input('password') . "'"
            ],
            file_get_contents($file)
        );

        if (file_put_contents($file, $config)) {
            return js_safe_redirect(url()->setParam('action', 'migration')->relativeUrl());
        } else {
            return show_config_error($config, $file, url()->setParam('action', 'migration')->relativeUrl());
        }
    } elseif (request()->isMethod('post') && input('action') === 'user') {
        $user = new User;
        $user->name = 'Super Admin';
        $user->role = 'admin';
        $user->erase(['username' => input('username')]);

        if ($user->inputValidate() && $user->save()) {
            return js_safe_redirect(url()->setParam('action', 'setup')->relativeUrl());
        } else {
            return js_safe_redirect(url()->setParam('action', 'error')->setParam('message', "Failed! to create new user, " . $user->errorField() . ': ' . $user->firstError())->relativeUrl());
        }
    } elseif (request()->isMethod('post') && input('action') === 'setup') {
        Option::saveOptions(input()->all(['site_title', 'site_slogan', 'site_description', 'site_language']), 'settings');
        $file = root_dir('/config/phpadmin.php');
        $config = str_ireplace("'installer' => true", "'installer' => false", file_get_contents($file));

        if (file_put_contents($file, $config)) {
            return js_safe_redirect(auth_url('login'));
        } else {
            return show_config_error($config, $file, auth_url('login'));
        }
    }

    ?>

    <?php if (input('action', 'database') === 'database') : ?>
        <form method="post">
            <h2>MySQL Database Information: </h2>
            <label for="name">Database Name</label>
            <input type="text" id="name" name="name" required placeholder="Enter Database Name" value="<?= config('database.name', '') ?>">

            <label for="host">Database Host</label>
            <input type="text" id="host" name="host" required placeholder="Enter Database Hot Name" value="<?= config('database.host', 'localhost') ?>">

            <label for="port">Database Port</label>
            <input type="text" id="port" name="port" required placeholder="Enter Database Port" value="<?= config('database.port', '3306') ?>">

            <label for="user">Database User</label>
            <input type="text" id="user" name="user" required placeholder="Enter Username" value="<?= config('database.user', '') ?>">

            <label for="password">Database Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" value="<?= config('database.password', '') ?>">

            <div style="text-align: center;margin-top: 30px;">
                <button>Setup Database</button>
                <a class="btn btn-alt" style="margin-left: 10px;" onclick="return confirm('Are you sure to use SQlite instead of MySQL Database')" href="?action=sqlite">Use SQlite Database</a>
            </div>
        </form>
    <?php elseif (input('action') == 'error') : ?>
        <p style="background-color: #fecdd3; margin-bottom: 20px; color:#e11d48;padding:20px; border-radius: 8px;font-size: 20px; font-weight: 600;"><?= input('message') ?></p>
        <a href="<?= url()->setParam('action', 'database')->removeParams(['error', 'message']) ?>" class="btn">Start Over</a>
    <?php elseif (input('action') == 'user') : ?>
        <form method="post">
            <h2>Create Admin User: </h2>
            <label for="name">Username</label>
            <input type="text" id="name" required name="username" placeholder="Enter User Name">

            <label for="email">Email Address</label>
            <input type="email" id="email" required name="email" placeholder="Enter Email Address">

            <label for="password">User Password</label>
            <input type="password" id="password" required name="password" placeholder="Enter Password">

            <div style="text-align: center;margin-top: 30px;">
                <button>Create User</button>
            </div>
        </form>
    <?php elseif (input('action') == 'setup') : ?>
        <form method="post">
            <h2>Setup Site Information: </h2>
            <label for="site_title">Site Title</label>
            <input type="text" id="site_title" required name="site_title" placeholder="Enter Your Site Title">

            <label for="site_slogan">Site Slogan</label>
            <input type="text" id="site_slogan" required name="site_slogan" placeholder="Enter Site Slogan">

            <label for="site_description">Site Description</label>
            <textarea id="site_description" required name="site_description" placeholder="Enter site_description"></textarea>

            <label for="site_language">Site Language</label>
            <select name="site_language" id="site_language">
                <?php foreach (load_json('languages') as $code => $lang) : ?>
                    <option <?= $code == 'en' ? 'selected' : '' ?> value="<?= $code ?>"><?= $lang ?></option>
                <?php endforeach; ?>
            </select>

            <div style="text-align: center;margin-top: 30px;">
                <button>Setup</button>
            </div>
        </form>
    <?php elseif (input('action') === 'migration') : ?>
        <?php
        try {
            $database = new Database(config('database'));
        } catch (Throwable $e) {
            return js_safe_redirect(url()->setParam('action', 'error')->setParam('message', $e->getMessage())->relativeUrl());
        }

        // apply migrations
        foreach ([root_dir('/database/migrations/'), __DIR__ . '/../../migrations/'] as $path) {
            $migration =  new Migration($database->getPdo(), $path);
            $migration->applyMigrations();
        }
        ?>
        <p class="text-yellow-500" style="font-size:20px; font-weight: 600;margin-top: 10px;">Redirecting...</p>
        <script>
            setTimeout(function() {
                window.location = '<?= url()->setParam('action', 'user')->relativeUrl() ?>';
            }, 2500);
        </script>
    <?php else : ?>
        <p style="background-color: #fecdd3; margin-bottom: 20px; color:#e11d48;padding:20px; border-radius: 8px;font-size: 20px; font-weight: 600;">Undefined Action, Please Start Over :(</p>
        <a href="<?= url()->setParam('action', 'database')->removeParams(['error', 'message', 'action']) ?>" class="btn">Start Over</a>
    <?php endif ?>

</body>

</html>