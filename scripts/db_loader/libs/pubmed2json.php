<?php
include ('libs/helper.php');
/*--- PUBMED to JSON Crawler Script ---

Usage: php pubmed2json.php 12345, where 12345 is a PMID 

Notes: in helper.php file, function file_get_contents_d uses 0.5 ms delay. If you start getting "too many requests" error, you can up it to 0.75,
	or use the function file_get_contents_try which does it in 5 tries. This mostly affects the bulk option (not implemented here)

*/

$dlm="|";
$rbsAPIKey='6178583033a3e93bd315b72853de63bb2e08';
$fetchUrl="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&retmode=xml&api_key=".$rbsAPIKey."&id=";
$pubUrl="https://www.ncbi.nlm.nih.gov/pubmed/";

function xml($str,$tag) {
	return str_tween($str,'<'.$tag.'>','</'.$tag.'>');
}

function xmlOpenTag($str,$tag) {
	$res=str_tween($str,'<'.$tag,'</'.$tag.'>');
	return after('>',$res);
}

function xmlList ($str,$tag) {
	global $dlm;
	return implode($dlm,get_all_tween($str,'<'.$tag.'>','</'.$tag.'>'));
}

function xmlListOpenTag ($str,$tag) {
	global $dlm;
	$list="";
	$res=get_all_tween($str,'<'.$tag,'</'.$tag.'>');	
	foreach ($res as $r) {
		$list=$list.after('>',$r).$dlm;
	}
	return $list;
}

function xmlAuthors ($str) {
	global $dlm;
	$list="";
	$las=get_all_tween($str,'<LastName>','</LastName>');	
	$ini=get_all_tween($str,'<Initials>','</Initials>');	
	
	$i=0;
	foreach ($las as $l) {
		$list=$list.$ini[$i].'. '.$l.$dlm;
		$i++;
	}
	return $list;
}

function makeJS($name,$val) {
	$val=html_entity_decode($val);
	$val=str_replace('"','\"',$val);
	return '"'.$name.'":"'.$val.'"';
}

function isPMC($cont) {
	if (contains('<ArticleId IdType="pmc">',$cont)) {return 1;} else {return 0;}
}

function pmdataJSON($pmid) {
	global $fetchUrl,$pubUrl;
	
	$cont=file_get_contents_try($fetchUrl.$pmid);
	
	$title=xml($cont,"ArticleTitle");
	$auth=xmlAuthors($cont);
    $jour_full=xml($cont,"Title");	
	$jour=xml($cont,"ISOAbbreviation");	
	if (!$jour) {$jour=$jour_full;}
	$yr=xml($cont,'PubDate');
	$yr=xml($yr,'Year');
	$doi=str_tween($cont,'<ArticleId IdType="doi">','</ArticleId>');
	$dat=xml($cont,'Year') . "-" . xml($cont,'Month') . "-" . xml($cont,'Day');
	$essn=str_tween($cont,'<ISSN IssnType="Electronic">','</ISSN>');
	if (!$essn) {$issn=xmlOpenTag($cont,'ISSN');} else {$issn="";}
	if (!$yr) {$yr=xml($cont,'Year');}
	$abs=xml($cont,'AbstractText');
	$lan=xml($cont,'Language');
	$addr=xml($cont,'Affiliation');
	$vol=xml($cont,'Volume');
	$iss=xml($cont,'Issue');
	$pag=xml($cont,'MedlinePgn');
	$typ=after('>',xmlListOpenTag($cont,'PublicationType'));
	$url=$pubUrl.$pmid;
	$pmc=isPMC($cont);
	$chem=xmlListOpenTag($cont,'NameOfSubstance');
	$mesh=xmlListOpenTag($cont,'DescriptorName');
	
	$res='{'.makeJS('pmid',$pmid).','.
	makeJS('title',$title).','.
	makeJS('authors',$auth).','.
	makeJS('journal',$jour).','.
	makeJS('journal_full',$jour_full).','.
	makeJS('volume',$vol).','.
	makeJS('number',$iss).','.
	makeJS('year',$yr).','.
	makeJS('pages',$pag).','.
	makeJS('type',$typ).','.
	makeJS('abstract',$abs).','.
	makeJS('language',$lan).','.
	makeJS('address',$addr).','.
	makeJS('url',$url).','.
	makeJS('doi',$doi).','.
	makeJS('issn',$issn).','.
	makeJS('essn',$essn).','.
	makeJS('keywords',$chem.$mesh).','.
	makeJS('pubdate',$dat).','.
	makeJS('pdf',$pmc).','.
	/*chdi-specific*/
	makeJS('identifier','PubMed:'.$pmid).','.
	makeJS('appcodes','').','.
	makeJS('appnames','').
	'}';	
	
	$res=str_replace(PHP_EOL," ",$res);
	$res=str_replace("\n"," ",$res);
	$res=str_replace("\r"," ",$res);
	$res=str_replace("\t"," ",$res);
	$res=str_replace('|"','"',$res);
	//$res=html_entity_decode($res);
	return $res;
}

//$pmid="";

//if (count($argv)>1) { $pmid=$argv[1]; } 

//if (!$pmid) {exit("PUBMED ID is missing. Aborted".PHP_EOL);} 

//echo pmdataJSON($pmid);

?>