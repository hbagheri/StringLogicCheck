# Changelog

All notable changes to this project are documented here.
The format is loosely based on [Keep a Changelog](https://keepachangelog.com/)
and this project follows [Semantic Versioning](https://semver.org/).

## [2.0.0] - 2026-05-17

### Rewritten as a proper PHP library

The 1.x code was a single procedural file (`LogicParser.php`) that walked
the input with string-replace passes. It had an infinite-loop bug on
`1|1` (typo: matched `||` instead of `|`), used `die()` on error,
exposed no public API surface beyond a global function, and shipped
without tests.

The 2.x release reorganises the project as a Composer-installable PSR-4
library with a lexer + recursive-descent parser and a typed exception
hierarchy.

### Added

- `src/Lexer.php` -- proper tokenizer.
- `src/Parser.php` -- recursive-descent parser with precedence:
  `NOT > AND > OR`.
- `src/Token.php`, `src/TokenType.php` -- token model.
- `src/Exception/LogicParserException.php` and friends -- typed
  exception hierarchy; callers can catch `LexerException` or
  `ParserException` to distinguish the failure mode.
- `tests/LogicParserTest.php` -- PHPUnit suite.
- `tests/smoke.php` -- standalone smoke test (no PHPUnit required).
- `composer.json` -- PSR-4 autoload, `php >= 8.1`.
- `phpunit.xml`.
- Polished `README.md` with TOC, requirements, install, examples,
  operator table, grammar, error-handling guide.

### Fixed

- Infinite loop on `1|1` (the original `OR` substitution used `||`
  instead of `|`, so `|1` was never consumed).
- `die()` replaced with typed exceptions.
- Tokenisation now distinguishes invalid characters (`+`, `xor`, ...)
  from invalid grammar (`(1`, `1 &`).

### Backwards compatibility

The old `require_once 'class/LogicParser.php'` entry point still works
via a `class_alias` shim, so legacy callers don't break.

## [1.x] - legacy

Initial procedural parser. Source preserved on the GitLab origin and in
the `class/` directory.
