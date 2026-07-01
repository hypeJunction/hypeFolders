<?php

$elgg_root = getenv('ELGG_ROOT') ?: '/var/www/html';
if (!file_exists($elgg_root . '/vendor/autoload.php')) {
    throw new \RuntimeException("Elgg not found at $elgg_root. Run tests inside the Docker container.");
}
require_once $elgg_root . '/vendor/autoload.php';

// Elgg's test classes (Elgg\IntegrationTestCase, etc.) live under the package's
// engine/tests/classes and are NOT in the composer autoloader when Elgg is
// installed as a dependency (--no-dev). Register them explicitly, otherwise the
// test class fails to load with "Class Elgg\IntegrationTestCase not found".
$test_classes = $elgg_root . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($test_classes) {
    $file = $test_classes . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$plugin_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($plugin_autoload)) {
    require_once $plugin_autoload;
}

\Elgg\Application::loadCore();
