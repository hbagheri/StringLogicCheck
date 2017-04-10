<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'class/LogicParser.php';

$string = "1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(1&(1&NOT~!0))";
$stringParser = new LogicParser();

$result = $stringParser->logicCheck($string);
echo "</br >test $string: \n", ($result)?"TRUE":"FALSE";

