<?php

declare(strict_types=1);

namespace StringLogicCheck;

use StringLogicCheck\Exception\LexerException;

final class Lexer
{
    /**
     * Keywords ordered by descending length so the lexer prefers longest
     * matches (e.g. "false" before "f", "and" before nothing).
     *
     * @var array<string, array{TokenType, bool|null}>
     */
    private const KEYWORDS = [
        'false' => [TokenType::Literal, false],
        'true'  => [TokenType::Literal, true],
        'and'   => [TokenType::And_, null],
        'not'   => [TokenType::Not_, null],
        'or'    => [TokenType::Or_, null],
        'f'     => [TokenType::Literal, false],
        't'     => [TokenType::Literal, true],
    ];

    /**
     * @return Token[]
     */
    public function tokenize(string $input): array
    {
        $tokens = [];
        $length = strlen($input);
        $i = 0;

        while ($i < $length) {
            $char = $input[$i];

            if (ctype_space($char)) {
                $i++;
                continue;
            }

            if (ctype_alpha($char)) {
                $token = $this->readKeyword($input, $i);
                $tokens[] = $token;
                $i += strlen($token->lexeme);
                continue;
            }

            $tokens[] = match ($char) {
                '('     => new Token(TokenType::LParen, '(', $i),
                ')'     => new Token(TokenType::RParen, ')', $i),
                '&'     => new Token(TokenType::And_, '&', $i),
                '|'     => new Token(TokenType::Or_, '|', $i),
                '!', '~' => new Token(TokenType::Not_, $char, $i),
                '0'     => new Token(TokenType::Literal, '0', $i, false),
                '1'     => new Token(TokenType::Literal, '1', $i, true),
                default => throw LexerException::unexpectedCharacter($char, $i),
            };
            $i++;
        }

        $tokens[] = new Token(TokenType::Eof, '', $length);

        return $tokens;
    }

    private function readKeyword(string $input, int $start): Token
    {
        foreach (self::KEYWORDS as $word => [$type, $value]) {
            $candidate = strtolower(substr($input, $start, strlen($word)));
            if ($candidate === $word) {
                return new Token($type, $candidate, $start, $value);
            }
        }

        throw LexerException::unknownKeyword($input[$start], $start);
    }
}
