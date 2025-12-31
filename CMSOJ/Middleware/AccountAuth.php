<?php

namespace CMSOJ\Middleware;

// Accounts can only be viewed by admin users except for the users account themselves.
class AccountAuth
{
  public function handle()
  {
    session_start();

    // Must be logged in
    if (empty($_SESSION['admin_logged_in'])) {
      header("Location: /admin/login");
      exit;
    }

    $role = strtolower($_SESSION['admin_role'] ?? 'user');

    // Admins can access all accounts
    if ($role === 'admin') {
      return;
    }

    // check if id set  accounts/edit/{id} or accounts/{id}
    preg_match('/\/admin\/accounts\/(?:edit\/)?(\d+)/', $_SERVER['REQUEST_URI'], $m);
    $requestId = $m[1] ?? null;

    // Normal users: can access only their own account
    if ((int)$requestId === (int)($_SESSION['admin_id'] ?? 0)) {
      return;
    }

    // check if accessing profile page
    if (strpos($_SERVER['REQUEST_URI'], '/admin/profile') === 0) {
      return;
    }

    http_response_code(403);
    exit("You are not allowed to access this account.");
  }
}
