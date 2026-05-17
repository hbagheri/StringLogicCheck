<?php

declare(strict_types=1);

namespace StringLogicCheck\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StringLogicCheck\Exception\LexerException;
use StringLogicCheck\Exception\LogicParserException;
use StringLogicCheck\Exception\ParserException;
use StringLogicCheck\LogicParser;

final class LogicParserTest extends TestCase
{
    private LogicParser $parser;

    protected function setUp(): void
    {
        $this->parser = new LogicParser();
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function expressionProvider(): array
    {
        return [
            // atoms
            'literal 1'       => ['1', true],
            'literal 0'       => ['0', false],
            'literal true'    => ['true', true],
            'literal false'   => ['false', false],
            'literal t'       => ['t', true],
            'literal f'       => ['f', false],
            'mixed case TRUE' => ['TrUe', true],

            // and / or / not
            '1 & 1'           => ['1&1', true],
            '1 & 0'           => ['1&0', false],
            '0 | 0'           => ['0|0', false],
            '0 | 1'           => ['0|1', true],
            '!1'              => ['!1', false],
            '!0'              => ['!0', true],
            '~1'              => ['~1', false],
            'not 0'           => ['not 0', true],
            'double negation' => ['!!1', true],
            'triple negation' => ['!!!1', false],
            'mixed not forms' => ['NOT~!0', true],

            // precedence: NOT > AND > OR
            '0 | 1 & 0'       => ['0|1&0', false],         // 0 | (1&0) = 0
            '1 & 0 | 1'       => ['1&0|1', true],          // (1&0)|1 = 1
            '!1 & 1'          => ['!1&1', false],          // (!1)&1 = 0
            '!(1 & 1)'        => ['!(1&1)', false],
            '!(0 | 0)'        => ['!(0|0)', true],

            // parens / grouping
            '(1)'             => ['(1)', true],
            '((1))'           => ['((1))', true],
            'nested'          => ['(1|0)&(0|1)', true],

            // word aliases, no spaces
            'README example'  => ['1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(!1&(~1&NOT0))', false],
            'demo example'    => ['1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(1&(1&NOT~!0))', true],

            // whitespace
            'with spaces'     => ['  1   and  ( 0 or 1 ) ', true],

            // regression: the original CheckOr had a typo causing infinite loop on 1|1
            '1 | 1 regression' => ['1|1', true],
        ];
    }

    #[DataProvider('expressionProvider')]
    public function testEvaluates(string $expression, bool $expected): void
    {
        self::assertSame($expected, $this->parser->logicCheck($expression));
    }

    public function testConstructorExpression(): void
    {
        $parser = new LogicParser('1 & 1');
        self::assertTrue($parser->logicCheck());
    }

    public function testArgumentOverridesConstructor(): void
    {
        $parser = new LogicParser('0');
        self::assertTrue($parser->logicCheck('1'));
    }

    public function testEmptyStringThrows(): void
    {
        $this->expectException(ParserException::class);
        $this->parser->logicCheck('');
    }

    public function testWhitespaceOnlyThrows(): void
    {
        $this->expectException(ParserException::class);
        $this->parser->logicCheck('   ');
    }

    public function testNullThrows(): void
    {
        $this->expectException(ParserException::class);
        $this->parser->logicCheck(null);
    }

    public function testUnmatchedOpenParenThrows(): void
    {
        $this->expectException(ParserException::class);
        $this->parser->logicCheck('(1');
    }

    public function testUnmatchedCloseParenThrows(): void
    {
        $this->expectException(ParserException::class);
        $this->parser->logicCheck('1)');
    }

    public function testUnknownKeywordThrows(): void
    {
        $this->expectException(LexerException::class);
        $this->parser->logicCheck('1 xor 0');
    }

    public function testUnknownCharacterThrows(): void
    {
        $this->expectException(LexerException::class);
        $this->parser->logicCheck('1 + 0');
    }

    public function testDanglingOperatorThrows(): void
    {
        $this->expectException(ParserException::class);
        $this->parser->logicCheck('1 &');
    }

    public function testAllErrorsAreLogicParserException(): void
    {
        // Callers can catch one base type for both lexer and parser failures.
        try {
            $this->parser->logicCheck('@');
            self::fail('Expected exception');
        } catch (LogicParserException $e) {
            self::assertInstanceOf(LexerException::class, $e);
        }

        try {
            $this->parser->logicCheck('1 1');
            self::fail('Expected exception');
        } catch (LogicParserException $e) {
            self::assertInstanceOf(ParserException::class, $e);
        }
    }
}
