<?php

namespace DiplomaProject\Core\Libs;

class FileHelper
{
    public static function initDir(string $path_to_dir, string $label = '')
    {
        $label .= ': ';

        if (file_exists($path_to_dir) && !is_dir($path_to_dir)) {
            throw new \Exception("{$label}is not a directory '{$path_to_dir}'");
        }

        if (!file_exists($path_to_dir)) {
            if (!mkdir($path_to_dir, 0774, true)) {
                throw new \Exception("{$label}permission denied writing to directory '{$path_to_dir}'");
            }
        }

        if (!is_writable($path_to_dir)) {
            throw new \Exception("{$label}permission denied writing to directory '{$path_to_dir}'");
        }
    }
}
