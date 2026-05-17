<?php

declare(strict_types=1);

namespace StringLogicCheck;

use StringLogicCheck\Exception\ParserException;

/**
 * Recursive-descent parser/evaluator.
 *
 * Grammar (low → high precedence):
 *     expr     := or_expr
 *     or_expr  := and_expr (OR and_expr)*
 *     and_expr := not_expr (AND not_expr)*
 *     not_expr := NOT* atom
 *     atom     := LITERAL | '(' expr ')'
 */
final class Parser
{
    /** @var Token[] */
    private array $tokens;
    private int $cursor = 0;

    /**
     * @param Token[] $tokens
     */
    public function evaluate(array $tokens): bool
    {
        $this->tokens = $tokens;
        $this->cursor = 0;

        if ($this->peek()->type === TokenType::Eof) {
            throw ParserException::emptyExpression();
        }

        $result = $this->parseOr();
        $this->expect(TokenType::Eof);

        return $result;
    }

    private function parseOr(): bool
    {
        $left = $this->parseAnd();
        while ($this->peek()->type === TokenType::Or_) {
            $this->advance();
            $right = $this->parseAnd();
            $left = $left || $right;
        }
        return $left;
    }

    private function parseAnd(): bool
    {
        $left = $this->parseNot();
        while ($this->peek()->type === TokenType::And_) {
            $this->advance();
            $right = $this->parseNot();
            $left = $left && $right;
        }
        return $left;
    }

    private function parseNot(): bool
    {
        $negate = false;
        while ($this->peek()->type === TokenType::Not_) {
            $this->advance();
            $negate = !$negate;
        }
        $value = $this->parseAtom();
        return $negate ? !$value : $value;
    }

    private function parseAtom(): bool
    {
        $token = $this->peek();

        if ($token->type === TokenType::Literal) {
            $this->advance();
            return (bool) $token->value;
        }

        if ($token->type === TokenType::LParen) {
            $this->advance();
            $value = $this->parseOr();
            $this->expect(TokenType::RParen);
            return $value;
        }

        throw ParserException::unexpectedToken((string) $token, 'a literal or "("', $token->position);
    }

    private function peek(): Token
    {
        return $this->tokens[$this->cursor];
    }

    private function advance(): Token
    {
        return $this->tokens[$this->cursor++];
    }

    private function expect(TokenType $type): Token
    {
        $token = $this->peek();
        if ($token->type !== $type) {
            if ($type === TokenType::RParen) {
                throw ParserException::unmatchedParenthesis($token->position);
            }
            throw ParserException::unexpectedToken((string) $token, $type->value, $token->position);
        }
        return $this->advance();
    }
}
