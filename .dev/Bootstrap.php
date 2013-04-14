<?php
/**
 * Language
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   MIT
 */
define('MOLAJO', 'This is a Molajo Distribution');

if (substr($_SERVER['DOCUMENT_ROOT'], - 1) == '/') {
    define('ROOT_FOLDER', $_SERVER['DOCUMENT_ROOT']);
} else {
    define('ROOT_FOLDER', $_SERVER['DOCUMENT_ROOT'] . '/');
}

$base = substr(__DIR__, 0, strlen(__DIR__) - 5);
define('BASE_FOLDER', $base);

//include BASE_FOLDER . '/Tests/Testcase1/Data.php';

$classMap = array(
    'Molajo\\Language\\Exception\\LanguageException'     => BASE_FOLDER . '/Exception/LanguageException.php',
    'Molajo\\Language\\Exception\\ExceptionInterface' => BASE_FOLDER . '/Exception/ExceptionInterface.php',
    'Molajo\\Language\\Api\\LanguageInterface'       => BASE_FOLDER . '/Api/LanguageInterface.php',
    'Molajo\\Language\\Type\\AbstractType'            => BASE_FOLDER . '/Type/AbstractType.php',
    'Molajo\\Language\\Type\\PhpMailerType'           => BASE_FOLDER . '/Type/PhpMailerType.php',
    'Molajo\\Language\\Adapter'                       => BASE_FOLDER . '/Adapter.php',

    'PhpMailer\\phpmailer' => '/Users/amystephen/Sites/Standard/Vendor/PhpMailer/phpmailer.php',
);

spl_autoload_register(
    function ($class) use ($classMap) {
        if (array_key_exists($class, $classMap)) {
            require_once $classMap[$class];
        }
    }
);
