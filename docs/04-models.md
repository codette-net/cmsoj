Great, this is a key document. Models are where CMSOJ deliberately draws a line between “helpful abstraction” and “ORM complexity”.

Below is **`docs/04-models.md`**, written to match how your `Model` base class and helpers (BulkAction, Controllers) actually work today, and to clearly state what *should* and *should not* live in models.

---

```markdown
# CMSOJ Models

This document defines the role, structure, and conventions for models in CMSOJ.

Models represent application data and provide a thin abstraction over database
operations. They are not full ORMs and do not attempt to hide SQL or database
reality.

---

## Model Philosophy

CMSOJ models follow these principles:

- Models represent database tables
- Models encapsulate CRUD logic
- Models are explicit and predictable
- Models avoid magic behavior
- Models are simple by default

CMSOJ does not aim to replicate Eloquent, Doctrine, or ActiveRecord patterns.
If that level of abstraction is required, CMSOJ is no longer the right tool.

---

## Base Model

All models extend:

```

CMSOJ\Core\Model

```

The base model provides:

- PDO-backed database access
- Table binding via `$table`
- Common CRUD helpers
- Bulk operations (used by admin tables)

Models are expected to be lightweight and readable.

---

## Model Location & Naming

Models live in:

```

CMSOJ/Models/

````

Naming conventions:

- One model per database table
- Singular class names
- Table name defined explicitly

Example:

```php
namespace CMSOJ\Models;

use CMSOJ\Core\Model;

class Post extends Model
{
    protected string $table = 'posts';
}
````

CMSOJ does not infer table names automatically.

---

## Responsibilities of a Model

A CMSOJ model may:

* Represent a database table
* Fetch records
* Create records
* Update records
* Delete records
* Encapsulate query logic specific to that table
* Provide helper methods for common queries

A CMSOJ model should not:

* Read from `$_POST` or `$_GET`
* Perform request validation
* Handle permissions
* Render templates
* Redirect users
* Depend on session state

---

## CRUD Operations

The base `Model` provides common CRUD-style methods.

Typical usage from controllers:

```php
Post::create([
  'title' => $title,
  'content' => $content
]);
```

```php
Post::update($id, [
  'title' => $title
]);
```

```php
Post::delete($id);
```

```php
Post::find($id);
```

```php
Post::all();
```

These methods return structured data (arrays or objects, depending on your base
implementation) and do not perform side effects beyond database access.

---

## Bulk Operations

CMSOJ models support bulk operations, used by admin tables.

Required methods:

* `bulkDelete(array $ids): int`
* `bulkUpdate(array $ids, array $data): int`

These are invoked via the `BulkAction` helper, not directly from views.

Example usage (via controller):

```php
BulkAction::handle(new Post(), $actions, $_POST);
```

Models should ensure bulk operations are safe and scoped to their table.

---

## Query Helper Methods

Models may define **domain-specific query helpers**.

Example:

```php
class Post extends Model
{
    protected string $table = 'posts';

    public static function published()
    {
        return self::where('published', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
```

Guidelines:

* Query helpers should express intent clearly
* Avoid large conditional logic
* Prefer multiple small methods over one complex method

---

## Business Logic in Models

Light business logic is acceptable **when it belongs to the data**.

Good examples:

* Determining publish state
* Formatting database fields
* Scoping queries (published, archived, visible)

Bad examples:

* Permission checks
* Redirect decisions
* Request-specific branching
* Session-dependent logic

When logic starts depending on *who* is making the request or *how* it was made,
it no longer belongs in the model.

---

## Validation and Models

CMSOJ models do **not** validate input.

Validation responsibilities belong to controllers (via `Validator`).

Reasons:

* Validation is request-context dependent
* Different controllers may apply different rules
* Models remain reusable and predictable

---

## Error Handling

Models should:

* Fail loudly on programmer errors
* Let database exceptions bubble up when appropriate
* Avoid swallowing errors silently

Controllers are responsible for handling failures gracefully for the user.

---

## Returning Data

Models should return:

* Plain arrays
* Simple objects
* PDO result structures

Models should not return:

* HTML
* JSON responses
* Redirect instructions

Serialization decisions belong to controllers or views.

---

## Common Model Smells

* Models accessing `$_SESSION`
* Models performing permission checks
* Models growing beyond a few hundred lines
* Models duplicating controller logic
* Models with many unrelated responsibilities

These indicate missing abstractions or overly broad models.

---

## Summary

In CMSOJ:

* Models represent tables
* Models encapsulate data access
* Models stay simple and explicit
* Controllers decide how model data is used
* Helpers coordinate cross-cutting concerns

If a model becomes complex, it is a signal to rethink boundaries.

---

Proceed to:

`docs/05-views-templating.md`

This will cover:

* Template engine conventions
* Layouts, blocks, and yields
* Partials and components
* Admin UI patterns
