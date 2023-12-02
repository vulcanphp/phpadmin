<?php

namespace VulcanPhp\PhpAdmin\Models;

use VulcanPhp\SimpleDb\Model;

class Visitor extends Model
{
    public static function tableName(): string
    {
        return 'visitors';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function fillable(): array
    {
        return ['ip', 'country', 'os', 'device', 'browser', 'page', 'referer'];
    }

    public static function check()
    {
        if (intval(cache()->retrieve('last_visitor_checked')) < strtotime('-1 day')) {
            if (is_sqlite()) {
                parent::erase("date <= date('now','-7 day')");
            } else {
                parent::erase('date < NOW() - INTERVAL 15 DAY');
            }

            cache()->store('last_visitor_checked', time());
        }
    }
}
