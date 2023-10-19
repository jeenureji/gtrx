<?php
	require 'data_transform_library.php';

	$redCapDataDump = getDbTableDataFromCSV($argv[1], ',');
	$gtrxMetaDataFieldsDump = getDbTableDataFromCSV($argv[2]);
	$omimOrphaToHPODump = getDbTableDataFromCSV($argv[3]);
	/*$omimToGhrDump = getDbTableDataFromCSV($argv[2]);
	$omimToGardMondoDump = getDbTableDataFromCSV($argv[3]);
	$gardIndexDump = getDbTableDataFromCSV($argv[4]);
	$orphanetToOmimDump = getDbTableDataFromCSV($argv[5]);
	$omimOrphaToHPODump = getDbTableDataFromCSV($argv[6]);*/
	
	$csvString = generateETLCSVFromExtract($redCapDataDump, $gtrxMetaDataFieldsDump, $omimOrphaToHPODump);
	file_put_contents($argv[4], $csvString);
	$currentDateStr = date("Y-m-d");
	file_put_contents($argv[5] . "/RadysGtrxData.properties", "data_version = v" . $currentDateStr);

	print_r("DONE\n");