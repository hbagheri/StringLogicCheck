<?php

declare(strict_types=1);

namespace StringLogicCheck;

/**
 * Evaluates boolean expression strings.
 *
 * Supported syntax (case-insensitive, whitespace optional):
 *   literals:  1, 0, true, false, t, f
 *   AND:       &, and
 *   OR:        |, or
 *   NOT:       !, ~, not
 *   grouping:  ( ... )
 *
 * Example:
 *     (new LogicParser())->logicCheck('1 AND (TRUE OR !0)'); // true
 */
final class LogicParser
{
    private readonly Lexer $lexer;
    private readonly Parser $parser;

    public function __construct(private ?string $string = null)
    {
        $this->lexer = new Lexer();
        $this->parser = new Parser();
    }

    /**
     * Evaluate the given expression (or the one stored at construction time).
     */
    public function logicCheck(?string $string = null): bool
    {
        $expression = $string ?? $this->string;

        if ($expression === null || trim($expression) === '') {
            throw Exception\ParserException::emptyExpression();
        }

        $tokens = $this->lexer->tokenize($expression);
        return $this->parser->evaluate($tokens);
    }
}
