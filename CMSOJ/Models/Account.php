<?php

namespace CMSOJ\Models;

use CMSOJ\Core\Model;

use PDO;

class Account extends Model
{
    protected string $table = 'accounts';

    public array $sortable = [
        'id',
        'name',
        'email',
        'display_name',
        'role',
        'updated_at',
        'last_seen',
    ];

    public array $searchable = [
        'name',
        'email',
        'display_name',
        'role',
    ];

    public function findByEmail(string $email)
    {
        $stmt = $this->db()->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listAccounts(array $params = [])
    {
        return $this->list([
            'columns'  => $this->sortable,
            'searchIn' => $this->searchable,
            'search' => trim($params['q'] ?? ''),
            'sort'     => $params['sort'] ?? 'id',
            'dir'      => $params['dir'] ?? 'desc',
            'page'     =>(int)($params['page'] ?? 1),
            'perPage'  => $params['perPage'] ?? 5
          
        ]);
    }
}
