<?php

namespace VulcanPhp\PhpAdmin\Models;

class Settings extends Option
{
    public $site_title,
        $site_slogan,
        $site_description,
        $site_language,
        $site_favicon,
        $maxmind_api_key,
        $enabled_visitor_analytics;

    public function options(): array
    {
        return [
            'site_title',
            'site_slogan',
            'site_description',
            'site_language',
            'site_favicon',
            'maxmind_api_key',
            'enabled_visitor_analytics'
        ];
    }

    public function optionType(): string
    {
        return 'settings';
    }
}
