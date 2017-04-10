<?php
/**
 * This class compares a logic given string to logic value of that 
 *
 * @author Hans
 * @copyright (c) 2017, Hans Burgman
 * @version 1.0.0
 */
class LogicParser{
    /**
     *
     * @var string saved string on work 
     */
    private $string;
    /**
    *
    * @var string saved the base string 
    */
    private $base_string;
    /**
     * OPEN prantes
     */
    const OPENP  = "(";
    /**
     * Close prantes
     */
    const CLOSEP = ")";
    /**
     * AND logic
     */
    const ANDOP	 = "&";
    /**
     * OR logic
     */
    const OROP	 = "|"; 
    /**
     * NOT logic
     */ 
	const NOTOP = "!";
//-------------------------------------------------------------
	/**
         * 
         * @param string $string if set the working string will set
         */ 
	public function __construct($string = NULL){
		$this->setString($string);
	}
	
	//-------------------------------------------------------------
	/**
         * 
         * Convert string to a good acceptable string replase all true fand falses 
         *  to 1 & 0 and also chages all AND , OR s to & , | then it can be uses in
         *  other methods. Will exit in case of any exceptions.
         * @param string $string string to compare
         * @return string compared string
         */
	private function stringCompaire($string){
		
		$op = 0;
		$string = strtolower($string);
		for ($i = 0 ; $i < strlen($string) ; $i++){
			if($string[$i] == "("){ $op++;} 
			if($string[$i] == ")"){ $op--; if ($op < 0) die("Prantess mismatch on ".__FILE__." ".__LINE__);} 
		} 

		if($op > 0){ die("Prantess mismatch on ".__FILE__." ".__LINE__);}
                $replaceArray = array("not"=>"!","~"=>"!","true"=>1,"false"=>0,"t"=>1,"f"=>0,"or"=>"|","and"=>"&",);
                foreach($replaceArray as $old=>$new){
                    $string = str_replace($old,$new,$string);
                }
		preg_match("/[a-z]+/",$string,$rungChars,PREG_OFFSET_CAPTURE);
		if(count($rungChars)){
				$error =  "Un-acceptable chars on string : ";
				foreach($rungChars as $str){
					$error .= "\"".$str[0]."\" on pos:".$str[1]." , ";
				}
				die($error.__FILE__." ".__LINE__);
		}
		return $string;
	}
	//-------------------------------------------------------------
	
        /**
         * 
         * sets given string as class parameters
         * @param string $string
         */
	private function setString($string=NULL){
		if(strlen($string) > 0 ){
			$this->base_string = $string;
			$this->string = $this->stringCompaire($string);
		}
	}
	
	//-------------------------------------------------------------
	/**
         * 
         * @return string compared string to work on. 
         */
	private function getString(){
		return $this->string;
	}
	
	//-------------------------------------------------------------
	/**
     * get string from input and call strPArs method to parse it and return 
     * its logical value.
     * 
     * @param string $string
     * @return boolean
     */
	public function logicCheck($string=NULL){
		$this->setString($string);
		$result = $this->strPars($this->getString());
		return (intval($result)==1);
	}
	//-------------------------------------------------------------
	/**
         * Gets a part of string or a complex one to compare it logicaly. in case of 
         * complex string this method will devide it to shortest string contains only  
         * deepest prateses that are not containes ane prantess inside. Then will
         * start from last prantes and replace its logic value with it. untill no more
         * prantess is inside of string. then compare result as a normal string  
         * @param string $str a string to check logical
         * @return boolean
         */
	private function strPars($str){
		$deepPrantesesStrings = $this->getDeepestPrantes($str);
		$subCount = count($deepPrantesesStrings[0]);
		if($subCount > 0){
			for($i=$subCount-1 ; $i>=0 ; $i--){
				$substr = $deepPrantesesStrings[0][$i][0];
				$strlen = strlen($substr);
				$newSubstr = $this->clearStringParse(substr($substr,1,$strlen-2));
				$str=substr_replace($str,$newSubstr,$deepPrantesesStrings[0][$i][1],$strlen);
			}
			$str = $this->strPars($str);
		}
		$str = $this->clearStringParse($str);
		return $str;
	}
	
	//-------------------------------------------------------------
	
	/**
	 * 
	 * @param string $patern the patern that should search
	 * @param The string that should be serch inside
	 * @return array parent hapended as an array contains matches string and 
	 * 		their pos
	 */ 
	
	private function getArrayWithPatern($patern,$string){
		preg_match_all($patern, $string, $matches, PREG_OFFSET_CAPTURE);
		return $matches;
	}
	//-------------------------------------------------------------
	
	/**
	 * Retuns array of deepest prantesses without any other prantesses inside
	 * @param string $str string to search in
	 * @return array parent hapended as an array contains matches string and 
	 */
	private function getDeepestPrantes($str){
		$patern = "/\\([10&|!]+\\)/";
		return $this->getArrayWithPatern($patern,$str);
	}
	//-------------------------------------------------------------
	
	/**
	 * Checks a clear string that is not contains any pranteses
	 * $param string $str String to parse
	 * $return string a single charecter string that is 0 or 1 the result of
	 * logical cheks of string
	 */ 
	private function clearStringParse($str){
		$str = $this->checkNot($str);
		
		$str = $this->checkAnd($str);

		$str = $this->checkOr($str);

		return $str;
	}
	//-------------------------------------------------------------
	/**
	 * Check all NOT operators and replace values 
	 * $param string $str string to check for NOT operator
	 * @return string new string whiout NOT operator.
	 */ 
	
	private function checkNot($str){
		$notStrs = array("!!"=>"","!1"=>"0","!0"=>"1");
		while(strpos($str,"!")!== FALSE){
			foreach($notStrs as $oldVal=>$newVal){
				$str=str_replace($oldVal,$newVal,$str);
			}
		}
		return $str;
	}
	
	//-------------------------------------------------------------
	/**
         * Takes an string with and operators and replace all ANDS with thire value
         * @param string $string
         * @return string a new string without any AND operator
         */
	private function CheckAnd($string){
		$andRes = array("0&0"=>"0","0&1"=>"0","1&0"=>"0","1&1"=>"1");
		while(strpos($string,"&")){
			foreach($andRes as $oldVal=>$newVal){
				$string = str_replace($oldVal,$newVal,$string);
			}
		}
		return $string;
	}
	
	//-------------------------------------------------------------
	/**
         * Takes an string with or operators and replace all ORs with thire value
         * @param string $string
         * @return string a new string without any OR operator
         */
	private function CheckOr($string){
		$orRes = array("0|0"=>"0","0|1"=>"1","1|0"=>"1","1&1"=>"1");
		while(strpos($string,"|")){
			foreach($orRes as $oldVal=>$newVal){
				$string = str_replace($oldVal,$newVal,$string);
			}
		}
		return $string;
	}
}
