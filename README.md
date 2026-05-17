# StringLogicCheck

A small, dependency-free PHP library that evaluates a boolean expression
written as a string and returns a `bool`. It accepts a mix of symbolic and
word-style operators, is case-insensitive, and tolerates optional whitespace.

```php
(new StringLogicCheck\LogicParser())->logicCheck('1 AND (true OR !0)'); // true
```

[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-777BB4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](#license)

---

## Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Supported syntax](#supported-syntax)
- [Grammar](#grammar)
- [Error handling](#error-handling)
- [Testing](#testing)
- [Project layout](#project-layout)
- [Backward compatibility](#backward-compatibility)
- [Contributing](#contributing)
- [License](#license)

## Features

- Evaluates boolean expressions of arbitrary depth and nesting.
- Multiple aliases per operator (symbolic and word forms).
- Case-insensitive; whitespace is optional.
- Clear, typed exceptions on invalid input (no `die()`).
- Pure PHP 8.1+, no runtime dependencies.
- Backward compatible with the original `class/LogicParser.php` entry point.

## Requirements

- PHP **8.1** or newer.
- [Composer](https://getcomposer.org/) (only if you want autoloading or to run
  the PHPUnit suite). The library can be used without Composer via a simple
  PSR-4 autoloader; see [Without Composer](#without-composer).

## Installation

```bash
composer require hbagheri/string-logic-check
```

Or, when working directly inside this repository:

```bash
composer install
```

### Without Composer

If you cannot use Composer, register a minimal PSR-4 autoloader pointing at
`src/`:

```php
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
```

The shipped `index.php` and `class/LogicParser.php` already do this as a
fallback when `vendor/autoload.php` is missing.

## Quick start

```php
use StringLogicCheck\LogicParser;

$parser = new LogicParser();

$parser->logicCheck('1');                                       // true
$parser->logicCheck('TRUE AND FALSE');                          // false
$parser->logicCheck('!(1 & 0) AND (false OR t)');               // true
$parser->logicCheck('1andFaLseoR(TRUEAnD0)|((0|f)&0)');         // false

// The expression can also be passed at construction time:
(new LogicParser('1 | 0'))->logicCheck();                       // true
```

## Supported syntax

| Element | Aliases                                       |
|---------|-----------------------------------------------|
| literal | `1`, `0`, `true`, `false`, `t`, `f`           |
| AND     | `&`, `and`                                    |
| OR      | <code>&#124;</code>, `or`                     |
| NOT     | `!`, `~`, `not`                               |
| group   | `( ... )`                                     |

Rules:

- Operators and keywords are **case-insensitive** (`TRUE`, `True`, `true` all work).
- Whitespace between tokens is optional (`1 and 0` and `1and0` are equivalent).
- Word-form keywords use longest-prefix matching, so they can be packed
  together without separators (`trueANDfalse` parses as `true AND false`).
- Operator precedence, highest to lowest: **grouping → NOT → AND → OR**.

## Grammar

The parser implements the following recursive-descent grammar:

```
expr     := or_expr
or_expr  := and_expr (OR  and_expr)*
and_expr := not_expr (AND not_expr)*
not_expr := NOT* atom
atom     := LITERAL | '(' expr ')'
```

## Error handling

All failures throw a subclass of
`StringLogicCheck\Exception\LogicParserException`, which itself extends
`\RuntimeException`. Catch the base class to handle every error in one place:

```php
use StringLogicCheck\Exception\LogicParserException;

try {
    $parser->logicCheck('1 xor 0');
} catch (LogicParserException $e) {
    echo $e->getMessage();   // Unknown keyword "x" at position 2.
}
```

| Exception           | Raised when                                                       |
|---------------------|-------------------------------------------------------------------|
| `LexerException`    | The input contains an unknown character or unknown keyword.       |
| `ParserException`   | The token stream is empty, unbalanced, or syntactically invalid.  |

Position information in error messages is zero-based and refers to the
original input string.

## Testing

A standalone smoke test that does not require any dependency:

```bash
php tests/smoke.php
```

The full PHPUnit suite (requires `composer install` and the `ext-dom`
extension):

```bash
composer test
```

## Project layout

```
StringLogicCheck/
├── src/
│   ├── LogicParser.php           # Public facade
│   ├── Lexer.php                 # Tokenizer
│   ├── Parser.php                # Recursive-descent evaluator
│   ├── Token.php                 # Value object
│   ├── TokenType.php             # Enum of token kinds
│   └── Exception/                # LogicParserException + subclasses
├── tests/
│   ├── LogicParserTest.php       # PHPUnit data-driven suite
│   └── smoke.php                 # Zero-dependency smoke test
├── class/LogicParser.php         # Backward-compatibility shim
├── index.php                     # Command-line demo
├── composer.json
├── phpunit.xml
└── README.md
```

## Backward compatibility

The original API entry point is preserved:

```php
require_once 'class/LogicParser.php';

$parser = new LogicParser();
$parser->logicCheck('1 AND 0');   // false
```

`class/LogicParser.php` is now a thin shim that loads the namespaced
implementation and registers the global `LogicParser` symbol via
`class_alias`. Existing callers do not need to change.

## Contributing

Bug reports and pull requests are welcome on the project tracker.
Before opening a pull request:

1. Run the smoke test: `php tests/smoke.php`.
2. If you can, run PHPUnit: `composer test`.
3. Keep the code style consistent with PSR-12.

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).
Copyright © Hassan Bagheri.
