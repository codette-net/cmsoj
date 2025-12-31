<?php

namespace CMSOJ\Core;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';

    public function db()
    {
        return Database::connect();
    }

    public function all()
    {
        return $this->db()->query("SELECT * FROM {$this->table}")->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->db()->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function list(array $options = [])
    {
        $db = $this->db();

        $page    = max(1, (int)($options['page'] ?? 1));
        $perPage = min(50, (int)($options['perPage'] ?? 10));
        $offset  = ($page - 1) * $perPage;

        $sort = $options['sort'] ?? $this->primaryKey;
        $dir  = strtolower($options['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        // column whitelist (important for security)
        $allowed = $options['columns'] ?? [$this->primaryKey];
        if (!in_array($sort, $allowed, true)) {
            $sort = $this->primaryKey;
        }

        $where  = [];
        $params = [];

        // exact where conditions
        foreach ($options['where'] ?? [] as $col => $val) {
            $where[] = "$col = ?";
            $params[] = $val;
        }
        $searching = false;
        // simple search (LIKE)
        if (!empty($options['search']) && !empty($options['searchIn'])) {
            $searching = true;
            $likes = [];
            foreach ($options['searchIn'] as $col) {
                $likes[] = "$col LIKE ?";
                $params[] = '%' . $options['search'] . '%';
            }
            $where[] = '(' . implode(' OR ', $likes) . ')';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';



        // main query
        $stmt = $db->prepare("
        SELECT SQL_CALC_FOUND_ROWS *
        FROM {$this->table}
        {$whereSql}
        ORDER BY {$sort} {$dir}
        LIMIT {$perPage} OFFSET {$offset}
    ");
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        // pagination meta
        $total = $db->query("SELECT FOUND_ROWS()")->fetchColumn();
        $pages = (int) ceil($total / $perPage);

        // count when searching


        if ($searching) {
            $countStmt = $db->prepare("
            SELECT COUNT(*) as cnt
            FROM {$this->table}
            {$whereSql}");
            $countStmt->execute($params);
            $totalFiltered = (int)$countStmt->fetchColumn();
        }


        return [
            'data' => $data,
            'meta' => [
                'page'  => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'total' => (int)$total,
                'totalFiltered' => $totalFiltered ?? (int)0,
            ]
        ];
    }


    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $values  = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db()->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$values})");
        $stmt->execute(array_values($data));

        return $this->db()->lastInsertId();
    }

    public function update($id, array $data)
    {
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));

        $stmt = $this->db()->prepare("UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?");
        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db()->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }


    public function bulkUpdate(array $ids, array $data): int
    {
        if (empty($ids) || empty($data)) {
            return 0;
        }

        $ids = array_map('intval', $ids);

        $set = implode(', ', array_map(
            fn($col) => "$col = ?",
            array_keys($data)
        ));

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "UPDATE {$this->table}
            SET {$set}
            WHERE {$this->primaryKey} IN ($placeholders)";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([...array_values($data), ...$ids]);

        return $stmt->rowCount();
    }

    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->db()->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} IN ($placeholders)"
        );

        $stmt->execute(array_map('intval', $ids));

        return $stmt->rowCount();
    }
}
