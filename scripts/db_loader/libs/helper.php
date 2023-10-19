<?php
function contains($th,$inthat) {
        return !is_bool(strpos($inthat, $th));
}

function file_get_contents_d($file) {
    sleep(0.5);
    return file_get_contents($file);
}

function file_get_contents_try($file) {
	$too='Too Many Requests';
	$too2='Bad Gateway';
    $allowed_attempts = 5;
	$t=0;
	while (++$t) {
		sleep(0.5);
		$res=@file_get_contents($file);
        //var_dump($res);
		if ($res !== false OR $t>$allowed_attempts) {break 1;} 
        $remaining_attempts_left = $allowed_attempts - $t;
        print_r(  "[" . date("Y-m-d H:i:s") . "] There was an error getting data, trying again....." . $remaining_attempts_left . " attempts remaining\n");
	}	
	return $res;
}

function str_tween($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function write1($line, $fname) {
    file_put_contents($fname, $line.PHP_EOL , FILE_APPEND | LOCK_EX);
}

function beginsWith($str,$sub) {
     return (substr($str, 0, strlen($sub))===$sub);
}

function after ($th, $inthat){
    if (!is_bool(strpos($inthat, $th))) {
		return substr($inthat, strpos($inthat,$th)+strlen($th));
	} else {return "";}
}

function before ($th, $inthat) {
    return substr($inthat, 0, strpos($inthat, $th));
}

function platform ($id) {
    global $platforms;
    $ini = strpos($id, 'GPL');
    if ($ini == 0) {$id='GPL'.$id;}
    $res="";
    foreach ($platforms as $plat) {
        if (before("|", $plat)==$id){$res=after("|",$plat);}
    }
    return trim($id.'::'.$res);
}

function get_all_tween($str, $startDelimiter, $endDelimiter) {
	$contents = array();
	$startDelimiterLength = strlen($startDelimiter);
	$endDelimiterLength = strlen($endDelimiter);
	$startFrom = $contentStart = $contentEnd = 0;
	while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
		$contentStart += $startDelimiterLength;
		$contentEnd = strpos($str, $endDelimiter, $contentStart);
		if (false === $contentEnd) {
			break;
		}
		$contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
		$startFrom = $contentEnd + $endDelimiterLength;
	}

	return $contents;
}

function str_replace_once($s,$r,$l) {
    return preg_replace('/'.$s.'/', $r, $l, 1);
}

function sxlop($s1,$s2s,$scol) {
    global $dlm;

    if (!trim($s1)) {return implode($dlm,$s2s);} 
	else {
        $s1s=explode($dlm,$s1);
        for ($x=count($s2s);$x<count($s1s);$x++) {$s2s[]="";}
        
		for ($x=0;$x<count($s1s);$x++) {
            if ($x!==$scol) {
				if ($s1s[$x]!==$s2s[$x]) {
					if ( (!contains(";".$s2s[$x], $s1s[$x]))&& (!contains($s2s[$x].";", $s1s[$x])) && (trim($s2s[$x]))) {
                       $s1s[$x]=$s1s[$x].";".$s2s[$x];
                    }
                }
            }
        }
    }
    $res=[];
    $y=0;
    foreach ($s1s as $s)  {
        $res=[];
        $rs=explode(';',$s);
        foreach ($rs as $r) {
			if (trim($r)) {
                if (!in_array(trim($r),$res)) {$res[]=trim($r);}
            }
        }
        $s1s[$y]=implode('; ',$res);
        $y++;
    }
    return implode($dlm,$s1s);
}

?>
