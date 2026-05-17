<?php

declare(strict_types=1);

namespace StringLogicCheck;

final class Token
{
    public function __construct(
        public readonly TokenType $type,
        public readonly string $lexeme,
        public readonly int $position,
        public readonly ?bool $value = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->type->value . '(' . $this->lexeme . ')';
    }
}
