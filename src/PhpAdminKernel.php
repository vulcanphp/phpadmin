<?php

namespace VulcanPhp\PhpAdmin;

use Exception;
use VulcanPhp\Core\Foundation\Interfaces\IKernel;
use VulcanPhp\PhpAdmin\Controllers\PublicController;
use VulcanPhp\PhpAdmin\Controllers\VisitorController;
use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\SimpleAuth;
use VulcanPhp\Translator\Manager\TranslatorFileManager;
use VulcanPhp\Translator\Translator;

class PhpAdminKernel implements IKernel
{
    public function boot(): void
    {
        // set default config
        if (empty(config('phpadmin')))
            $this->checkConfig();

        // setup SimpleAuth Extension
        SimpleAuth::setup();

        if (
            stripos(url()->path(), config('phpadmin.prefix')) !== false
            && !in_array(true, array_map(fn ($ignore) => stripos(url()->path(), $ignore) !== false, config('phpadmin.ignore', [])))
        ) {
            // PhpAdmin Installer
            if (config('phpadmin.installer', false)) {
                $this->renderInstaller();
            }

            // PhpAdmin Setup
            if (empty(config('phpadmin.require_auth')) || (!empty(config('phpadmin.require_auth')) && auth()->isLogged())) {
                // init translator
                Translator::$instance->getDriver()
                    ->setManager(new TranslatorFileManager([
                        'convert'   => user()->meta('language', 'en'),
                        'suffix'    => 'admin',
                        'local_dir' => config('app.language_dir'),
                    ]));

                // set phpadmin as a component to application
                app()->setComponent('phpadmin', new PhpAdmin());
                // require default dashboard menu settings
                require_once __DIR__ . '/includes/defaults.php';
                // end..
            } elseif (auth()->isGuest() && !in_array(url()->path(), auth_actions())) {
                redirect(auth_url('login'));
            }
        } else {
            // put visitor information
            (new VisitorController)->newVisitor();

            // fallback to PhpPage from database
            router()->setFallback([new PublicController, 'index']);
        }
    }

    protected function renderInstaller(): void
    {
        // dispatch installer..
        echo view()
            ->getDriver()
            ->getEngine()
            ->clean()
            ->resourceDir(__DIR__ . '/resources/views/')
            ->template('installer')
            ->render();

        exit;
    }

    protected function checkConfig(): void
    {
        if (!is_writable(root_dir('/config/')) && !chmod(root_dir('/config/'), 0777))
            throw new Exception('PhpAdmin failed to setup default configuration..');

        file_put_contents(
            root_dir('/config/phpadmin.php'),
            <<<EOT
            <?php 
            return [
                'installer' => true,
                'prefix' => '/admin/',
                'ignore' => [],
                'require_auth' => ['admin', 'editor']
            ];
            EOT
        );

        sleep(2);

        config('phpadmin', null, true);
    }

    public function shutdown(): void
    {
    }
}
