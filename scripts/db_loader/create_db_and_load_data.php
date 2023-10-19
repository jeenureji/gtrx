<?php

require 'libs/db_library.php';


echo "[" . date("Y-m-d H:i:s") . "] Getting JSON template!\n";
$schemasMetaData = parseJSONFromFile($argv[1]);
$dbconn = getDBConnection($_SERVER['argv'][3], $_SERVER['argv'][4], $_SERVER['argv'][5], $_SERVER['argv'][6], $_SERVER['argv'][7]);
foreach ($schemasMetaData as $schemaMetaData) {
	echo "[" . date("Y-m-d H:i:s") . "] JSON template successfully loaded!\n";
	echo "[" . date("Y-m-d H:i:s") . "] Getting database data from " . $_SERVER['argv'][1] . "!\n";
	$worksheetsData = getDbData($schemaMetaData, $_SERVER['argv'][2]);
	echo "[" . date("Y-m-d H:i:s") . "] Database data acquired!\n";

	//var_dump($worksheetsData);

	
	if ($dbconn === false) {
		echo "[" . date("Y-m-d H:i:s") . "] Incorrect password entered!\n";
		sleep(3);
		exit;
	}
	echo "[" . date("Y-m-d H:i:s") . "] Generating Table Queries\n";
	$tableGenerationQueries =[];
	$tableGenerationQueries = generateSchema($schemaMetaData, $_SERVER['argv'][5]);
	echo "[" . date("Y-m-d H:i:s") . "] Checking queries for Errors\n";

	for ($i=0; $i<count($tableGenerationQueries); $i++){
		if (strpos($tableGenerationQueries[$i], "Error:") === 0){
			echo "[" . date("Y-m-d H:i:s") . "] The following error was generated while creating tables: " . $tableGenerationQueries[$i] . "\n";
			exit;
		}
	}
	echo "[" . date("Y-m-d H:i:s") . "] No Error Found!  Generating Tables!\n";
	for ($i=0; $i<count($tableGenerationQueries); $i++){
		echo "[" . date("Y-m-d H:i:s") . "] Running " . $tableGenerationQueries[$i] . "\n";
		$queryResult = $dbconn->query($tableGenerationQueries[$i]);
		if ($queryResult === false) {
			//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
			print_r( "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
		}
		else {
			echo "[" . date("Y-m-d H:i:s") . "] Success!\n";
		}
	}

	echo "[" . date("Y-m-d H:i:s") . "] Tables generated successfully!\n";

	echo "[" . date("Y-m-d H:i:s") . "] Loading data to non many-to-many tables\n";
	loadSchemaWithWorksheetsData($worksheetsData, $schemaMetaData, $dbconn);
	echo "[" . date("Y-m-d H:i:s") . "] Completed execution of non Many-to-many insertion queries\n";

	echo "[" . date("Y-m-d H:i:s") . "] Executing imported fields insertion queries\n";
	loadImportedFields($worksheetsData, $schemaMetaData, $dbconn);
	echo "[" . date("Y-m-d H:i:s") . "] Completed execution of imported fields insertion queries\n";

	echo "[" . date("Y-m-d H:i:s") . "] Executing Many-to-many insertion queries\n";
	loadManyToManyTableWithWorksheetsData($worksheetsData, $schemaMetaData, $dbconn);
	//var_dump($manyToManyQueries);
	echo "[" . date("Y-m-d H:i:s") . "] Completed execution of Many-to-many insertion queries\n";

	$currentGeneIds = NULL;
	

	/*if ($_SERVER['argv'][7]) {
		echo "[" . date("Y-m-d H:i:s") . "] Updating current ref_hd_genes\n";
		updateDBCurrentGeneData($dbconn, $currentGeneIds);
		echo "[" . date("Y-m-d H:i:s") . "] Completed updating current ref_hd_genes\n";

	}
	else {
		echo "[" . date("Y-m-d H:i:s") . "] Skipping updating current ref_hd_genes\n";
	}

	if ($_SERVER['argv'][8]) {
		echo "[" . date("Y-m-d H:i:s") . "] Updating latest ref_hd_genes\n";
		updateDBLatestGeneData($dbconn, $currentGeneIds);
		echo "[" . date("Y-m-d H:i:s") . "] completed updating latest ref_hd_genes\n";

	}
	else {
		echo "[" . date("Y-m-d H:i:s") . "] Skipping updating latest ref_hd_genes\n";
	}*/

	echo "[" . date("Y-m-d H:i:s") . "] DONE!!\n";
}
$dbconn = NULL;