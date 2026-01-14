<?php

namespace CMSOJ\Controllers\Admin\Concerns;

use CMSOJ\Helpers\Csrf;
use CMSOJ\Helpers\Flash;
use CMSOJ\Helpers\Redirect;
use CMSOJ\Helpers\BulkAction;
use CMSOJ\Template;
use CMSOJ\Core\Model;

trait Bulkable
{
  /**
   * @param Model $model Model that supports bulkDelete/bulkUpdate
   * @param array $actions Action map (same format as AccountsController::bulkActions)
   * @param string $backUrl Where to go back after action / for confirm view
   * @param string $successLabel Used in flash message ("X $successLabel updated.")
   */
  protected function bulkEndpoint(Model $model, array $actions, string $backUrl, string $successLabel = 'items')
  {
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
      http_response_code(403);
      exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';
    $ids    = $_POST['ids'] ?? [];

    if (!isset($actions[$action])) {
      Flash::set('error', 'Invalid bulk action.');
      return Redirect::back()->send();
    }

    if (BulkAction::requiresConfirmation($actions, $action) && empty($_POST['confirmed'])) {
      return Template::view('CMSOJ/Views/admin/bulk-confirm.html', [
        'action'  => $action,
        'label'   => $actions[$action]['label'],
        'confirm' => $actions[$action]['confirm'],
        'ids'     => $ids,
        '_csrf'   => \CMSOJ\Helpers\Csrf::token(),
        'back'    => $backUrl,
      ]);
    }

    $count = BulkAction::handle($model, $actions, $_POST);

    Flash::set('success', "{$count} {$successLabel} updated.");
    header("Location: {$backUrl}");
    exit;
  }
}
