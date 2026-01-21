You’re right — my previous `03-controllers.md` assumed helper APIs that don’t match your actual implementation (notably `Permissions::check`, `Redirect::to`, and `Csrf::validate` behavior). Thanks for pasting the helpers; that’s the correct source of truth.

Also: in this chat, I “find the files” from what you’ve already provided (tree + code snippets + any uploaded files). When we want docs to stay accurate, we should treat these helper APIs as the contract and document them as-is.

Here is a corrected rewrite of **`docs/03-controllers.md`** that matches your helpers exactly.

---

```markdown
# CMSOJ Controllers

This document defines the role, structure, and conventions for controllers in CMSOJ.

Controllers are the coordination layer: they connect routes, middleware, models,
helpers, and templates. They should stay readable and avoid accumulating
business logic that belongs elsewhere.

---

## Controller Philosophy

CMSOJ controllers aim to be:

- Thin and explicit
- Easy to read top-to-bottom
- Focused on request/response coordination
- Consistent across modules

Controllers should not become a second “model layer” or a “service layer”.

---

## Controller Responsibilities

A controller method may:

- Read request input (`$_GET`, `$_POST`, `$_FILES`)
- Validate input (via `Validator`)
- Check permissions (via `Permissions::can`)
- Call models for CRUD work
- Invoke bulk actions (via `BulkAction`)
- Set flash messages (via `Flash`)
- Redirect users (via `Redirect`)
- Render templates (via `Template`)

A controller method should not:

- Contain SQL queries
- Contain large HTML strings
- Duplicate validation rules in multiple places
- Implement permission maps (those live in `Permissions`)
- Perform heavy file system logic (prefer services later)

---

## Controller Types

### Public Controllers

Location:
```

CMSOJ/Controllers/

```

Public controllers serve visitors and typically only read data and render views.

Common traits:

- No admin middleware
- Minimal side effects
- Render templates directly

---

### Admin Controllers

Location:
```

CMSOJ/Controllers/Admin/

````

Admin controllers manage CRUD, uploads, configuration, and other state changes.

Common traits:

- Protected by middleware at the route level (e.g. `AdminAuth`)
- Use CSRF validation for POST
- Validate input
- Redirect after POST (Post/Redirect/Get)
- Use flash messages and old input on errors

---

## Recommended Method Structure

A typical controller action should follow this order:

1. Start session if needed (many helpers depend on `$_SESSION`)
2. CSRF validate for state-changing requests (POST)
3. Validate input
4. Authorization / permission checks
5. Perform model operations
6. Set flash message
7. Redirect (for POST) or render view (for GET)

---

## CSRF in Controllers

CSRF helper:

- `Csrf::token()` generates/returns a session token
- `Csrf::validate($token)` returns `true/false` (does not throw)

Because `validate()` is boolean, controllers must decide how to handle failure.

Recommended pattern for POST handlers:

```php
if (!\CMSOJ\Helpers\Csrf::validate($_POST['_csrf'] ?? null)) {
    http_response_code(403);
    exit('Invalid CSRF token');
}
````

In templates, include the token in forms:

```html
<input type="hidden" name="_csrf" value="{{ csrf }}">
```

(Your controller can pass `'csrf' => \CMSOJ\Helpers\Csrf::token()` to the view, or a shared layout can do it.)

---

## Validation in Controllers

Validator API:

```php
$validator = \CMSOJ\Helpers\Validator::make($_POST, [
  'title' => 'required|min:3',
  'email' => 'required|email',
  'bio'   => 'nullable|min:10'
]);

if ($validator->fails()) {
  // redirect back with errors + old input
}
```

### Redirecting back with errors + old input

Your `Redirect` helper supports a fluent pattern:

```php
\CMSOJ\Helpers\Redirect::back()
  ->withErrors($validator)
  ->withOld()
  ->send();
```

This sets:

* `$_SESSION['errors']` from `$validator->errors()`
* `$_SESSION['old']` from `$_POST`
* and redirects to `HTTP_REFERER` (or `/admin` fallback)

---

## Permissions in Controllers

Permissions helper:

* `Permissions::loadForRole($role)` loads a permission list into session
* `Permissions::can('permission.key')` returns `true/false`

CMSOJ does not provide `Permissions::check()`; controllers should be explicit:

```php
if (!\CMSOJ\Helpers\Permissions::can('accounts.delete')) {
    http_response_code(403);
    exit('Not allowed');
}
```

### Where to load permissions

The permission map is role-based and stored in `$_SESSION['permissions']`.

Loading typically happens during login / session setup, for example in an auth
controller after verifying credentials:

```php
\CMSOJ\Helpers\Permissions::loadForRole($accountRole);
```

Middleware (like `AdminAuth`) should focus on authentication, not role maps,
unless you intentionally want permission loading centralized there.

---

## Flash Messages

Flash helper API:

* `Flash::set($type, $message)`
* `Flash::get($type)`
* `Flash::all()`
* `Flash::clear()`

Typical controller usage:

```php
\CMSOJ\Helpers\Flash::set('success', 'Post updated');
```

Then in views/components you render and clear.

---

## Redirects

Redirect helper supports:

* `Redirect::back()` + fluent chaining
* `Redirect::toReturnTo($returnTo, $fallback)` with open-redirect prevention

### Back redirect with state

```php
\CMSOJ\Helpers\Redirect::back()
  ->withErrors($validator)
  ->withOld()
  ->send();
```

### Return-to redirect (safe internal URL)

Useful after login:

```php
\CMSOJ\Helpers\Redirect::toReturnTo($_GET['returnTo'] ?? null, '/admin');
```

This sanitizes the target to internal relative paths only.

---

## Bulk Actions (Admin Tables)

Bulk actions are centralized via `BulkAction::handle()`.

API:

```php
$count = \CMSOJ\Helpers\BulkAction::handle(
  $model,
  $actions,
  $_POST
);
```

The helper:

* Reads `action` and `ids` from input
* Checks the action exists
* Checks permission using `Permissions::can(...)`
* Executes `bulkDelete()` or `bulkUpdate()` on the model

A typical controller method:

```php
public function bulk() {
    if (!\CMSOJ\Helpers\Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $actions = [
        'delete' => [
            'handler' => 'delete',
            'permission' => 'accounts.delete',
            'confirm' => true
        ],
        'publish' => [
            'handler' => 'update',
            'permission' => 'blog.edit',
            'data' => ['published' => 1]
        ]
    ];

    $count = \CMSOJ\Helpers\BulkAction::handle(new \CMSOJ\Models\Post(), $actions, $_POST);

    \CMSOJ\Helpers\Flash::set('success', "{$count} items updated");
    \CMSOJ\Helpers\Redirect::back()->send();
}
```

Note:

* `BulkAction` will `exit('Not allowed')` with 403 if permission fails.
* Invalid action throws `InvalidArgumentException`.

Controllers should catch exceptions if you want a softer UX.

---

## Rendering Views

Controllers render templates via `Template::view(...)` (framework-level).

Recommended:

* GET actions render views
* POST actions redirect

---

## Session Keys Used by CMSOJ

CMSOJ relies on a small set of conventional session keys shared between helpers,
controllers, and views.

These keys are not hidden or abstracted; they are part of the framework contract.

Controllers and views may safely rely on these keys existing when relevant.

---

### `_csrf`

Type: `string`

Purpose:
- CSRF protection for state-changing requests

Set by:
- `Csrf::token()`

Used by:
- `Csrf::validate($token)`

Notes:
- Stored once per session
- Must be included as a hidden input in POST forms

---

### `permissions`

Type: `array<string>`

Purpose:
- Stores permission identifiers for the authenticated user

Set by:
- `Permissions::loadForRole($role)`

Used by:
- `Permissions::can($permission)`

Notes:
- Permission strings are flat and explicit
- Controllers are responsible for enforcing permissions

---

### `flash`

Type: `array<string, string>`

Purpose:
- One-request user feedback messages

Set by:
- `Flash::set($type, $message)`

Read by:
- `Flash::get($type)`
- `Flash::all()`

Cleared by:
- `Flash::clear()`

Notes:
- Typically rendered in a shared component
- Should be cleared after display

---

### `errors`

Type: `array<string, string>`

Purpose:
- Validation error messages

Set by:
- `Redirect::back()->withErrors($validator)`

Used by:
- Views and form components

Notes:
- Keys correspond to field names
- Intended for one request only

---

### `old`

Type: `array<string, mixed>`

Purpose:
- Repopulating form input after validation failure

Set by:
- `Redirect::back()->withOld()`

Used by:
- Form views and components

Notes:
- Usually mirrors `$_POST`
- Should not be trusted as sanitized data

---

### `__redirect_back`

Type: `string`

Purpose:
- Temporary storage for HTTP referer during redirects

Set by:
- `Redirect::back()`

Used by:
- `Redirect::send()`

Notes:
- Internal helper key
- Not intended for direct use in controllers or views

---

## Session Conventions

- CMSOJ does not wrap or abstract `$_SESSION`
- Helpers are responsible for starting sessions when needed
- Controllers should assume session state exists in admin contexts
- Views should read session data indirectly via helpers

If new session keys are introduced, they should be documented here.

---

## Common Controller Smells

* Duplicated validation rules across methods
* Permission checks scattered inconsistently
* POST actions rendering views directly
* Large blocks of array-mapping / formatting logic
* File upload logic taking over controller methods

These are signs that:

* helpers might need small additions, or
* a service class is justified (only when repetition appears)

---


## Summary

In CMSOJ, controllers:

* Validate input using `Validator::make()`
* Use `Csrf::validate()` explicitly (boolean)
* Enforce authorization via `Permissions::can()`
* Use `Flash::set()` for feedback
* Redirect with `Redirect::back()->...->send()` or `Redirect::toReturnTo(...)`
* Delegate bulk table behavior to `BulkAction::handle()`

Proceed to: `docs/04-models.md`

