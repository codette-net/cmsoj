<?php

namespace CMSOJ\Helpers;

class Flash
{
    public static function set(string $type, string $message)
    {
        $_SESSION['flash'][$type] = $message;
    }

    public static function get(string $type)
    {
        return $_SESSION['flash'][$type] ?? null;
    }

    public static function all()
    {
        return $_SESSION['flash'] ?? [];
    }

    public static function clear()
    {
        unset($_SESSION['flash']);
    }
}
