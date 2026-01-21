Great — routing is the right next doc because it locks in **how people are supposed to think** when extending CMSOJ.

Below is a **clean, practical `docs/02-routing.md`** written as a *quick reference + convention guide*.
It assumes the reader already read `01-request-lifecycle.md`.

---

```markdown
# CMSOJ Routing

This document explains how routing works in CMSOJ and establishes the
conventions for defining, organizing, and extending routes.

Routing in CMSOJ is intentionally explicit and minimal.
There is no auto-discovery, no annotations, and no hidden behavior.

---

## Routing Philosophy

CMSOJ routing follows these principles:

- Routes are registered manually
- Route definitions are readable PHP
- Public and admin routes are separated
- Middleware is opt-in and explicit
- URL structure mirrors application structure

Routing is not a DSL or configuration layer.
It is plain PHP code.

---

## Where Routes Live

CMSOJ defines routes in two files:

```

CMSOJ/Routes/
├── web.php     # Public-facing routes
└── admin.php   # Admin-only routes

````

This separation is intentional and should be preserved.

- `web.php` contains routes accessible to visitors
- `admin.php` contains routes protected by authentication middleware

---

## Basic Route Definition

Routes are registered on the `$router` instance.

### GET route

```php
$router->get('blog', [BlogController::class, 'index']);
````

### POST route

```php
$router->post('admin/blog/store', [Admin\BlogController::class, 'store']);
```

Each route consists of:

1. HTTP method (`get`, `post`)
2. URI pattern (relative to site root)
3. Controller class + method
4. Optional middleware

---

## URI Patterns

### Static routes

```php
$router->get('about', [PageController::class, 'about']);
```

Matches:

```
/about
```

### Dynamic parameters

CMSOJ supports dynamic parameters using `{}` syntax.

```php
$router->get('blog/{slug}', [BlogController::class, 'show']);
```

Matches:

```
/blog/hello-world
/blog/my-first-post
```

The parameter value is passed to the controller method in order.

```php
public function show(string $slug) {
    // ...
}
```

---

## Multiple Parameters

Routes may contain multiple dynamic parameters.

```php
$router->get(
    'blog/{topic}/{slug}',
    [BlogController::class, 'showByTopic']
);
```

Controller:

```php
public function showByTopic(string $topic, string $slug) {
    // ...
}
```

---

## Admin Routes

Admin routes are defined in `CMSOJ/Routes/admin.php`.

They usually include middleware to protect access.

Example:

```php
use CMSOJ\Middleware\AdminAuth;
use CMSOJ\Controllers\Admin\BlogController;

$router->get(
    'admin/blog',
    [BlogController::class, 'index'],
    AdminAuth::class
);
```

### Convention

* All admin routes are prefixed with `admin`
* All admin controllers live in `Controllers/Admin`
* All admin views live in `Views/admin`

This keeps the admin surface clearly scoped.

---

## Middleware Usage

Middleware can be attached to a route as the fourth argument.

```php
$router->get(
    'admin/settings',
    [SettingsController::class, 'index'],
    AdminAuth::class
);
```

### Middleware responsibilities

Middleware should:

* Perform access checks
* Redirect or abort when needed
* Avoid business logic
* Run before controller execution

Middleware should **not**:

* Render templates
* Mutate application state
* Contain domain logic

---

## Route Organization Conventions

### Grouping by feature (recommended)

Inside `web.php`:

```php
// Blog
$router->get('blog', [BlogController::class, 'index']);
$router->get('blog/{slug}', [BlogController::class, 'show']);

// Pages
$router->get('', [HomeController::class, 'index']);
$router->get('contact', [PageController::class, 'contact']);
```

Inside `admin.php`:

```php
// Blog admin
$router->get('admin/blog', [BlogController::class, 'index'], AdminAuth::class);
$router->get('admin/blog/create', [BlogController::class, 'create'], AdminAuth::class);
$router->post('admin/blog/store', [BlogController::class, 'store'], AdminAuth::class);
```

Avoid mixing unrelated features.

---

## CRUD Route Patterns (Admin)

CMSOJ does not enforce REST, but follows a predictable CRUD pattern.

Typical admin routes:

```
GET    admin/blog            index
GET    admin/blog/create     create
POST   admin/blog/store      store
GET    admin/blog/{id}/edit  edit
POST   admin/blog/{id}/update update
POST   admin/blog/{id}/delete delete
```

This consistency makes controllers easier to read and reuse.

---

## POST Requests & CSRF

All state-changing routes (POST) must include CSRF validation.

In routes:

* No special handling is required

In controllers:

* CSRF tokens must be validated before processing input

Routing itself remains agnostic to CSRF concerns.

---

## 404 Handling

If no route matches the incoming request:

* CMSOJ renders `Views/404.html`
* No exception is thrown
* No fallback controller is executed

This behavior is intentional and simple.

---

## What Routing Does Not Do

CMSOJ routing deliberately avoids:

* Route groups
* Route names
* Automatic resource controllers
* HTTP verb spoofing
* Dependency injection
* Route caching

If those features are needed, CMSOJ is likely no longer the right tool.

---

## Common Mistakes

* Defining admin routes in `web.php`
* Forgetting middleware on admin routes
* Mixing rendering logic into routes
* Using routes as business logic containers

Routes should remain declarative.

---

## Summary

Routing in CMSOJ is:

* Explicit
* Minimal
* Predictable
* Easy to debug

A good rule of thumb:

> If you can understand your routes by reading them top to bottom, they are correct.

The next step is understanding how controllers are structured and how they
interact with routes.

Proceed to: `docs/03-controllers.md`
