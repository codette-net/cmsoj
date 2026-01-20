<?php
namespace CMSOJ\Models;

use CMSOJ\Core\Database;
use CMSOJ\Core\Model; 

class Post extends Model {
    protected string $table = 'posts';

    public $id;
    public $title;
    public $slug;
    public $content;
    public $author_id;
    public $created_at;
    public $updated_at;

    public function findBySlug($slug) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }

    public function published() {
      return $this->list(
        [
          'where' => ['status' => 'published'],
          'columns' => ['id', 'title', 'slug', 'content', 'author_id', 'created_at', 'updated_at'],
          'sort' => 'created_at',
          'dir' => 'desc',
          'page' => 1,
          'perPage' => 10
      ]);
    }
}