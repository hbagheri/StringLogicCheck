<?php

/**
 * Back-compat shim: lets legacy callers keep doing
 *     require_once 'class/LogicParser.php';
 *     $p = new LogicParser();
 * by aliasing the global LogicParser to the namespaced one.
 *
 * New code should depend on \StringLogicCheck\LogicParser via composer autoload.
 */

declare(strict_types=1);

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'StringLogicCheck\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

if (!class_exists('LogicParser', false)) {
    class_alias(\StringLogicCheck\LogicParser::class, 'LogicParser');
}
