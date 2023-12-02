<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use Exception;
use Throwable;
use ZipArchive;
use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;
use VulcanPhp\PhpAdmin\Extensions\PhpCm\PhpCm;
use RecursiveIteratorIterator;
use VulcanPhp\FileSystem\File;
use RecursiveDirectoryIterator;
use VulcanPhp\Core\Helpers\Str;
use VulcanPhp\Core\Helpers\Cookie;
use VulcanPhp\Core\Helpers\Session;
use VulcanPhp\PhpAdmin\Models\Option;
use VulcanPhp\Core\Database\DBBackup;
use VulcanPhp\Core\Foundation\Controller;

class ToolController extends Controller
{
    public function setting(?string $active = null)
    {
        $settings =  phpadmin()->getSettings();
        $active   = $active ?? 'general';
        $setting  = $settings[$active] ?? null;

        if ($setting === null) {
            return response()->back();
        }

        $model = is_array($setting['callback']) ? $setting['callback'][0] : $setting['callback'];

        if ($active !== null && request()->isMethod('post')) {
            $model->load(input()->all());

            if (call_user_func([$model, (is_array($setting['callback']) ? $setting['callback'][1] : 'save')])) {
                if (isset($setting['on_update'])) {
                    call_user_func($setting['on_update']);
                }

                session()->setFlash('success', Str::read($active) . ' Settings Has Been Saved.');
                return response()->back();
            } else {
                session()->setFlash('error', Str::read($active) . ' Settings Failed To Save.');
            }
        }

        $form = QForm::begin($model);
        foreach ((array)$setting['form_fields'] as $field)
            $form->{$field['field']}($field);

        $form->formAttr(['method' => 'post', 'action' => ''])->submit(['name' => 'Save Changes', 'center' => true]);

        return phpadmin_view('settings', ['settings' => $settings, 'active' => $active, 'setting' => $setting, 'form' => $form]);
    }

    public function cms()
    {
        return PhpCm::Options(
            [
                'title' => translate('Php Content Manager (PhpCM)'),
                'version' => '1.x'
            ],
            phpadmin()->get('php_cm')
        )->resolve();
    }

    public function menus()
    {
        return PhpCm::Menu(phpadmin()->get('site_menus'))
            ->resolve();
    }

    public function i18n()
    {
        storage()->enter('local');
        storage()->check();

        if (request()->isMethod('post')) {
            if (storage()->file(input('lang'))->putContent(input('json'))) {
                session()->setFlash('success', 'Language file has been saved');
            } else {
                session()->setFlash('warning', 'failed to save language file');
            }
            return response()->back();
        }

        return phpadmin_view('i18n', ['files' => storage()->scan()]);
    }

    public function DBBackup()
    {
        $this->checkAuth(['admin']);

        storage()->setPath(root_dir('/database/backups'));
        storage()->check();

        if (request()->isMethod('post') && input('action') === 'upload') {
            if (!empty(storage()->upload('upload', 'keep'))) {
                session()->setFlash('success', 'New Backup has been uploaded');
            } else {
                session()->setFlash('warning', 'Failed to upload backup file');
            }
            return response()->back();
        }

        if (input('action') === 'new') {
            if (is_sqlite()) {
                $db = File::choose(config('database.file'));
                $status = $db->copy(storage()->file('backup-' . date('d-M-Y-H-i-s') . '.sqlite')->path());
            } else {
                $manager = new DBBackup;
                $status = storage()->file('backup-' . date('d-M-Y-H-i-s') . '.sql')->putContent($manager->dump('*', [], []));
            }

            if ($status) {
                session()->setFlash('success', 'New Backup has been created.');
            } else {
                session()->setFlash('warning', 'Failed to create new backup.');
            }
            if (stripos(request()->referer(), '/factory-reset') !== false) {
                return response()->redirect(phpadmin_url('tools/factory-reset') . '?step=2');
            }
            return response()->back();
        } elseif (input('action') === 'new_storage') {
            if (!class_exists('ZipArchive')) {
                throw new Exception('Zip: needs to be enabled');
            }

            $zip = new ZipArchive;
            $rootPath = realpath('storage');
            $zipname = sprintf('%s/backup-%s.storage.zip', storage()->path(), date('d-M-Y-H-i-s'));

            if ($zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Failed to open Zip file');
            }

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            if (stripos(request()->referer(), '/factory-reset') !== false) {
                return response()->redirect(phpadmin_url('tools/factory-reset') . '?step=3');
            }

            return response()->back();
        } elseif (input('action') === 'upload_storage') {

            $file = storage()->file(input('backup'));
            $zip = new ZipArchive;

            if ($zip->open($file->path()) !== true) {
                throw new Exception('Failed to open Zip file');
            }

            $zip->extractTo(config('app.storage_dir'));
            $zip->close();

            return response()->back();
        } elseif (input('action') === 'rollback') {
            try {
                $file = storage()->file(input('backup'));
                if (is_sqlite() && $file->ext() === 'sqlite') {
                    $file->copy(config('database.file'));
                } elseif (!is_sqlite() && $file->ext() === 'sql') {
                    $manager = new DBBackup;
                    $manager->import($file->getContent());
                } else {
                    session()->setFlash('warning', 'Invalid Database Rollback File Extension');
                    return response()->back();
                }

                cache_engine()->flush();
                Cookie::removeAll();
                Session::flush();
                session()->setFlash('success', 'Database has been rollback with Backup:' . input('backup'));
            } catch (Throwable $e) {
                session()->setFlash('warning', 'Failed to rollback, ' . $e->getMessage());
            }

            return response()->back();
        } elseif (input('action') === 'delete') {
            if (storage()->file(input('backup'))->remove()) {
                session()->setFlash('success', 'New Backup has been deleted.');
            } else {
                session()->setFlash('warning', 'Failed to delete new backup.');
            }

            return response()->back();
        } elseif (input('action') === 'download') {
            storage()->download(input('backup'));
        }

        return phpadmin_view('backup', ['backups' => storage()->scan()]);
    }

    public function factoryReset()
    {
        $this->checkAuth(['admin']);

        if (input('step') == 3 && request()->isMethod('post')) {
            if (input('confirm') === 'ERASE' && password(input('password'), user('password'))) {
                Option::saveOptions(['token' => bin2hex(random_bytes(32)), 'step' => 1], 'reset');
                return response()->redirect(phpadmin_url('tools/factory-reset') . '?erase=' . Option::getOption('reset', 'token'));
            } else {
                session()->setFlash('warning', 'Wrong Password/Confirmation');
                return response()->back();
            }
        } elseif (!empty(input('erase'))) {
            $config = Option::getOptions('reset');

            if ($config->get('token') == input('erase')) {
                if (!is_writable(root_dir('/config/')) && !chmod(root_dir('/config/'), 0777)) {
                    echo "Cannot change the mode of config/app.php file..";
                    exit;
                } elseif (!is_writable(root_dir('/config/phpadmin.php')) && !chmod(root_dir('/config/phpadmin.php'), 0777)) {
                    echo "Cannot change the mode of config/app.php file..";
                    exit;
                }

                if ($config->get('step') == 1) {
                    // erase storage folder
                    storage()->remove();
                    $config->set('step', 2);
                    Option::saveOptions($config, 'reset');
                } elseif ($config->get('step') == 2) {
                    // erase database, caches and enable installer
                    if (is_sqlite()) {
                        database()->exec('PRAGMA foreign_keys = OFF;');
                        $query = database()->prepare("SELECT name FROM sqlite_master WHERE type='table'");
                        $query->execute();

                        foreach ($query->fetchAll(\PDO::FETCH_COLUMN) as $table) {
                            database()->exec('DROP TABLE IF EXISTS ' . $table);
                        }

                        database()->exec('PRAGMA foreign_keys = ON;');
                    } else {
                        database()->exec('SET foreign_key_checks = 0;');
                        $result = database()->prepare("SHOW TABLES");
                        $result->execute();

                        foreach ($result->fetchAll(\PDO::FETCH_COLUMN) as $table) {
                            database()->exec('DROP TABLE IF EXISTS ' . $table);
                        }

                        database()->exec('SET foreign_key_checks = 1;');
                    }

                    // erase caches and sessions
                    cache_engine()->flush();
                    Cookie::removeAll();
                    Session::flush();

                    // enable installer
                    $file = root_dir('/config/phpadmin.php');
                    file_put_contents($file, str_ireplace("'installer' => false", "'installer' => true", file_get_contents($file)));

                    // redirecting to homepage via js with success message
                    return '<p style="color:green">All Data Has Been Erased Successfully.</p><p style="font-weight:bold;">Redirecting...</p><script>setTimeout(function() {window.location = "' . phpadmin_url('/') . '";}, 5000);</script>';
                }
            } else {
                session()->setFlash('warning', 'Failed to erase data.');
                return response()->back();
            }
        }

        return phpadmin_view('reset');
    }
}
