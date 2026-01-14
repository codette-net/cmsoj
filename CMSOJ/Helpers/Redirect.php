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
    
    public static function toReturnTo(?string $returnTo, string $fallback)
    {
        $url = $returnTo ?: $fallback;

        // Important: prevent open redirects (only allow same-host relative paths)
        $url = self::sanitizeInternalUrl($url, $fallback);

        header('Location: ' . $url);
        exit;
    }

    private static function sanitizeInternalUrl(string $url, string $fallback): string
    {
        $url = trim($url);

        // allow only relative URLs starting with /
        if ($url === '' || $url[0] !== '/') {
            return $fallback;
        }

        // block protocol-relative //evil.com
        if (str_starts_with($url, '//')) {
            return $fallback;
        }

        return $url;
    }
}
