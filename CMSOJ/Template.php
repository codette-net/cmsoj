<?php

namespace CMSOJ;

class Template
{
    /**
     * Collected block contents during compilation of a single Template::view() call.
     * Reset in view() to avoid leakage across requests.
     */
    static $blocks = array();

    /**
     * Cache directory for compiled templates.
     * Note: this is a relative path, so it depends on process working directory.
     * In production it is usually safer to use an absolute path.
     */
    static $cache_path = 'cache/';

    /**
     * When false, templates recompile whenever cache is missing or stale.
     * When true, compilation happens only when cached template is missing or stale.
     */
    static $cache_enabled = FALSE;

    static function view($file, $data = array())
    {
        // Important: blocks must be reset per request/render.
        self::$blocks = [];

        /**
         * Make view variables accessible to components via $GLOBALS.
         * This is a pragmatic approach so components can read common variables
         * without explicitly threading them through every render call.
         *
         * Prefer passing props explicitly to components when possible.
         */
        $GLOBALS['__TEMPLATE_VIEW_VARS'] = $data;
        $GLOBALS['errors'] = $data['errors'] ?? ($_SESSION['errors'] ?? []);
        $GLOBALS['old']    = $data['old'] ?? ($_SESSION['old'] ?? []);

        // Compile (or reuse cached) template.
        $cached_file = self::cache($file);

        // Make $data keys available as variables in the view scope.
        extract($data, EXTR_SKIP);

        // Provide conventional $errors/$old for forms (fallback to session).
        $errors = $data['errors'] ?? ($_SESSION['errors'] ?? []);
        $old    = $data['old'] ?? ($_SESSION['old'] ?? []);

        // Render compiled template (a .php file in cache/).
        require $cached_file;

        /**
         * After rendering, clear validation state to avoid it persisting across pages.
         * Only unset if session is active.
         */
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION['errors'], $_SESSION['old']);
        }
    }

    static function resolvePath($file)
    {
        // If path is already absolute (Unix or Windows), return it.
        if (str_starts_with($file, '/') || preg_match('/^[A-Z]:/i', $file)) {
            return $file;
        }

        // Build absolute path from project root (CMSOJ is inside the project).
        return dirname(__DIR__) . '/' . $file;
    }

    static function cache($file)
    {
        // Ensure cache directory exists.
        if (!file_exists(self::$cache_path)) {
            mkdir(self::$cache_path, 0744);
        }

        // Map template path to a cache filename.
        $cached_file = self::$cache_path . str_replace(array('/', '.html'), array('_', ''), $file . '.php');

        /**
         * Recompile when:
         * - caching disabled, OR
         * - cached file missing, OR
         * - cached file older than the template file
         *
         * Note: staleness check uses filemtime($file) (the original template path).
         * If a partial file changes (included at runtime), you may need to clear cache.
         */
        if (!self::$cache_enabled || !file_exists($cached_file) || filemtime($cached_file) < filemtime($file)) {
            // Inline any {% extends %} and {% include %} directives first.
            $code = self::includeFiles(self::resolvePath($file));

            // Compile tags into PHP.
            $code = self::compileCode($code);

            // Guard header prevents direct access to cached file.
            file_put_contents($cached_file, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);
        }

        return $cached_file;
    }

    static function clearCache()
    {
        foreach (glob(self::$cache_path . '*') as $file) {
            unlink($file);
        }
    }

    static function compileCode($code)
    {
        /**
         * Compilation order matters.
         *
         * - Partials/components first so their tags become PHP calls early.
         * - Blocks/yields then determine final layout output.
         * - Loops and echos afterwards.
         * - Finally compile remaining {% ... %} as raw PHP.
         */
        $code = self::compilePartials($code);
        $code = self::compileComponents($code);
        $code = self::compileBlock($code);
        $code = self::compileYield($code);
        $code = self::compileForLoops($code);

        // Escaped echos should be compiled before raw echos (because {{{ }}} contains {{ }}).
        $code = self::compileEscapedEchos($code);
        $code = self::compileEchos($code);

        // Compile any remaining {% ... %} blocks into PHP.
        $code = self::compilePHP($code);

        // Cleanup stray block tags that weren't matched.
        $code = self::stripLeftoverBlockTags($code);

        return $code;
    }

    static function includeFiles($file)
    {
        /**
         * includeFiles() implements {% extends %} and {% include %} by inlining
         * file contents into one combined template string.
         *
         * This keeps the rest of the compiler simpler (it only sees one string).
         */
        $code = file_get_contents(self::resolvePath($file));

        preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $code, $matches, PREG_SET_ORDER);
        foreach ($matches as $value) {
            $code = str_replace($value[0], self::includeFiles($value[2]), $code);
        }

        // Remove the tags after inlining.
        $code = preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $code);

        return $code;
    }

    static function compileForLoops($code)
    {
        /**
         * For-loop syntax:
         *   {% for item in items %}
         *     ...
         *   {% endfor %}
         *
         * Limitation: "items" must be a simple variable name (no expressions).
         */
        $code = preg_replace(
            '/\{%[\s]*for\s+([A-Za-z_][A-Za-z0-9_]*)\s+in\s+([A-Za-z_][A-Za-z0-9_]*)\s*%}/',
            '<?php foreach ($$2 as $$1): ?>',
            $code
        );

        $code = preg_replace(
            '/\{%[\s]*endfor[\s]*%}/',
            '<?php endforeach; ?>',
            $code
        );

        return $code;
    }

    static function compilePHP($code)
    {
        /**
         * Catch-all: any remaining {% ... %} becomes raw PHP.
         * Use this carefully in templates to keep view logic minimal.
         */
        return preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code);
    }

    static function compileEchos($code)
    {
        /**
         * Raw echo tag: {{ ... }}
         *
         * Behavior:
         * - If expression is a quoted literal starting with "/", treat it as an asset path:
         *     {{ "/assets/css/app.css" }} -> Template::asset("/assets/css/app.css")
         * - If expression is a simple variable name:
         *     {{ title }} -> echo $title
         * - Otherwise, echo the expression as-is:
         *     {{ strtoupper($title) }} -> echo strtoupper($title)
         *
         * Note: this is NOT escaped output.
         */
        return preg_replace_callback(
            '/\{{\s*(.+?)\s*\}}/s',
            function ($m) {
                $expr = trim($m[1]);

                // If literal string beginning with "/", treat as asset.
                if (preg_match('/^[\'"]\/.+[\'"]$/', $expr)) {
                    return "<?php echo \\CMSOJ\\Template::asset($expr); ?>";
                }

                // If variable name (letters, numbers, underscores).
                if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $expr)) {
                    return "<?php echo \$$expr; ?>";
                }

                // Fallback: echo raw PHP expression.
                return "<?php echo $expr; ?>";
            },
            $code
        );
    }

    static function compileEscapedEchos($code)
    {
        /**
         * Escaped echo tag: {{{ ... }}}
         * This uses htmlentities(ENT_QUOTES, UTF-8).
         *
         * Recommendation: use triple-braces for user-generated/untrusted output.
         */
        return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
    }

    static function compileBlock($code)
    {
        /**
         * Block syntax:
         *   {% block name %} ... {% endblock %}
         *
         * Blocks are collected into Template::$blocks and removed from the
         * final template output string. Later, compileYield() will place block
         * content at {% yield name %} positions.
         *
         * @parent inside a block merges with the previous value.
         */
        if (!preg_match_all('/\{%[\s]*block\s+([a-zA-Z0-9_]+)[\s]*%}(.*?){%[\s]*endblock[\s]*%}/is', $code, $matches, PREG_SET_ORDER)) {
            return $code;
        }

        foreach ($matches as $value) {
            $blockName = trim($value[1]);
            $blockContent = $value[2];

            if (!array_key_exists($blockName, self::$blocks)) {
                self::$blocks[$blockName] = '';
            }

            if (strpos($blockContent, '@parent') === false) {
                self::$blocks[$blockName] = $blockContent;
            } else {
                self::$blocks[$blockName] = str_replace('@parent', self::$blocks[$blockName], $blockContent);
            }

            $code = str_replace($value[0], '', $code);
        }

        return $code;
    }

    static function compileYield($code)
    {
        /**
         * Yield syntax:
         *   {% yield name %}
         *
         * Each yield is replaced with the collected block content.
         * Unknown yields are stripped.
         */
        foreach (self::$blocks as $block => $value) {
            $code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $code);
        }

        $code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);

        return $code;
    }

    static function stripLeftoverBlockTags($code)
    {
        // Remove any stray block or endblock tags that weren't matched.
        $code = preg_replace('/\{%[\s]*block\s+[a-zA-Z0-9_]+[\s]*%}/i', '', $code);
        $code = preg_replace('/\{%[\s]*endblock[\s]*%}/i', '', $code);
        return $code;
    }

    static function compilePartials($code)
    {
        /**
         * Partial syntax:
         *   {% partial 'footer' %}
         *   {% partial 'pagination.html', ['meta' => $meta] %}
         *
         * Compiles to:
         *   Template::partial(...)
         *
         * Important: partial() includes raw files; partial templates are not compiled.
         */
        return preg_replace_callback(
            '/\{%[\s]*partial[\s]+(.+?)\s*%\}/is',
            function ($matches) {
                return "<?php \\CMSOJ\\Template::partial({$matches[1]}); ?>";
            },
            $code
        );
    }

    static function compileComponents($code)
    {
        /**
         * Component syntax:
         *   {% component 'admin/table', [ 'rows' => $rows ] %}
         *
         * Components are compiled and cached like normal templates.
         * renderComponent() returns the component output as a string.
         */
        return preg_replace_callback(
            '/\{%[\s]*component[\s]+\'([^\'"]+)\'(?:\s*,\s*(\[[^\%]*\]))?\s*%}/is',
            function ($m) {
                $componentFile = 'CMSOJ/Views/components/' . $m[1] . '.html';
                $props = $m[2] ?? '[]';
                return "<?php echo CMSOJ\\Template::renderComponent('$componentFile', $props); ?>";
            },
            $code
        );
    }

    public static function renderComponent(string $path, array $props)
    {
        // Compile or reuse cached component template.
        $compiled = self::cache($path);

        // Capture component output as a string.
        ob_start();
        extract($props, EXTR_SKIP);
        include $compiled;
        return ob_get_clean();
    }

    static function asset($path)
    {
        /**
         * Adds cache-busting query string based on filemtime().
         * $path must be a public path like "/assets/css/main.css".
         */
        $full = dirname(__DIR__) . '/public' . $path;
        if (file_exists($full)) {
            return $path . '?v=' . filemtime($full);
        }
        return $path;
    }

    public static function partial(string $file, array $data = []): void
    {
        /**
         * Partials are included as raw files (not compiled).
         *
         * Path rules:
         * - No "/" => assume CMSOJ/Views/partials/
         * - Missing extension => append ".html"
         */
        extract($data, EXTR_SKIP);

        if (!str_contains($file, '/')) {
            if (!str_contains($file, '.')) {
                $file .= '.html';
            }
            $file = 'CMSOJ/Views/partials/' . $file;
        } else {
            if (!str_contains(basename($file), '.')) {
                $file .= '.html';
            }
        }

        $path = self::resolvePath($file);

        if (!file_exists($path)) {
            throw new \Exception("Partial not found: {$file}");
        }

        include $path;
    }

    public static function merge(array $a, array $b): array
    {
        return array_merge($a, $b);
    }

    public static function http_build_query(array $params): string
    {
        return http_build_query($params);
    }

    public static function highlightSearch(string $text, string $term = ''): string
    {
        /**
         * Highlights the current search term (from $_GET['q']) inside $text.
         * Escapes the full text first, then wraps matches in <mark>.
         *
         * Note: $term parameter is currently ignored because $_GET['q'] is used.
         * If you want this reusable, consider using the passed $term when provided.
         */
        $term = trim($_GET['q'] ?? '');
        if ($term === '') {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        return preg_replace(
            '/' . preg_quote($term, '/') . '/i',
            '<mark>$0</mark>',
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
        );
    }
}
