### “module flow”  (example: blog)

1. **Module definition**

   * What data it owns (tables/models)
   * Public endpoints vs admin endpoints
2. **System flow**

   * Route → Middleware → Controller → Model → Template → Response
3. **Steps to implement**

   * Add model(s)
   * Add routes (web + admin)
   * Add controller actions
   * Add views (public + admin)
   * Add permissions checks (if needed)
   * Add CSRF + validation
   * Add admin table + bulk actions pattern (using `Bulkable`)
4. **File checklist**

   * A checkbox list of the files you expect to touch/add
5. **Copy/paste snippets**

   * Route patterns
   * Typical controller structure
   * Typical model methods you expect to use
6. **Conventions**

   * Naming, folder placement, view naming
7. **Gotchas**

   * Common mistakes (csrf token missing, template paths, pagination params)

