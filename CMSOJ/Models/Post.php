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
        $stmt = $this->db()->prepare("SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            $post = new Post();
            foreach ($data as $key => $value) {
                $post->$key = $value;
            }
            return $post;
        }
        return null;
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