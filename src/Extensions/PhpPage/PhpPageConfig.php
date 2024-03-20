<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage;

class PhpPageConfig
{
    const PB_LIB_ROOT_PATH  = 'vendor/vulcanphp/phpadmin/src/Extensions/PhpPage';
    const PB_STORAGE_PATH   = 'storage/phppage';
    const PB_THEME_DIR      = 'resources/phppage';

    public static function storage(string $path = '', bool $url = false): string
    {
        return sprintf('%s/%s/%s', $url ? rtrim(home_url(), '/') : root_dir(), self::PB_STORAGE_PATH, trim($path, '/'));
    }

    public static function assets(string $path = ''): string
    {
        return sprintf('%s/%s/%s', rtrim(home_url(), '/'), self::PB_LIB_ROOT_PATH, trim($path, '/'));
    }
}
