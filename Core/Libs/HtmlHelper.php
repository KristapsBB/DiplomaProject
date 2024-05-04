<?php

namespace DiplomaProject\Core\Libs;

class HtmlHelper
{
    public static function getEsc(string $raw_string): string
    {
        return htmlspecialchars($raw_string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }

    public static function printEsc(string $raw_string)
    {
        echo static::getEsc($raw_string);
    }
}
