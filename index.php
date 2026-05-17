<?php

declare(strict_types=1);

if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'StringLogicCheck\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $path = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

use StringLogicCheck\Exception\LogicParserException;
use StringLogicCheck\LogicParser;

$parser = new LogicParser();

$examples = [
    '1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(1&(1&NOT~!0))',
    '1 & (TRUE OR !0)',
    '!(1 & 0) AND (false OR t)',
    '1|1',
    '(1 & 0',
];

foreach ($examples as $expression) {
    try {
        $result = $parser->logicCheck($expression) ? 'TRUE' : 'FALSE';
        printf("%-60s => %s\n", $expression, $result);
    } catch (LogicParserException $e) {
        printf("%-60s => ERROR: %s\n", $expression, $e->getMessage());
    }
}
