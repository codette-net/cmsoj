<?php
namespace CMSOJ\Controllers;

use CMSOJ\Models\Post;
use CMSOJ\Template;


class BlogController {
    public function index(): void {
        $postModel = new Post();
        $posts = $postModel->published();
        
        Template::view('CMSOJ/Views/blog/index.html', [
            'title' => 'Blog',
            'posts' => $posts
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

    public function create() {
        // Implementation for creating a new blog post
    }

    public function store() {
        // Implementation for saving a new blog post
    }

    public function edit($id) {
        // Implementation for editing an existing blog post
    }

    public function update($id) {
        // Implementation for updating an existing blog post
    }

    public function delete($id) {
        // Implementation for deleting a blog post
    }
}