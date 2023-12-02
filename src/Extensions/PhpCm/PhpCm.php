<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpCm;

class PhpCm
{
    public static function Menu(...$args): PhpCmMenu
    {
        return new PhpCmMenu(...$args);
    }

    public static function Options(...$args): PhpCmOptions
    {
        return new PhpCmOptions(...$args);
    }

    public static function Table(...$args): PhpCmTable
    {
        return new PhpCmTable(...$args);
    }
}
