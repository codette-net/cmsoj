<?php

namespace CMSOJ\Controllers\Admin;

use CMSOJ\Template;
use CMSOJ\Models\Account;
use CMSOJ\Helpers\Permissions;
use CMSOJ\Helpers\Flash;

class AuthController
{
  public function show()
  {
    return Template::view('CMSOJ/Views/admin/login.html', ['title' => 'Login', 'body_class' => 'login']);
  }

  public function submit()
  {
    session_start();

    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';

    $email    = trim($_POST['admin_email'] ?? '');
    $password = trim($_POST['admin_password'] ?? '');

    // Validation
    if ($email === '' || $password === '') {
      $_SESSION['login_error'] = "Please fill in all fields.";
      header("Location: /admin/login");
      exit;
    }

    $account = (new Account())->findByEmail($email);

    if (!$account || !password_verify($password, $account['password'])) {
      $_SESSION['login_error'] = "Invalid login credentials.";
      $this->show();
      exit;
    }

    // Login OK
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $account['id'];
    $_SESSION['admin_email'] = $account['email'];
    $_SESSION['account_name'] = $account['display_name'];
    $_SESSION['admin_role'] = strtolower($account['role']); 

    // set permissions for this role
    Permissions::loadForRole($_SESSION['admin_role']);

    // update last seen to now
    (new Account())->update($_SESSION['admin_id'], [
      'last_seen' => date('Y-m-d H:i:s')
    ]);


    Flash::set('success', 'Account updated successfully.');

    header("Location: /admin");
    exit;
  }
  public function logout()
  {
    session_start();
    session_destroy();
    header("Location: /admin/login");
    exit;
  }
}
