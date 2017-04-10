<?php
/**
 * This class compares a logic given string to logic value of that 
 *
 * @author Hans
 * @copyright (c) 2017, Hans Burgman
 * @version 1.0.0
 */
class logicParse{
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
                $replaceArray = array("true"=>1,"false"=>0,"t"=>1,"f"=>0,"or"=>"|","and"=>"&");
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
		return $this->strPars($this->getString());
	}
	
	//-------------------------------------------------------------
	/**
         * Gets a part of string or a complex one to compare it logicaly. in case of 
         * complex string this method will devide it to shortest string contains only 
         * 2 boolean and a logic operator. it will takes from lastest and deepest open close
         * prates.  
         * @param string $str a string to check logical
         * @return boolean
         */
	private function strPars($str){
		$lastOpPos = strrpos($str,self::OPENP);
		if($lastOpPos !== FALSE){	
			$firstClPos = strpos($str,self::CLOSEP,$lastOpPos);
			$firstClPos = ($lastOpPos ==FALSE)?strlen($str):$firstClPos;
			$prantesLength = $firstClPos - $lastOpPos -1;
			$subSt = substr($str,$lastOpPos+1,$prantesLength);
			$newVal = $this->Check_and_or($subSt);
			$newStr = substr($str,0,$lastOpPos).$newVal.substr($str,$firstClPos+1);
			return $this->strPars($newStr);
		}
		return $this->Check_and_or($str);
	}
	
	//-------------------------------------------------------------
	/**
         * 
         * This method will calls by strPars with a short string with tow boolean value and
         *  only one operator. between them;
         * @param string $str  
         * @return boolean
         */
	private function Check_and_or($str){
		$lastAndPos = strpos($str,self::ANDOP);
		if($lastAndPos !== FALSE){
			$subStr = substr($str,$lastAndPos-1,strlen(self::ANDOP)+2);
			$newVal = $this->CheckAnd($subStr);
			$newStr = substr($str,0,$lastAndPos-1).intval($newVal).substr($str,$lastAndPos+strlen(self::ANDOP)+1);
			return $this->Check_and_or($newStr);
		}
		$lastOrPos = strpos($str,self::OROP);
		if($lastOrPos !== FALSE){
			$subStr = substr($str,$lastOrPos-1,strlen(self::OROP)+2);
			$newVal = $this->CheckOr($subStr);
			$newStr = substr($str,0,$lastOrPos-1).intval($newVal).substr($str,$lastOrPos+strlen(self::OROP)+1);
			return $this->Check_and_or($newStr);
		}
		return $str;
	}
	
	
	//-------------------------------------------------------------
	/**
         * Takes tow parameters first is string second is a logical operator.
         * Then will explode it in an array contaos of boolean values.
         * @param string $string
         * @param string $opr
         * @return array
         */
	private function getBooleanArray($string,$opr){
		$array = explode($opr,$string);
		if(count($array)>2){
			die("too long array on ".__FILE__." ".__LINE__);
		}
		foreach($array as $item){
			$result[] = boolval($item); 
		}
		return $result;
	}
	
	//-------------------------------------------------------------
	/**
         * Takes an string with and operator and check it if is true or false.
         * @param string $string
         * @return boolean
         */
	private function CheckAnd($string){	
		$array = $this->getBooleanArray($string,self::ANDOP);
		if(count($array)==1) return $array[0];
		return ($array[0]) && ($array[1]);
	}
	
	//-------------------------------------------------------------
	/**
         * Takes an string with or operator and check it if is true or false.
         * @param string $string
         * @return boolean
         */
	private function CheckOr($string){
		
		$array = $this->getBooleanArray($string,self::OROP);
		return ($array[0] || $array[1]);
	}
}
