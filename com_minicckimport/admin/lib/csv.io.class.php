<?php
/**
* CSV read & write
* version 1.2
*/

class csv{
    
    var $delimit = ";";
    var $text_qualifier = '"';
    
    function setDelimit($val){
        $this->delimit = $val;    
    }
    
    function setTextQualifier($val){
        $this->text_qualifier = $val;
    }    

 	function read($file, $srart = 0){
 	$rows=array();
 		$fp = fopen ($file,"r");
         $i = 0;
		while ($data = fgetcsv($fp, 262144, $this->delimit, $this->text_qualifier) ) {
            if($i >= ($srart-1)){
                $rows[]=$data;
            }
            $i++;
		}
		fclose ($fp);
	return $rows;
 	}

 	function readHeaders($file, $num_row){
 	$rows=array();
 		$fp = fopen ($file,"r");
         $i = 0;
		while ($data = fgetcsv($fp, 262144, $this->delimit, $this->text_qualifier) ) {

            if($i == ($num_row-1)){
                $rows = $data;
                break;
            }
                $i++;
		}
		fclose ($fp);
	return $rows;
 	}

 	function implodeCSV($data){
        
        $delimit = $this->delimit;
        
 		foreach($data as $k=>$v) {
 			$v = str_replace(array("\n", "\r", "\t"), " ", $v);
            if ($this->text_qualifier!=""){ 
 			    $v = str_replace($this->text_qualifier, $this->text_qualifier.$this->text_qualifier, $v);
            }
            if ($this->text_qualifier!=""){ 
 			    if (strpos($v, $delimit)!==false || strpos($v, $this->text_qualifier)!==false){
                    $v = $this->text_qualifier.$v.$this->text_qualifier; 
                }
            }else{
                if (strpos($v, $delimit)!==false){
                    $v = str_replace($delimit, " ", $v);
                }
            }
            
            $data[$k] = $v;
 		}

	return implode($delimit, $data);
 	}

 	function write($file, $mass2D){
 		$fp = fopen($file,"w");
 		if (!$fp) return 0;
        $countrow = count($mass2D);
 		foreach($mass2D as $k=>$v){
 			if (!is_array($v)) return 0;
            $str = $this->implodeCSV($v);
            if ($k < ($countrow-1)) $str = $str."\n";
 			fwrite($fp, $str);
 		}
		fclose($fp);
	return 1;
 	}
            
}
?>