# CMSOJ Views and Templating

This document explains how CMSOJ renders views using its custom template engine,
and establishes conventions for layouts, blocks, partials, components, and
escaping rules.

CMSOJ templating is intentionally PHP-friendly and explicit. It compiles `.html`
templates to cached `.php` files for speed, but it does not try to be a full
templating DSL like Twig.

---

## View Locations

Views live in:

```

CMSOJ/Views/

````

Common conventions:

- Public pages: `CMSOJ/Views/*.html` and `CMSOJ/Views/blog/*`
- Admin pages: `CMSOJ/Views/admin/*`
- Partials: `CMSOJ/Views/partials/*` and `CMSOJ/Views/admin/partials/*` (optional)
- Components: `CMSOJ/Views/components/*` and `CMSOJ/Views/components/admin/*`

---

## Rendering a View

Controllers render views using:

```php
\CMSOJ\Template::view('CMSOJ/Views/blog/index.html', [
  'title' => 'Blog',
  'posts' => $posts,
]);
````

### What `Template::view()` does

* Resets template blocks for this request
* Makes `$data` available to the main view via `extract()`
* Compiles the template into `/cache/*.php` if needed
* Requires the compiled file
* Unsets `$_SESSION['errors']` and `$_SESSION['old']` after rendering (if session is active)

### Special note: components and `$GLOBALS`

`Template::view()` copies the view data into:

* `$GLOBALS['__TEMPLATE_VIEW_VARS']`
* `$GLOBALS['errors']` and `$GLOBALS['old']`

This allows components to access common variables even when they are rendered in
different scopes. Treat this as a convenience, not as a primary data channel.

---

## Template Compilation and Cache

Templates compile into:

```
cache/<template-path-as-underscores>.php
```

Compiled files are regenerated when:

* cache is disabled, or
* cached file is missing, or
* cached file is older than the source template

Note: cache invalidation is currently based on the *main* template file mtime.
If partials are changed, you may need to clear cache manually depending on how
the partial is included (see "Partials" below).

Clear cache:

```php
\CMSOJ\Template::clearCache();
```

---

## Layouts, Blocks, and Yield

CMSOJ supports layout inheritance using:

* `{% extends ... %}`
* `{% block name %} ... {% endblock %}`
* `{% yield name %}` in the parent layout

### Layout example

`CMSOJ/Views/layout.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Manezinho | {% yield title %}</title>
  {% yield meta %}
  {% yield css %}
</head>

<body>
  {% yield nav %}
  {% yield content %}
  {% yield footer %}
  {% yield scripts %}
</body>
</html>

{% block meta %}
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
{% endblock %}

{% block css %}
<link rel="stylesheet" href='{{ "/assets/css/main.css" }}' />
<link rel="stylesheet" href='{{ "/assets/css/components.css" }}'>
<link rel="stylesheet" href='{{ "/assets/css/style.css" }}'>
{% endblock %}
```

### Child template example

```twig
{% extends CMSOJ/Views/layout.html %}

{% block title %} {{ $title }} {% endblock %}

{% block meta %}
  <meta name="description" content="...">
{% endblock %}

{% block content %}
  {% partial 'CMSOJ/Views/partials/nav.html' %}
  <h1>CMSOJ</h1>
{% endblock %}

{% block scripts %}
  @parent
  <script src="/assets/js/imgscroller.js"></script>
{% endblock %}
```

### `@parent` behavior

Inside a `{% block %}`, `@parent` is replaced with the previous value for that
block (from the parent layout or earlier compilation step).

Use `@parent` when you want to append scripts/styles rather than replace them.

---

## Includes and Extends

CMSOJ supports:

```twig
{% extends 'CMSOJ/Views/layout.html' %}
{% include 'CMSOJ/Views/partials/nav.html' %}
```

Implementation detail:

* Both `extends` and `include` are resolved by physically inlining file contents
  before other compilation passes.

---

## Partials

Partials are included using:

```twig
{% partial 'footer' %}
{% partial 'pagination.html', ['meta' => $meta, 'query' => $query] %}
{% partial 'CMSOJ/Views/partials/nav.html' %}
```

### How partial paths resolve

`Template::partial($file, $data)` has path rules:

* If `$file` contains no `/`:

  * assumes `CMSOJ/Views/partials/`
  * appends `.html` if no extension
* If `$file` contains `/`:

  * uses that path
  * appends `.html` only if missing extension

Partials are included via `include $path;` (they are not compiled by the template
engine). This is important:

* Partials can contain PHP
* Partials will not process `{% block %}`, `{% component %}`, `{% for %}`, or `{{ }}` tags
  unless they were already compiled as part of a compiled template (they are not)

Practical convention:

* Use partials for simple fragments (nav/footer/pagination) that are mostly HTML/PHP.
* If you want templating syntax inside the fragment, prefer a component.

---

## Components

Components are reusable “widgets” with inputs/props.

```twig
{% component 'admin/table', [
  'headers' => $headers,
  'rows' => $rows,
  'sortable' => $sortable,
  'query' => $query,
  'bulk' => $bulk
] %}
```

### How components work

* Component tag compiles to `Template::renderComponent($file, $props)`
* Components are compiled and cached like regular views
* Component rendering uses output buffering:

  * `extract($props)`
  * `include $compiledComponent`
  * returns buffered HTML string

Components are the preferred way to build reusable UI widgets because they:

* accept props
* can use templating syntax (since they are compiled)
* can contain PHP for internal logic

---

## Loop Syntax

CMSOJ supports a simple for-each loop syntax:

```twig
{% for post in posts %}
  <h2>{{ $post['title'] }}</h2>
{% endfor %}
```

Important limitation:

* The compiler currently supports `for <item> in <list>` where `<list>` is a
  simple variable name (not an expression). Example: `posts`, not `$posts` and
  not `posts.items`.

---

## PHP Logic Blocks

CMSOJ compiles any `{% ... %}` into raw PHP:

```twig
{% if (\CMSOJ\Helpers\Permissions::can('accounts.create')) : %}
  <a href="/admin/accounts/create">Create</a>
{% endif %}
```

This is powerful and should be used sparingly. Keep view logic limited to
presentation concerns (show/hide, simple loops).

Avoid heavy computation and any database work in templates.

---

## Output and Escaping Rules (Very Important)

CMSOJ has two echo forms:

### 1) Raw echo: `{{ ... }}`

`{{ ... }}` compiles to `echo ...` without escaping.

Examples:

```twig
{{ $title }}
{{ $post['title'] }}
{{ strtoupper($title) }}
```

Use raw echo only when you fully trust the value or you intentionally want to
output HTML.

### 2) Escaped echo: `{{{ ... }}}`

Triple braces are escaped using `htmlentities(..., ENT_QUOTES, 'UTF-8')`.

Example:

```twig
{{{ $post['title'] }}}
```

Default guideline:

* Use `{{{ ... }}}` for any user-generated or untrusted content
* Use `{{ ... }}` for trusted strings or when you need HTML output

---

## Asset Cache-Busting

A special case exists inside `{{ ... }}`:

If the expression is a *literal string* that starts with `/`, it is treated as
an asset path and compiled to:

```php
\CMSOJ\Template::asset("/assets/css/main.css")
```

Example:

```html
<link rel="stylesheet" href='{{ "/assets/css/main.css" }}' />
```

This becomes:

```
/assets/css/main.css?v=<filemtime>
```

Note:

* The asset transform only triggers when the expression is a quoted literal.
  Example: `{{ "/assets/x.css" }}` works.
  Example: `{{ $path }}` does not trigger asset handling.

---

## Admin UI Conventions

### Admin table component

Your admin table component is a good example of a UI widget:

* Receives headers, rows, sortable keys, query state
* Optionally renders bulk selection form
* Handles sort links by rebuilding query params
* Includes CSRF token when bulk actions are enabled

Key conventions to keep consistent:

* Bulk forms include:

  * `<input type="hidden" name="_csrf" value="...">`
  * checkboxes named `ids[]`
  * action dropdown named `action`

* Accessibility:

  * "Select all rows" checkbox has an `aria-label`
  * row checkboxes have per-row labels

If you create more table-like components, keep the same input names so the bulk
controller actions remain reusable.

### Admin form input component

Example:

```html
<div class="form-group">
  <label for="{{ $id }}">{{ $label }}</label>

  <input
    type="{{ $type ?? 'text' }}"
    id="{{ $id }}"
    name="{{ $name }}"
    value="{{ $value ?? '' }}"
    placeholder="{{ $placeholder ?? '' }}"
    class="form-control {{ $error ? 'is-invalid' : '' }}"
  >

  <?php if (!empty($error)): ?>
    <div class="invalid-feedback">{{ $error }}</div>
  <?php endif; ?>
</div>
```

Conventions:

* `label` must match `for="{{ $id }}"`
* `id` should be unique per page
* `name` matches the POST field name
* errors are shown near the field
* `is-invalid` class is used for styling invalid state

Recommended pattern:

* Controllers set `$_SESSION['errors']` and `$_SESSION['old']` via:
  `Redirect::back()->withErrors(...)->withOld()->send()`
* Views/components read from these keys to populate values and error messages
---

## Summary

CMSOJ views support:

* Layout inheritance (`extends`, `block`, `yield`, `@parent`)
* Include/extend by inlining file contents (`includeFiles`)
* Partials for lightweight fragments (included as raw PHP/HTML)
* Components for reusable compiled widgets (props + caching)
* Raw echo `{{ }}` and escaped echo `{{{ }}}`
* Simple loop syntax `{% for x in items %}`

Proceed to: `docs/06-security.md`