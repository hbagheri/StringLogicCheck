<?php

declare(strict_types=1);

namespace StringLogicCheck\Exception;

class LexerException extends LogicParserException
{
    public static function unexpectedCharacter(string $char, int $position): self
    {
        return new self(sprintf('Unexpected character %s at position %d.', var_export($char, true), $position));
    }

    public static function unknownKeyword(string $word, int $position): self
    {
        return new self(sprintf('Unknown keyword "%s" at position %d.', $word, $position));
    }
}
