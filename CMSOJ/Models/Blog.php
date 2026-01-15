<?php

namespace CMSOJ\Models;
use CMSOJ\Core\Model;

class Blog extends Model
{
    protected array $posts = [];

    public function addPost($post) {
        $this->posts[] = $post;
    }

    public function getPosts() {
        return $this->posts;
    }
}