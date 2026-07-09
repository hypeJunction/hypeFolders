<?php

namespace hypeJunction\Folders\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Plugin-specific regression guards for the Elgg 6.x -> 7.x migration of
 * hypefolders. Each test pins one FIXED behavior recorded in the migration
 * history so a bulk remediation pass cannot silently resurrect the legacy form.
 *
 * These are static source assertions (no Elgg boot): the fatals they guard
 * against fire at class-load / page-render on Elgg 7.x, so the signature has to
 * be caught in the source rather than at runtime.
 */
class MigrationRegressionsTest extends TestCase {

	private static function root(): string {
		return dirname(__DIR__, 2);
	}

	private static function read(string $rel): string {
		$path = self::root() . '/' . ltrim($rel, '/');
		return is_file($path) ? (string) file_get_contents($path) : '';
	}

	/** @return list<string> non-test PHP files under classes/ + lib/ + views/ */
	private static function pluginPhp(): array {
		$out = [];
		foreach (['classes', 'lib', 'views'] as $sub) {
			$base = self::root() . '/' . $sub;
			if (!is_dir($base)) {
				continue;
			}
			$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
			foreach ($it as $f) {
				$p = $f->getPathname();
				if (str_ends_with($p, '.php') && !str_contains($p, '/vendor/')) {
					$out[] = $p;
				}
			}
		}
		return $out;
	}

	/**
	 * ee95edd / FC-6x7x-10: Doctrine DBAL named-param keys must NOT carry the
	 * ':' prefix. A ':'-prefixed key throws MissingNamedParameter on
	 * executeStatement in Elgg 7. The SQL keeps ':name' placeholders; only the
	 * $params array keys are bare.
	 */
	public function testMainFolderDbalParamKeysOmitColonPrefix(): void {
		$src = self::read('classes/hypeJunction/Folders/MainFolder.php');
		$this->assertNotSame('', $src, 'MainFolder.php must exist');

		// The bound $params array uses bare keys.
		$this->assertStringContainsString("'relationship_id' =>", $src);
		$this->assertStringContainsString("'resource_guid' =>", $src);

		// A colon-prefixed ARRAY KEY (quoted) is the 7.x-fatal form and must be absent.
		$this->assertStringNotContainsString("':relationship_id'", $src);
		$this->assertStringNotContainsString("':resource_guid'", $src);
		$this->assertStringNotContainsString("':folder_guid'", $src);

		// The SQL itself still uses :name placeholders (that is correct DBAL).
		$this->assertStringContainsString(':relationship_id', $src);
	}

	/**
	 * b12194f: add.mjs must import the Elgg 7 'elgg/system_messages' module, not
	 * the removed AMD 'elgg/notify' module.
	 */
	public function testAddMjsImportsSystemMessagesNotElggNotify(): void {
		$src = self::read('views/default/folders/resources/add.mjs');
		$this->assertNotSame('', $src, 'add.mjs must exist');
		$this->assertStringContainsString("'elgg/system_messages'", $src);
		$this->assertStringNotContainsString("'elgg/notify'", $src);
	}

	/**
	 * 5751792: client JS was converted to Elgg 7 ESM (.mjs). No file may use the
	 * AMD define() wrapper, and each module must use ESM import statements.
	 */
	public function testClientJavaScriptIsEsmWithoutAmdDefine(): void {
		$modules = [
			self::read('views/default/folders/resources/add.mjs'),
			self::read('views/default/navigation/menu/folders.mjs'),
		];
		foreach ($modules as $i => $src) {
			$this->assertNotSame('', $src, "mjs module #{$i} must exist");
			$this->assertMatchesRegularExpression('/^\s*import\s/m', $src, 'ESM module must use import');
			$this->assertDoesNotMatchRegularExpression('/\bdefine\s*\(\s*\[/', $src, 'AMD define([...]) wrapper must be gone');
		}
	}

	/**
	 * e420e9a + 5e38875: MainFolder static event handlers take a single
	 * \Elgg\Event argument (create/update/delete,object fire with one arg in
	 * Elgg 7), and save() declares : bool to match ElggEntity::save(): bool.
	 */
	public function testMainFolderSignaturesMatchElgg7(): void {
		$src = self::read('classes/hypeJunction/Folders/MainFolder.php');
		foreach (['addCreatedResource', 'syncTitle', 'removeDeletedItems'] as $method) {
			$this->assertMatchesRegularExpression(
				'/function\s+' . $method . '\s*\(\s*\\\\Elgg\\\\Event\s+\$event\s*\)/',
				$src,
				"{$method} must take a single \\Elgg\\Event \$event argument"
			);
		}
		$this->assertMatchesRegularExpression('/function\s+save\s*\(\s*\)\s*:\s*bool/', $src, 'save() must declare : bool return type');
	}

	/**
	 * 7fc0052: Router / Permissions / Menus hook callbacks were migrated to the
	 * single \Elgg\Event signature. The legacy 4-arg ($hook, $type, $return,
	 * $params) form must not reappear anywhere in classes/.
	 */
	public function testEventCallbacksUseElggEventSignature(): void {
		$checks = [
			'classes/hypeJunction/Folders/Router.php' => 'entityUrlHandler',
			'classes/hypeJunction/Folders/Permissions.php' => 'checkContainerPermissions',
			'classes/hypeJunction/Folders/Menus.php' => 'setupFolderMenu',
		];
		foreach ($checks as $rel => $method) {
			$src = self::read($rel);
			$this->assertMatchesRegularExpression(
				'/function\s+' . $method . '\s*\(\s*\\\\Elgg\\\\Event\s+\$event\s*\)/',
				$src,
				"{$method} must take a single \\Elgg\\Event \$event argument"
			);
		}

		$violations = [];
		foreach (self::pluginPhp() as $file) {
			if (!str_contains($file, '/classes/')) {
				continue;
			}
			if (preg_match('/function\s+\w+\s*\(\s*\$hook\s*,\s*\$type\s*,\s*\$return\s*,\s*\$params\s*\)/', (string) file_get_contents($file))) {
				$violations[] = basename($file);
			}
		}
		$this->assertSame([], $violations, 'Legacy 4-arg hook signature resurfaced in: ' . implode(', ', $violations));
	}

	/**
	 * 0e66fde: Bootstrap extends \Elgg\DefaultPluginBootstrap. The 6.x-removed
	 * \Elgg\PluginBootstrap base must not be referenced.
	 */
	public function testBootstrapExtendsDefaultPluginBootstrap(): void {
		$src = self::read('classes/hypeJunction/Folders/Bootstrap.php');
		$this->assertStringContainsString('extends DefaultPluginBootstrap', $src);
		$this->assertStringContainsString('use Elgg\DefaultPluginBootstrap;', $src);
		$this->assertStringNotContainsString('Elgg\PluginBootstrap;', $src);
	}

	/**
	 * 53f7ad8: search_results.php runs the query through elgg_search() for both
	 * count and entities. The 2.x pattern relied on the removed 'search' event
	 * returning ['entities'] (null on 7.x -> elgg_view_entity_list TypeError).
	 */
	public function testSearchResultsUsesElggSearch(): void {
		$src = self::read('views/default/folders/search_results.php');
		$this->assertNotSame('', $src, 'search_results.php must exist');
		$this->assertMatchesRegularExpression("/'count'\s*=>\s*elgg_search\(/", $src);
		$this->assertMatchesRegularExpression("/'entities'\s*=>\s*elgg_search\(/", $src);
		$this->assertStringContainsString('elgg_view_entity_list(', $src);
	}

	/**
	 * 2b22a5f: the group listing resource reads the {guid} route param via
	 * elgg_extract('guid', $vars) -- the route defines {guid}, not
	 * {container_guid}.
	 */
	public function testGroupResourceViewReadsGuidRouteParam(): void {
		$src = self::read('views/default/resources/folders/group.php');
		$this->assertNotSame('', $src, 'resources/folders/group.php must exist');
		$this->assertStringContainsString("elgg_extract('guid', \$vars)", $src);
		$this->assertStringNotContainsString("elgg_extract('container_guid', \$vars)", $src);
	}

	/**
	 * e654d44 + 4398bf7 + 1012bc5: the 5.x-removed procedural lookups must be
	 * gone (fatal "Call to undefined function" on 7.x). These are NOT all in the
	 * generic removed-functions map (get_user_by_username / get_user_by_email),
	 * so guard them explicitly here.
	 */
	public function testNoRemovedLookupFunctions(): void {
		$removed = [
			'get_user_by_username',
			'get_user_by_email',
			'get_default_access',
			'check_entity_relationship',
		];
		$alt = implode('|', array_map('preg_quote', $removed));
		$re = '/(?<![\w>$:\\\\])(' . $alt . ')\s*\(/';

		$violations = [];
		foreach (self::pluginPhp() as $file) {
			foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
				if (preg_match($re, $line, $m)) {
					$violations[] = basename($file) . ':' . ($n + 1) . ' ' . $m[1] . '()';
				}
			}
		}
		$this->assertSame([], $violations, "Removed 5.x lookup functions still called:\n" . implode("\n", $violations));
	}
}
