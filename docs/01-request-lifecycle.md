Perfect — this is the right moment to do this.
We’ll treat the **blog as a living test module**, and the docs as a *thinking tool* to reveal gaps in the core.

Below is the **first real CMSOJ documentation file**, designed as a **quick-reference + system map**.
This will live **separate from README**, under `docs/`.

I’ll start with the **most important document**:

---

# `docs/01-request-lifecycle.md`

This document explains *how CMSOJ actually works* end-to-end.
Everything else builds on this mental model.

---

````markdown
# CMSOJ Request Lifecycle

This document explains the full request lifecycle in CMSOJ, from HTTP request
to rendered response.

Understanding this flow is essential before building or extending modules
(e.g. blog, media, accounts).

CMSOJ favors an explicit and readable flow over hidden framework magic.

---

## High-Level Overview

A typical CMSOJ request follows this path:

1. Web server receives HTTP request
2. `public/index.php` bootstraps the system
3. Routes are registered
4. Router matches the request
5. Middleware runs (if defined)
6. Controller action executes
7. Models are queried or mutated
8. A template is rendered (or a redirect returned)
9. Response is sent to the browser

---

## 1. Entry Point: `public/index.php`

All requests enter CMSOJ through a **single front controller**.

Responsibilities:

- Load Composer autoloader
- Load environment configuration
- Initialize the router
- Register routes
- Dispatch the request

Nothing else should happen here.

This keeps the request lifecycle visible and predictable.

---

## 2. Environment & Configuration

Before routing begins:

- `.env` values are loaded
- Database credentials are resolved
- Environment-dependent behavior is configured

CMSOJ avoids global config state where possible, but configuration is loaded
early so all downstream code can rely on it.

---

## 3. Route Registration

Routes are registered manually in two files:

- `CMSOJ/Routes/web.php` (public)
- `CMSOJ/Routes/admin.php` (admin)

Example:

```php
$router->get('blog', [BlogController::class, 'index']);
$router->get('blog/{slug}', [BlogController::class, 'show']);

$router->get(
    'admin/blog',
    [Admin\BlogController::class, 'index'],
    AdminAuth::class
);
````

Routes define:

* HTTP method
* URL pattern
* Controller + method
* Optional middleware

No auto-discovery or annotations are used.

---

## 4. Router Matching & Dispatch

When `dispatch()` is called:

1. The router compares the request URI against registered routes
2. Dynamic parameters (e.g. `{id}`, `{slug}`) are extracted
3. If a route matches:

   * Middleware is executed (if defined)
   * Controller method is called
4. If no route matches:

   * `Views/404.html` is rendered

The router is intentionally simple and synchronous.

---

## 5. Middleware Execution

Middleware runs **before** the controller.

Typical use cases:

* Authentication
* Authorization
* Access control
* Redirecting unauthenticated users

Example:

```php
class AdminAuth {
    public function handle() {
        session_start();

        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin/login');
            exit;
        }
    }
}
```

Middleware should:

* Perform checks
* Redirect or exit if needed
* Avoid business logic

---

## 6. Controller Execution

Controllers contain **application logic**, not rendering logic.

Typical responsibilities:

* Validate input
* Call models
* Decide which view to render
* Redirect after state changes

Example (simplified):

```php
class BlogController {
    public function index() {
        $posts = Post::published();
        Template::view('CMSOJ/Views/blog/index.html', [
            'posts' => $posts
        ]);
    }
}
```

Controllers should remain thin and readable.

---

## 7. Models & Database Access

All models extend `CMSOJ\Core\Model`.

Responsibilities:

* Represent database tables
* Perform queries
* Return structured data

CMSOJ models are:

* Explicit
* PDO-based
* Free of ORM magic
* Designed for CRUD-style applications

Business rules should stay close to models when appropriate.

---

## 8. Template Rendering

Templates are rendered via `Template::view()`.

Features:

* Compiled templates (cached)
* Layout inheritance
* Blocks and yields
* Partials and components
* Asset cache-busting

Example:

```php
Template::view('CMSOJ/Views/blog/post.html', [
    'post' => $post
]);
```

Templates are parsed once and cached for performance.

---

## 9. Response Types

A controller may return:

* Rendered HTML (most common)
* Redirect (after POST requests)
* JSON (optional, for AJAX endpoints)

CMSOJ does not enforce a response object abstraction.
Headers and output are handled directly.

---

## 10. End of Request

Once output is sent:

* PHP finishes execution
* No background jobs or deferred logic
* No hidden lifecycle hooks

This keeps CMSOJ easy to reason about and debug.

---
