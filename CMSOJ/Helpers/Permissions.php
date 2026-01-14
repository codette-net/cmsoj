<?php

namespace CMSOJ\Helpers;

class Permissions
{
    public static function loadForRole(string $role)
    {
        $map = [
            'admin' => [
                'accounts.view_all',
                'accounts.create',
                'accounts.edit_other',
                'accounts.edit_self',
                'accounts.update_role',
                'accounts.delete',
                'events.delete',
                'events.edit'

            ],
            'user' => [
                'accounts.edit_self'
            ]
        ];

        $_SESSION['permissions'] = $map[$role] ?? [];
    }

    public static function can(string $permission): bool
    {
        return in_array($permission, $_SESSION['permissions'] ?? []);
    }
}
