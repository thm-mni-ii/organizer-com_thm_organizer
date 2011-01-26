<?php
/****************************************************************************
* Software: Tag Extraction Class                                            *
*               extracts the tags and corresponding text from a string      *
* Version:  1.1                                                             *
* Date:     2005/12/08                                                      *
* Author:   Bintintan Andrei  -- klodoma@ar-sd.net                          *
*                                                                           *
* License:  Free for non-commercial use                                     *
*                                                                           *
* You may use and modify this software as you wish.                         *
* PLEASE REPORT ANY BUGS TO THE AUTHOR. THANK YOU   	                    *
****************************************************************************/

/**
	Extracts the tags from a string
*/
class String_TAGS{
var $aTAGS;
var $aHREF;
var $iTagMaxElem;

	/**
    	Constructor
	*/
	function string_tags($p_tagmax = 2){
		$this->aTAGS = array();
		$this->aHREF = array();
		$this->iTagMaxElem = $p_tagmax;

	}

	/** returnes true if $p_tag is a "<open tag>"
		@param 	$p_tag - tag string
                $p_array - tag array;
        @return true/false
	*/
    function OpenTag($p_tag, $p_array){

        $aTAGS = & $this->aTAGS;
        $aHREF = & $this->aHREF;
        $maxElem = & $this->iTagMaxElem;

        if (!@eregi("^<([a-zA-Z1-9]{1,$maxElem}) *[href=]*(.*)>$", $p_tag, $reg)) return false;

        $p_tag = $reg[1];

        $sHREF = "";
        if (isset($reg[2])) $sHREF = $reg[2];
        if (strlen($sHREF)>0){

        	if (is_int(strpos("\"\'", $sHREF[0]))) $sHREF = substr($sHREF, 1, strlen($sHREF));
        	if (is_int(strpos("\"\'", $sHREF[strlen($sHREF) - 1]))) $sHREF = substr($sHREF, 0, strlen($sHREF) - 1);
		}

        if (in_array($p_tag, $aTAGS)) return false;//tag already opened

        #if (in_array("</$p_tag>", $p_array)) {
        if (in_array("</$p_tag>", $p_array)) {
        	array_push($aTAGS, $p_tag);
        	array_push($aHREF, $sHREF);
            return true;
        }
        return false;
    }

	/** returnes true if $p_tag is a "<close tag>"
		@param 	$p_tag - tag string
                $p_array - tag array;
        @return true/false
	*/
	function CloseTag($p_tag, $p_array){

	    $aTAGS = & $this->aTAGS;
	    $aHREF = & $this->aHREF;
	    $maxElem = & $this->iTagMaxElem;

	    if (!@ereg("^</([a-zA-Z1-9]{1,$maxElem})>$", $p_tag, $reg)) return false;

	    $p_tag = $reg[1];

	    if (in_array("$p_tag", $aTAGS)) {
	    	array_pop($aTAGS);
	    	array_pop($aHREF);
	    	return true;
		}
	    return false;
	}

	/** Optimieses the result of the tag
		In the result array there can be strings that are consecutive and have the same tag
		This is eliminated
		@param 	$result
		@return optimized array
	*/
	function optimize_tags($result){

		if (count($result) == 0) return $result;

		$res_result = array();
    	$current = $result[0];
    	$i = 1;

    	while ($i < count($result)){

    		//if they have the same tag then we concatenate them
			if (($current['tag'] == $result[$i]['tag']) && ($current['href'] == $result[$i]['href'])){
				$current['text'] .= $result[$i]['text'];
			}else{
				array_push($res_result, $current);
				$current = $result[$i];
			}

			$i++;
    	}

    	array_push($res_result, $current);
    	return $res_result;
    }

   	/** Parses a string and returnes the result
		@param 	$p_str - string
        @return array (
        			array (string1, tag1),
        			array (string2, tag2)
        		)
	*/
	function get_tags($p_str){

	    $aTAGS = & $this->aTAGS;
	    $aHREF = & $this->aHREF;
	    $aTAGS = array();
	    $result = array();

		$reg = preg_split('/(<.*>)/U', $p_str, -1, PREG_SPLIT_DELIM_CAPTURE);

	    $sTAG = "";
	    $sHREF = "";

        while (list($key, $val) = each($reg)) {
	    	if ($val == "") continue;

	        if ($this->OpenTag($val,$reg)){
	            $sTAG = (($temp = end($aTAGS)) != NULL) ? $temp : "";
	            $sHREF = (($temp = end($aHREF)) != NULL) ? $temp : "";
	        }elseif($this->CloseTag($val, $reg)){
	            $sTAG = (($temp = end($aTAGS)) != NULL) ? $temp : "";
	            $sHREF = (($temp = end($aHREF)) != NULL) ? $temp : "";
	        }else {
	        	if ($val != "")
	        		array_push($result, array('text'=>$val, 'tag'=>$sTAG, 'href'=>$sHREF));
	        }
	    }//while

	    return $this->optimize_tags($result);
	}

}//class String_TAGS{

?>