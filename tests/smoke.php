<?php

declare(strict_types=1);

// Standalone smoke test, no PHPUnit needed.
// Loads classes manually so it works before `composer install`.

spl_autoload_register(function (string $class): void {
    $prefix = 'StringLogicCheck\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use StringLogicCheck\Exception\LexerException;
use StringLogicCheck\Exception\ParserException;
use StringLogicCheck\LogicParser;

$parser = new LogicParser();
$passed = 0;
$failed = 0;

$assert = function (string $name, bool $cond) use (&$passed, &$failed): void {
    if ($cond) {
        $passed++;
        echo "  OK   $name\n";
    } else {
        $failed++;
        echo "  FAIL $name\n";
    }
};

// True/false cases
$cases = [
    ['1', true],
    ['0', false],
    ['true', true],
    ['FALSE', false],
    ['t', true],
    ['f', false],
    ['1&1', true],
    ['1&0', false],
    ['0|1', true],
    ['!1', false],
    ['~0', true],
    ['NOT 0', true],
    ['!!1', true],
    ['NOT~!0', true],
    ['0|1&0', false],
    ['1&0|1', true],
    ['!(1&1)', false],
    ['(1|0)&(0|1)', true],
    ['  1   and  ( 0 or 1 ) ', true],
    ['1|1', true],
    ['1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(!1&(~1&NOT0))', false],
    ['1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(1&(1&NOT~!0))', true],
];

foreach ($cases as [$expr, $expected]) {
    $actual = $parser->logicCheck($expr);
    $assert("logicCheck($expr) === " . ($expected ? 'true' : 'false'), $actual === $expected);
}

// Error cases
$errorCases = [
    ['', ParserException::class],
    ['   ', ParserException::class],
    ['(1', ParserException::class],
    ['1)', ParserException::class],
    ['1 xor 0', LexerException::class],
    ['1 + 0', LexerException::class],
    ['1 &', ParserException::class],
];

foreach ($errorCases as [$expr, $expectedException]) {
    try {
        $parser->logicCheck($expr);
        $assert("error case '$expr' throws $expectedException", false);
    } catch (Throwable $e) {
        $assert("error case '$expr' throws $expectedException", $e instanceof $expectedException);
    }
}

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
