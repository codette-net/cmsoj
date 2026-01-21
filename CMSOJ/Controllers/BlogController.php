<?php
namespace CMSOJ\Controllers;

use CMSOJ\Models\Post;
use CMSOJ\Template;


class BlogController {
    public function index(): void {
        $postModel = new Post();
        $result = $postModel->published();
        
        Template::view('CMSOJ/Views/blog/index.html', [
            'title' => 'Blog',
            'posts' => $result['data'],
            'meta' => $result['meta'],
        ]);

    }

    public function show(string $slug): void {
        $postModel = new Post();
        $post = $postModel->findBySlug($slug);
        if (!$post) {
            Template::view('CMSOJ/Views/404.html', ['title' => 'Post Not Found']);
            return;
        }
        Template::view('CMSOJ/Views/blog/post.html', [
            'title' => $post->title,
            'post' => $post
        ]);
        
    }

}