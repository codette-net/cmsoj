<?php

namespace CMSOJ\Controllers\Admin;

use CMSOJ\Controllers\Admin\AuthController;
use CMSOJ\Models\Account;
use CMSOJ\Template;
use CMSOJ\Helpers\Permissions;
use CMSOJ\Helpers\Validator;
use CMSOJ\Helpers\Redirect;
use CMSOJ\Helpers\Flash;
use CMSOJ\Helpers\Csrf;
use CMSOJ\Helpers\BulkAction;
use CMSOJ\Controllers\Admin\Concerns\Bulkable;

class AccountsController

{
    use Bulkable;

    public function index()
    {
        if (Permissions::can('accounts.view_all')) {
            $model = new Account();
            $result = $model->listAccounts($_GET);

            $accounts = $result['data'];
            $meta     = $result['meta'];
            $sortable  = $model->sortable;
        } else {
            $accounts = [(new Account())->find($_SESSION['admin_id'])];
            $meta = null;
            $sortable = [];
        }


        $rows = array_map(function ($a) {
            return [
                'id' => $a['id'],
                'cells' => [
                $a['id'],
                Template::highlightSearch($a['name']),
                Template::highlightSearch($a['email']),
                Template::highlightSearch($a['display_name']),
                $a['role'],
                date('Y-m-d H:i', strtotime($a['updated_at'] ?? '')),
                date('Y-m-d H:i', strtotime($a['last_seen'] ?? '')),
                "<a href='/admin/accounts/edit/{$a['id']}'>Edit</a>"
                ]
            ];
        }, $accounts);

        $bulkActions = array_filter($this->bulkActions(), function ($action) {
            return Permissions::can($action['permission']);
        });

        return Template::view('CMSOJ/Views/admin/accounts/index.html', [
            'headers' => [
                "id" => "ID",
                "name" => "Name",
                "email" => "Email",
                "display_name" => "Display Name",
                "role" => "Role",
                "updated_at" => "Last Updated",
                "last_seen" => "Last Seen",
                "actions" => "Actions"
            ],
            'rows'  => $rows,
            'meta'  => $meta,
            'query' => $_GET,
            'bulk' => [
                'endpoint' => '/admin/accounts/bulk',
                'actions' => $bulkActions ?? []
            ],
            'sortable' => $sortable,
            'title' => 'Accounts',
            'selected' => 'accounts'
        ]);
    }

    public function profile()
    {
        $account = (new Account())->find($_SESSION['admin_id']);

        return Template::view('CMSOJ/Views/admin/accounts/profile.html', [
            'title' => 'Profile',
            'account' => $account
        ]);
    }

    public function create()
    {
        // only admins can create accounts
        if (!Permissions::can('accounts.create')) {
            http_response_code(403);
            exit("You are not allowed to create accounts.");
        }
        return Template::view('CMSOJ/Views/admin/accounts/create.html', ['title' => 'Create Account']);
    }

    public function store()
    {
        if (!Permissions::can('accounts.create')) {
            http_response_code(403);
            exit('Not allowed');
        }

        $validator = Validator::make($_POST, [
            'name'         => 'required',
            'display_name' => 'required',
            'email'        => 'required|email',
            'password'     => 'required|min:8',
            'role'         => 'required',
        ]);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withOld()
                ->send();
        }

        $data = [
            'name'         => $_POST['name'],
            'display_name' => $_POST['display_name'],
            'email'        => $_POST['email'],
            'password'     => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role'         => $_POST['role'],
            'updated_at'   => date('Y-m-d H:i:s'),
            'last_seen'    => null
        ];

        (new Account())->create($data);

        Flash::set('success', 'Account created successfully.');
        header("Location: /admin/accounts");
        exit;
    }




    public function edit($id)
    {
        $account = (new Account())->find((int)$id);

        $isAdmin = strtolower($_SESSION['admin_role']) === 'admin';
        $isSelf  = $account['id'] == $_SESSION['admin_id'];

        if (!$isAdmin && !$isSelf) {
            http_response_code(403);
            exit("You are not allowed to edit this account.");
        }


        return Template::view('CMSOJ/Views/admin/accounts/edit.html', ['title' => 'Edit Accounts', 'account' => $account]);
    }

    public function update($id)
    {   
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }

        $account = (new Account())->find($id);

        // Permissions
        $isAdmin = Permissions::can('accounts.edit_other');
        $isSelf  = $_SESSION['admin_id'] == $id;

        if (!$isAdmin && !$isSelf) {
            http_response_code(403);
            exit("Unauthorized");
        }

        $validator = Validator::make($_POST, [
            'name'         => 'required',
            'display_name' => 'required',
            'email'        => 'required|email',
            'password'     => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withOld()
                ->send();
        }

        $data = [
            'name'         => $_POST['name'],
            'display_name' => $_POST['display_name'],
            'email'        => $_POST['email'],
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if ($isSelf) {
                (new AuthController())->logout();
            }
        }

        if ($isAdmin) {
            $data['role'] = $_POST['role'];
        }

        (new Account())->update($id, $data);

        Flash::set('success', 'Account updated successfully.');
        header("Location: /admin/accounts");
        exit;
    }

    protected function bulkActions(): array
    {
        return [
            'delete' => [
                'label'      => 'Delete',
                'permission' => 'accounts.delete',
                'handler'    => 'delete',
                'confirm'    => 'Delete selected accounts?',
            ],
            'deactivate' => [
                'label'      => 'Deactivate',
                'permission' => 'accounts.edit',
                'handler'    => 'update',
                'data'       => ['active' => 0],
            ],
        ];
    }

   public function bulk()
  {
    return $this->bulkEndpoint(new Account(), $this->bulkActions(), '/admin/accounts', 'accounts');
  }
}
