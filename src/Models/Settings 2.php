<?php

namespace VulcanPhp\PhpAdmin\Models;

class Settings extends Option
{
    public $site_title, $site_logo, $site_slogan, $site_description, $site_language, $site_photo, $site_favicon, $maxmind_api_key, $google_analytics_code, $enabled_visitor_analytics, $widget_head, $widget_footer, $widget_before_body, $widget_after_body, $widget_middle_body_1, $widget_middle_body_2, $widget_before_sidebar, $widget_middle_sidebar, $widget_after_sidebar;

    public function options(): array
    {
        return ['site_title', 'site_slogan', 'site_description', 'site_language', 'site_logo', 'site_photo', 'site_favicon', 'maxmind_api_key', 'google_analytics_code', 'enabled_visitor_analytics', 'widget_head', 'widget_footer', 'widget_before_body', 'widget_after_body', 'widget_middle_body_1', 'widget_middle_body_2', 'widget_before_sidebar', 'widget_middle_sidebar', 'widget_after_sidebar'];
    }

    public function optionType(): string
    {
        return 'settings';
    }

    public function labels(): array
    {
        return [];
    }
}
