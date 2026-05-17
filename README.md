# StringLogicCheck

A tiny PHP library that evaluates boolean expression strings. It accepts a
mix of symbolic and word-style operators (case-insensitive, whitespace
optional) and returns a `bool`.

## Install

```bash
composer install
```

Requires PHP 8.1+.

## Usage

```php
use StringLogicCheck\LogicParser;

$parser = new LogicParser();

$parser->logicCheck('1 AND (true OR !0)');                 // true
$parser->logicCheck('!(1 & 0) AND (false OR t)');          // true
$parser->logicCheck('1andFaLseoR(TRUEAnD0)|((0|f)&0)');    // false
```

Errors throw `\StringLogicCheck\Exception\LogicParserException` (subclasses
`LexerException` and `ParserException`):

```php
use StringLogicCheck\Exception\LogicParserException;

try {
    $parser->logicCheck('1 xor 0');
} catch (LogicParserException $e) {
    echo $e->getMessage(); // Unknown keyword "x" at position 2.
}
```

## Supported syntax

| Operator | Aliases               |
|----------|-----------------------|
| literal  | `1`, `0`, `true`, `false`, `t`, `f` |
| AND      | `&`, `and`            |
| OR       | <code>&#124;</code>, `or` |
| NOT      | `!`, `~`, `not`       |
| group    | `( ... )`             |

Precedence (high → low): grouping, NOT, AND, OR. All matching is
case-insensitive, and whitespace is optional.

## Running the tests

```bash
composer test
```

## Demo

```bash
composer demo
# or
php index.php
```

## Back-compat

Legacy callers that did `require_once 'class/LogicParser.php'` continue to
work — `class/LogicParser.php` is a shim that aliases the global
`LogicParser` symbol to `\StringLogicCheck\LogicParser`.
