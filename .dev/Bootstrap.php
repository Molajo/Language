<?php
/**
 * Bootstrap for Testing
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
include_once __DIR__ . '/CreateClassMap.php';

if (! defined('PHP_VERSION_ID')) {
    $version = explode('.', phpversion());
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

$base     = substr(__DIR__, 0, strlen(__DIR__) - 5);
$classmap = array();

$results  = createClassMap($base . '/Source/Handler', 'Molajo\\Language\\Handler\\');
$classmap = array_merge($classmap, $results);
$results  = createClassMap($base . '/Factories/Language', 'Molajo\\Factories\\Language\\');
$classmap = array_merge($classmap, $results);
$results  = createClassMap($base . '/vendor/commonapi/language', 'CommonApi\\Language\\');
$classmap = array_merge($classmap, $results);
$results  = createClassMap($base . '/vendor/commonapi/exception', 'CommonApi\\Exception\\');
$classmap = array_merge($classmap, $results);

$classmap['Molajo\\Language\\Adapter'] = $base . '/Source/Adapter.php';
ksort($classmap);

spl_autoload_register(
    function ($class) use ($classmap) {
        if (array_key_exists($class, $classmap)) {
            require_once $classmap[$class];
        }
    }
);
