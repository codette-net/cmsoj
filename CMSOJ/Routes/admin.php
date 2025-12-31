<?php

use CMSOJ\Router;
use CMSOJ\Middleware\AdminAuth;
use CMSOJ\Middleware\AccountAuth;
use CMSOJ\Controllers\Admin\DashboardController;
use CMSOJ\Controllers\Admin\AuthController;
use CMSOJ\Controllers\Admin\AccountsController;
use CMSOJ\Controllers\Admin\SettingsController;
use CMSOJ\Controllers\Admin\MenuSectionController;
use CMSOJ\Controllers\Admin\MenuItemController;

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

// restaurant menu sections management (protected)

$router->get('admin/menu/sections',[MenuSectionController::class, 'index'], AdminAuth::class
);

$router->post(
  'admin/menu/sections/delete/{id}',
  [MenuSectionController::class, 'delete'],
  AdminAuth::class
);

$router->post(
  'admin/menu/sections/update',
  [MenuSectionController::class, 'updateInline'],
  AdminAuth::class
);

$router->get(
    'admin/menu/sections/new',
    [MenuSectionController::class, 'create'],
    AdminAuth::class
);

$router->get(
    'admin/menu/sections/edit/{id}',
    [MenuSectionController::class, 'edit'],
    AdminAuth::class
);

$router->post(
    'admin/menu/sections/save',
    [MenuSectionController::class, 'save'],
    AdminAuth::class
);


$router->get(
    'admin/menu/items',
    [MenuItemController::class, 'index'],
    AdminAuth::class
);

$router->get(
    'admin/menu/items/create',
    [MenuItemController::class, 'create'],
    AdminAuth::class
);

$router->post(
    'admin/menu/items/store',
    [MenuItemController::class, 'store'],
    AdminAuth::class
);

$router->get(
    'admin/menu/items/edit/{id}',
    [MenuItemController::class, 'edit'],
    AdminAuth::class
);

$router->post(
    'admin/menu/items/update/{id}',
    [MenuItemController::class, 'update'],
    AdminAuth::class
);

$router->post(
    'admin/menu/items/update-inline',
    [MenuItemController::class, 'updateInline'],
    AdminAuth::class
);