<?php
/**
 * PHPUnit bootstrap for hypeFolders plugin tests.
 * Plugin installed at {elgg_root}/mod/hypefolders/
 */

// tests/ -> mod/hypefolders/ -> mod/ -> elgg_root/
$elggRoot = dirname(__DIR__, 3);

require_once $elggRoot . '/vendor/autoload.php';

// Load Elgg test classes (UnitTestCase, IntegrationTestCase, Seeding trait)
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Plugin's PSR-0 classes directory (classes/hypeJunction/Folders/*.php).
// Registered manually because IntegrationTestCase will not auto-load a plugin's
// classes/ dir unless the plugin is active in the test DB.
$pluginRoot = dirname(__DIR__);
spl_autoload_register(function ($class) use ($pluginRoot) {
    $prefix = 'hypeJunction\\Folders\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $pluginRoot . '/classes/hypeJunction/Folders/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

if (file_exists($pluginRoot . '/vendor/autoload.php')) {
    require_once $pluginRoot . '/vendor/autoload.php';
}

\Elgg\Application::loadCore();
