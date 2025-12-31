<?php

namespace CMSOJ\Helpers;

class Redirect
{
    public static function back()
    {
        $_SESSION['__redirect_back'] = $_SERVER['HTTP_REFERER'] ?? '/admin';
        return new self;
    }

    public function withErrors($validator): self
    {
        $_SESSION['errors'] = $validator->errors();
        return $this;
    }

    public function withOld(): self
    {
        $_SESSION['old'] = $_POST;
        return $this;
    }

    public function send()
    {
        header("Location: " . ($_SESSION['__redirect_back'] ?? '/admin'));
        exit;
    }
}
