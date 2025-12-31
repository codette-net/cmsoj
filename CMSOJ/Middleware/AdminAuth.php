<?php

namespace CMSOJ\Middleware;

class AdminAuth
{
    public function handle()
    {
        session_start();

        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: /admin/login");
            exit;
        }
    }
}
