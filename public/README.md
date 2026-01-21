#  ** CMSOJ Lightweight PHP Framework – Documentation**

A reusable, lightweight MVC framework powering Art Restaurant Manezinho, designed to gradually replace a legacy procedural system.
It supports:
    • Modern routing (GET/POST, middleware, parameters)
    • Custom template engine (extends, blocks, partials, components, echo)
    • MVC structure (Controllers, Models, Views)
    • Services layer
    • Admin panel with authentication
    • Calendar system with AJAX frontend
    • Reservation/contact form with PHPMailer service
    • Menu system (sections, items, CRUD-ready)
    • Autoloading (Composer + internal autoloader)
    • Cache-compiled templates for speed

---

#  **1. Project Structure**

```
+---CMSOJ
ª   ª   Router.php                 # Route registration + dispatch + middleware
ª   ª   Template.php               # Template engine (parser + compiler)
ª   ª   
ª   +---Controllers
ª   ª   ª   BlogController.php     # Blog controller for public
ª   ª   ª   
ª   ª   +---Admin
ª   ª       ª   AccountsController.php
ª   ª       ª   AuthController.php
ª   ª       ª   BlogController.php
ª   ª       ª   DashboardController.php
ª   ª       ª   MediaController.php
ª   ª       ª   SettingsController.php
ª   ª       ª   
ª   ª       +---Concerns
ª   ª               Bulkable.php
ª   ª               
ª   +---Core                      # Framework internals
ª   ª       Config.php
ª   ª       Database.php
ª   ª       Env.php
ª   ª       Model.php
ª   ª       
ª   +---Helpers
ª   ª       Bulkaction.php
ª   ª       Csrf.php
ª   ª       Flash.php
ª   ª       Permissions.php
ª   ª       Redirect.php
ª   ª       Validator.php
ª   ª       
ª   +---Middleware                 
ª   ª       AccountAuth.php
ª   ª       AdminAuth.php         # Protects admin routes
ª   ª       
ª   +---Models
ª   ª       Account.php
ª   ª       Blog.php
ª   ª       BlogTopic.php
ª   ª       Calendar.php
ª   ª       Event.php
ª   ª       Media.php
ª   ª       Post.php
ª   ª       Setting.php
ª   ª       
ª   +---Routes
ª   ª       admin.php             # admin routes
ª   ª       web.php               # public routes
ª   ª       
ª   +---Services
ª   ª   +---admin
ª   +---Views
ª       ª   404.html
ª       ª   index.html
ª       ª   layout.html
ª       ª   scripts.html
ª       ª   
ª       +---admin                 # admin views
ª       ª   ª   bulk-confirm.html
ª       ª   ª   dashboard.html
ª       ª   ª   layout.html
ª       ª   ª   login.html
ª       ª   ª   main.html
ª       ª   ª   
ª       ª   +---accounts
ª       ª   ª       create.html
ª       ª   ª       edit.html
ª       ª   ª       index.html
ª       ª   ª       profile.html
ª       ª   ª       
ª       ª   +---blog
ª       ª   ª       edit.html
ª       ª   ª       index.html
ª       ª   ª       
ª       ª   +---media
ª       ª   ª       index.html
ª       ª   ª       
ª       ª   +---partials
ª       ª   ª       header.html
ª       ª   ª       sidebar.html
ª       ª   ª       
ª       ª   +---settings
ª       ª           index.html
ª       ª           
ª       +---blog
ª       ª       index.html
ª       ª       post.html
ª       ª       topic.html
ª       ª       
ª       +---components
ª       ª   ª   flash.html
ª       ª   ª   
ª       ª   +---admin
ª       ª       ª   search.html
ª       ª       ª   table.html
ª       ª       ª   
ª       ª       +---form
ª       ª               button.html
ª       ª               input.html
ª       ª               select.html
ª       ª               textarea.html
ª       ª               toggle.html
ª       ª               
ª       +---partials
ª               footer.html
ª               nav.html
ª               pagination.html
ª               
+---public                # Web root (only public-facing directory)
ª   ª   .htaccess
ª   ª   index.php         # Front controller
ª   ª   router.php        # Built-in PHP server router
ª   ª   
ª   +---assets
ª   ª   +---css
ª   ª   
ª   ª   +---fonts
ª   ª   +---img
ª   ª   +---js
ª   ª   +---svg
ª   ª           
ª   +---cache
ª   ª       
ª   +---uploads
+---uploads
ª       
+---vendor
    ª   autoload.php
    ª   
    +---composer
    ª       
    +---phpmailer
        +---phpmailer
            ª       
            +---src
            +---test
                        


```

---

#  **2. Routing System**

Router (CMSOJ/Router.php)
  Handles:
    • GET + POST
    • Route parameters: /menu/{id}
    • Middleware: AdminAuth::class
    • Controller dispatch
    • 404 fallback

Routes are defined in:

```
CMSOJ/Routes/web.php
CMSOJ/Routes/admin.php
```

Each route is registered using:

```php
$router->get('menu/{id}', [MenuController::class, 'show']);
$router->post('reservation', [ReservationController::class, 'submit']);
$router->get('admin', [DashboardController::class, 'index'], AdminAuth::class);
```

### **Dynamic parameters**

```php
$router->get('blog/{id}', function($id) {
    Template::view('CMSOJ/Views/blog.html', ['id' => $id]);
});
```

### **404 fallback**

If no route matches, Router sends:

```
CMSOJ/Views/404.html
```

---

# 🚦 **3. Front Controller**

`public/index.php` bootstraps the framework:

```php
// 1. Composer autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 2. Load .env + config
use CMSOJ\Core\Config;
Config::load();

// 3. Initialize router + template
use CMSOJ\Router;
use CMSOJ\Template;

$router = new Router();

// 4. Load routes
require dirname(__DIR__) . '/CMSOJ/Routes/web.php';
require dirname(__DIR__) . '/CMSOJ/Routes/admin.php';

// 5. Dispatch
$router->dispatch();

```

---

# 🔌 **4. PHP Built-in Server Support**

Because `.htaccess` is not supported, a router file is used:

**public/router.php**

```php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // serve static file
}

require __DIR__ . '/index.php';
```

Run server:

```
php -S localhost:8000 router.php
```

---

#  **5. Template Engine**

The custom engine supports:

| Feature              | Syntax                                   |
| -------------------- | ---------------------------------------- |
| Echo                 | `{{ variable }}`                         |
| Escaped echo         | `{{{ variable }}}`                       |
| PHP code             | `{% if ... %}`                           |
| Template inheritance | `{% extends 'layout' %}`                 |
| Blocks               | `{% block name %} ... {% endblock %}`    |
| Inserting blocks     | `{% yield name %}`                       |
| Partials             | `{% partial 'nav' %}`                    |
| Components           | `{% component 'card', { title: "X" } %}` |
| Cache busting        | `{{ "/assets/css/app.css" }}`            |

---

# 🧱 **6. Layout Inheritance**

### layout.html

```twig
<head>
  <title>Manezinho | {% yield title %}</title>
  {% yield meta %}
  {% yield css %}
</head>

<body>
  {% yield nav %}
  {% yield content %}
  {% yield scripts %}
  {% yield footer %}
</body>
```

### page.html

```twig
{% extends 'CMSOJ/Views/layout.html' %}

{% block title %}Events{% endblock %}
{% block meta %}@parent<meta name="description" content="...">{% endblock %}
{% block css %}@parent<link rel="stylesheet" href="/assets/css/calendar.css">{% endblock %}

{% block content %}
  {% partial 'nav' %}
  <h1>Events</h1>
{% endblock %}

{% block scripts %}
  @parent
  <script src="/assets/js/calendar.js"></script>
{% endblock %}
```

---

#  **7. Partials**

Reusable fragments inside:

```
CMSOJ/Views/partials/
```

Use in templates:

```twig
{% partial 'nav' %}
{% partial 'footer' %}
```

---

#  **8. Components**

Reusable UI elements with props.

### Example usage:

```twig
{% component 'card', { title: "Live Music", img: "/assets/img/ewi.jpg" } %}
```

### card.html example:

```html
<div class="card">
  <img src="{{ img }}" alt="{{ title }}">
  <h3>{{ title }}</h3>
</div>
```

---

#  **9. Cache Busting**

All `{{ "/assets/.../file" }}` paths become:

```
/assets/.../file?v=1698259387
```

where `1698259387` = filemtime().

### Usage

```html
<link rel="stylesheet" href="{{ "/assets/css/main.css" }}">
<script src="{{ "/assets/js/app.js" }}"></script>
<img src="{{ "/assets/img/photo.jpg" }}">
```

### How it works

`compileEchos()` is modified so every `{{ "/something" }}` is passed through:

```php
Template::asset("/something");
```

---

#  **10. View Include (Extends + Includes)**

### Extending a layout:

```twig
{% extends 'CMSOJ/Views/layout.html' %}
```

### Including a raw file inside content:

```twig
{% include 'CMSOJ/Views/somefile.html' %}
```

---

#  **11. Cache Directory**

Compiled templates are stored in `/cache/`.

Clear cache:

```php
Template::clearCache();
```

Or delete files inside `/cache`.

---

#  **12. File Path Resolving (Important)**

Because views are outside `/public`, paths must be resolved manually.

Template engine now resolves paths using:

```php
dirname(__DIR__) . '/' . $file
```

This ensures:

* PHP built-in server works
* XAMPP works
* Apache/Nginx work

---

#  **13. Admin Authentication **

##  Middleware:

namespace CMSOJ\Middleware;
```php
class AdminAuth {
    public function handle() {
        session_start();
        if (!isset($_SESSION['admin_logged_in'])) {
            header("Location: /admin/login");
            exit;
        }
    }
}
```

## Admin login

    • Email + password via Account Model
    • Sessions for login persistence
    • Redirect protected sections to /admin/login

## Models
All models extend the base class:
```php
class Account extends Model {
    protected string $table = 'accounts';
}
```

Base Model supports:
    • all()
    • find(id)
    • create(array)
    • update(id, array)
    • delete(id)