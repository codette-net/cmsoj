<?php

namespace CMSOJ\Helpers;

use CMSOJ\Core\Model;

class BulkAction
{
  public static function handle(
    Model $model,
    array $actions,
    array $input
  ): int {
    $actionKey = $input['action'] ?? '';
    $ids       = $input['ids'] ?? [];

    if (!isset($actions[$actionKey])) {
      throw new \InvalidArgumentException('Invalid bulk action.');
    }

    if (empty($ids) || !is_array($ids)) {
      return 0;
    }

    $action = $actions[$actionKey];

    // Permission check
    if (!Permissions::can($action['permission'])) {
      http_response_code(403);
      exit('Not allowed');
    }

    return match ($action['handler']) {
      'delete' => $model->bulkDelete($ids),
      'update' => $model->bulkUpdate($ids, $action['data'] ?? []),
      default  => throw new \RuntimeException('Unsupported bulk handler')
    };
  }

  public static function requiresConfirmation(array $actions, string $action): bool
  {
    return !empty($actions[$action]['confirm']);
  }


}
