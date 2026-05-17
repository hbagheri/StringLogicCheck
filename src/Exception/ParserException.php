<?php

declare(strict_types=1);

namespace StringLogicCheck\Exception;

class ParserException extends LogicParserException
{
    public static function unexpectedToken(string $found, string $expected, int $position): self
    {
        return new self(sprintf('Unexpected token %s at position %d; expected %s.', $found, $position, $expected));
    }

    public static function unmatchedParenthesis(int $position): self
    {
        return new self(sprintf('Unmatched parenthesis at position %d.', $position));
    }

    public static function emptyExpression(): self
    {
        return new self('Cannot evaluate an empty expression.');
    }
}
