<?php

use CMSOJ\Router;
use CMSOJ\Middleware\AdminAuth;
use CMSOJ\Middleware\AccountAuth;
use CMSOJ\Controllers\Admin\DashboardController;
use CMSOJ\Controllers\Admin\AuthController;
use CMSOJ\Controllers\Admin\AccountsController;
use CMSOJ\Controllers\Admin\SettingsController;

use CMSOJ\Controllers\Admin\BlogController as AdminBlogController;
use CMSOJ\Controllers\Admin\MediaController as AdminMediaController;


/** @var Router $router */

// LOGIN (public)
$router->get('admin/login', [AuthController::class, 'show']);
$router->post('admin/login', [AuthController::class, 'submit']);

// LOGOUT
$router->get('admin/logout', [AuthController::class, 'logout']);

// DASHBOARD (protected)
$router->get('admin', [DashboardController::class, 'index'], AdminAuth::class);

// Accounts management (protected)
$router->get('admin/accounts/create', [AccountsController::class, 'create'], AccountAuth::class);
$router->post('admin/accounts/create', [AccountsController::class, 'store'], AccountAuth::class);
$router->get('admin/accounts', [AccountsController::class, 'index'], AccountAuth::class);
$router->get('admin/accounts/{id}', [AccountsController::class, 'index'], AccountAuth::class);

$router->get('admin/accounts/edit/{id}', [AccountsController::class, 'edit'], AccountAuth::class);
$router->post('admin/accounts/edit/{id}', [AccountsController::class, 'update'], AccountAuth::class);
$router->post('admin/accounts/bulk', [AccountsController::class, 'bulk'], AccountAuth::class);

$router->get('admin/profile', [AccountsController::class, 'profile'], AccountAuth::class);

// Settings management (protected)
$router->get('admin/settings', [SettingsController::class, 'index'], AdminAuth::class);
$router->post('admin/settings', [SettingsController::class, 'save'], AdminAuth::class);

$router->get('admin/blog', [AdminBlogController::class, 'index'], AdminAuth::class);
$router->get('admin/blog/new', [AdminBlogController::class, 'create'], AdminAuth::class);
$router->post('admin/blog', [AdminBlogController::class, 'store'], AdminAuth::class);

$router->get('admin/blog/edit/{id}', [AdminBlogController::class, 'edit'], AdminAuth::class);
$router->post('admin/blog/update/{id}', [AdminBlogController::class, 'update'], AdminAuth::class);

$router->post('admin/blog/delete/{id}', [AdminBlogController::class, 'delete'], AdminAuth::class);

// Media (for TinyMCE + library)
$router->get('admin/media', [AdminMediaController::class, 'index'], AdminAuth::class);
$router->post('admin/media/upload', [AdminMediaController::class, 'upload'], AdminAuth::class);

// TinyMCE image upload endpoint (returns {location: "..."} )
$router->post('admin/media/tinymce', [AdminMediaController::class, 'tinymceUpload'], AdminAuth::class);
