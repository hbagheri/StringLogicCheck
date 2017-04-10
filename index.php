<?php

require_once 'class/logicParse.php';





$string = "1andFaLseoR(TRUEAnD0)|((((0|f)&0)|0|0)&1)|(1&(1&0))";


$stringParser = new logicParse();

$result = $stringParser->logicCheck($string);
echo "</br >test $string: \n", ($result)?"TRUE":"FALSE";

