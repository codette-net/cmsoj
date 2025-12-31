<?php 
namespace CMSOJ\Models;

use CMSOJ\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';
    public function getValue(string $key, $default = null)
    {
        $stmt = $this->db()->prepare("SELECT value FROM {$this->table} WHERE `key` = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetchColumn();
        return $row !== false ? $row : $default;
    }

    public function setValue(string $key, $value): void
    {
        $stmt = $this->db()->prepare("
            INSERT INTO {$this->table} (`key`, `value`) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
        $stmt->execute([$key, $value]);
    }

    public function allSettings(): array
    {
        $stmt = $this->db()->prepare("SELECT `key`, `value` FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

    }
}
