<?php

namespace DiplomaProject\Core\Libs;

class StringHelper
{
    /**
     * "admin-panel" => "AdminPanel"
     * "admin_panel" => "AdminPanel"
     */
    public static function toPascalCase(string $kebab_or_snake_case, string $separator = '_'): string
    {
        $words = explode($separator, $kebab_or_snake_case);
        $words = array_map('strtolower', $words);
        $words = array_map('ucfirst', $words);
        $pascal_case = implode('', $words);

        return $pascal_case;
    }

    /**
     * "admin-panel" => "adminPanel"
     * "admin_panel" => "adminPanel"
     */
    public static function toCamelCase(string $kebab_or_snake_case, string $separator = '_'): string
    {
        $camel_case = lcfirst(self::toPascalCase($kebab_or_snake_case, $separator));

        return $camel_case;
    }
}
