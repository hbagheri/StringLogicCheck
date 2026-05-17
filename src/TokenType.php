<?php

declare(strict_types=1);

namespace StringLogicCheck;

enum TokenType: string
{
    case Literal = 'LITERAL';
    case And_    = 'AND';
    case Or_     = 'OR';
    case Not_    = 'NOT';
    case LParen  = 'LPAREN';
    case RParen  = 'RPAREN';
    case Eof     = 'EOF';
}
