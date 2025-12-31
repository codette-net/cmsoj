<?php

namespace CMSOJ;

class Template
{

	static $blocks = array();
	static $cache_path = 'cache/';
	static $cache_enabled = FALSE;

	static function view($file, $data = array())
	{
		self::$blocks = [];

		// Make view variables accessible to components via $GLOBALS
		$GLOBALS['__TEMPLATE_VIEW_VARS'] = $data;
		$GLOBALS['errors'] = $data['errors'] ?? ($_SESSION['errors'] ?? []);
		$GLOBALS['old']    = $data['old'] ?? ($_SESSION['old'] ?? []);


		$cached_file = self::cache($file);

		extract($data, EXTR_SKIP);

		$errors = $data['errors'] ?? ($_SESSION['errors'] ?? []);
		$old    = $data['old'] ?? ($_SESSION['old'] ?? []);

		require $cached_file;

		unset($_SESSION['errors'], $_SESSION['old']);
	}

	static function resolvePath($file)
	{
		// If path is already absolute, return it
		if (str_starts_with($file, '/') || preg_match('/^[A-Z]:/i', $file)) {
			return $file;
		}

		// Build absolute path from project root
		return dirname(__DIR__) . '/' . $file;
	}


	static function cache($file)
	{
		if (!file_exists(self::$cache_path)) {
			mkdir(self::$cache_path, 0744);
		}
		$cached_file = self::$cache_path . str_replace(array('/', '.html'), array('_', ''), $file . '.php');
		if (!self::$cache_enabled || !file_exists($cached_file) || filemtime($cached_file) < filemtime($file)) {
			$code = self::includeFiles(self::resolvePath($file));

			$code = self::compileCode($code);
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
		$code = self::compilePartials($code);
		$code = self::compileComponents($code);
		$code = self::compileBlock($code);
		$code = self::compileYield($code);
		$code = self::compileForLoops($code);
		$code = self::compileEscapedEchos($code);
		$code = self::compileEchos($code);
		$code = self::compilePHP($code);
		$code = self::stripLeftoverBlockTags($code);
		return $code;
	}

	static function includeFiles($file)
	{
		$code = file_get_contents(self::resolvePath($file));

		preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			$code = str_replace($value[0], self::includeFiles($value[2]), $code);
		}
		$code = preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $code);
		return $code;
	}

	static function compileForLoops($code)
	{
		// {% for item in items %}
		$code = preg_replace(
			'/\{%[\s]*for\s+([A-Za-z_][A-Za-z0-9_]*)\s+in\s+([A-Za-z_][A-Za-z0-9_]*)\s*%}/',
			'<?php foreach ($$2 as $$1): ?>',
			$code
		);

		// {% endfor %}
		$code = preg_replace(
			'/\{%[\s]*endfor[\s]*%}/',
			'<?php endforeach; ?>',
			$code
		);

		return $code;
	}


	static function compilePHP($code)
	{
		return preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code);
	}

	static function compileEchos($code)
	{
		return preg_replace_callback(
			'/\{{\s*(.+?)\s*\}}/s',
			function ($m) {

				$expr = trim($m[1]);

				// If literal string beginning with "/", treat as asset
				if (preg_match('/^[\'"]\/.+[\'"]$/', $expr)) {
					return "<?php echo \\CMSOJ\\Template::asset($expr); ?>";
				}

				// If variable name (letters, numbers, underscores)
				if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $expr)) {
					return "<?php echo \$$expr; ?>";
				}

				// Fallback: echo raw PHP expression
				return "<?php echo $expr; ?>";
			},
			$code
		);
	}


	static function compileEscapedEchos($code)
	{
		return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
	}

	static function compileBlock($code)
	{
		// Match: {% block name %} ... {% endblock %}
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
				// no @parent, override completely
				self::$blocks[$blockName] = $blockContent;
			} else {
				// merge with parent
				self::$blocks[$blockName] = str_replace('@parent', self::$blocks[$blockName], $blockContent);
			}

			// remove the whole block from template
			$code = str_replace($value[0], '', $code);
		}

		return $code;
	}


	static function compileYield($code)
	{
		foreach (self::$blocks as $block => $value) {
			$code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $code);
		}
		$code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);
		return $code;
	}

	static function stripLeftoverBlockTags($code)
	{
		// Remove any stray block or endblock tags that weren't matched
		$code = preg_replace('/\{%[\s]*block\s+[a-zA-Z0-9_]+[\s]*%}/i', '', $code);
		$code = preg_replace('/\{%[\s]*endblock[\s]*%}/i', '', $code);
		return $code;
	}

	static function compilePartials($code)
	{
		return preg_replace_callback(
			'/\{%[\s]*partial[\s]+(.+?)\s*%\}/is', // 👈 note the "s"
			function ($matches) {
				return "<?php \\CMSOJ\\Template::partial({$matches[1]}); ?>";
			},
			$code
		);
	}


	static function compileComponents($code)
	{
		return preg_replace_callback(
			'/\{%[\s]*component[\s]+\'([^\'"]+)\'(?:\s*,\s*(\[[^\%]*\]))?\s*%}/is',
			function ($m) {

				$componentFile = 'CMSOJ/Views/components/' . $m[1] . '.html';

				// If props exist, use them; else use empty array
				$props = $m[2] ?? '[]';

				return "<?php echo CMSOJ\\Template::renderComponent('$componentFile', $props); ?>";
			},
			$code
		);
	}

	public static function renderComponent(string $path, array $props)
	{
		$compiled = self::cache($path);

		ob_start();
		extract($props, EXTR_SKIP);
		include $compiled;
		return ob_get_clean();
	}

	static function asset($path)
	{
		$full = dirname(__DIR__) . '/public' . $path;
		if (file_exists($full)) {
			return $path . '?v=' . filemtime($full);
		}
		return $path;
	}

	public static function partial(string $file, array $data = []): void
	{
		extract($data, EXTR_SKIP);

		// If no extension is provided, assume Views/partials/*.html
		if (!str_contains($file, '.')) {
			$file = 'CMSOJ/Views/partials/' . $file . '.html';
		}

		// Resolve absolute path
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
