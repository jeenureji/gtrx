<?php
	require 'libs/pubmed2json.php';
	
	/*

	Database Connection Library

	Author: Kabenla E. Armah

	Copyright: Rancho BioSciences

	Requirements: GSheets API (see composer.json)

	Parameters: none

	*/
	$generatedTables = [];
	$loadedTables = [];

	/*
		Function: getDBConnections
		Purpose: Gets a database connection given a host, port and credentials
		Args:
			$host: A string containing the URL to the database server
			$port: An integer containing the port the database is listening on
			$dbName: A string containing the name of the database
			$user: A string containeing the username for the connection
		Returns: A Database connection object
		Notes: The password for the database is prompted
	*/
	function getDBConnection($host, $port, $dbName, $user, $password) {
		$dsn = "pgsql:host=" . $host . ";dbname=" . $dbName . ";port=" . $port;
	    print 'Please enter the database password for ' . $user . '@' . $host . ':' . $port . '/' . $dbName . ': ';
	    //$dbPassword = trim(fgets(STDIN));
	    $dbPassword = $password;
	    return new PDO($dsn, $user, $dbPassword);
	}

	/*
		Function: parseJSONFromFile
		Purpose: Opens a JSON file into memory.  Used to get table description data
		Args:
			$pathToJSONFile: A string containing the full path to the json file
		Returns: An array representing the JSON data in the file
	*/
	function parseJSONFromFile($pathToJSONFile){
		$JSONStringFromFile = file_get_contents($pathToJSONFile);
		//var_dump($JSONStringFromFile);
		$JSONObject = json_decode($JSONStringFromFile, true);
		//var_dump($JSONObject);
		return $JSONObject;

	}

	function verifyPdfFromS3($host, $port, $dbName, $user, $publicationTable, $primaryKeyColumn, $sourceColumn, $sourceIdColumn, $pdfColumn, $urlColumn, $s3Region, $s3Bucket, $s3PDFBaseDir) {
		$dbconn = getDBConnection($host, $port, $dbName, $user);
		if ($dbconn === false) {
			echo "[" . date("Y-m-d H:i:s") . "] Incorrect password entered!\n";
			sleep(3);
			exit;
		}

		$pdfStatusQuery = "SELECT pubtab." . $primaryKeyColumn . ", er.filename AS efficacy_report_filename, " .$sourceColumn . ", " .  $sourceIdColumn . ", " . $pdfColumn . "," . $urlColumn . " FROM " . $publicationTable . " pubtab LEFT JOIN efficacy_reports er ON pubtab." . $primaryKeyColumn . " = er.source_identifier;";
		
		print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $pdfStatusQuery . "\n");
		$queryStatement = $dbconn->query($pdfStatusQuery);

		if ($queryStatement === false) {
			//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
			print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
		}

		foreach ($queryStatement as $row) {
			switch (strtolower($row[$sourceColumn])) {
				case 'pubmed':
					print_r( "[" . date("Y-m-d H:i:s") . "] Checking for file " . $s3PDFBaseDir . "/pubmed/" . $row[$sourceIdColumn] . ".pdf in S3\n");
					if (file_exists_on_s3($s3Bucket, $s3Region, $s3PDFBaseDir . "/pubmed/" . $row[$sourceIdColumn] . ".pdf")) {
						$updateQuery = "UPDATE " . $publicationTable . " SET " . $pdfColumn . " = 1 WHERE " . $primaryKeyColumn . " = '" . $row[$primaryKeyColumn] . "';";
						print_r( "[" . date("Y-m-d H:i:s") .'] Running: ' . $updateQuery . "\n");
					}
					else {
						$updateQuery = "UPDATE " . $publicationTable . " SET " . $pdfColumn . " = 0 WHERE " . $primaryKeyColumn . " = '" . $row[$primaryKeyColumn] . "';";
						print_r("[" . date("Y-m-d H:i:s") . '] Running ' . $updateQuery . "\n");
					}
					break;
				case 'chdi':
					print_r( "[" . date("Y-m-d H:i:s") . "] Checking for file " . $s3PDFBaseDir . "/chdi/" . $row['efficacy_report_filename'] . ".pdf in S3\n");
					if (file_exists_on_s3($s3Bucket, $s3Region, $s3PDFBaseDir . "/chdi/" . $row['efficacy_report_filename'])) {
						$updateQuery = "UPDATE " . $publicationTable . " SET " . $pdfColumn . " = 1 WHERE " . $primaryKeyColumn . " = '" . $row[$primaryKeyColumn] . "';";
						print_r("[" . date("Y-m-d H:i:s") . '] Running: ' . $updateQuery . "\n");
					}
					else {
						$updateQuery = "UPDATE " . $publicationTable . " SET " . $pdfColumn . " = 0 WHERE " . $primaryKeyColumn . " = '" . $row[$primaryKeyColumn] . "';";
						print_r("[" . date("Y-m-d H:i:s") . '] Running: ' . $updateQuery . "\n");
					}
					break;
				case 'biorxiv':
					$source_array = explode("/", $row[$urlColumn]);
					print_r( "[" . date("Y-m-d H:i:s") . "] Checking for file " . $s3PDFBaseDir . "/biorxiv/" . $source_array[count($source_array)-1] . ".full.pdf in S3\n");
					if (file_exists_on_s3($s3Bucket, $s3Region, $s3PDFBaseDir . "/biorxiv/" . $source_array[count($source_array)-1] . ".full.pdf")) {
						$updateQuery = "UPDATE " . $publicationTable . " SET " . $pdfColumn . " = 1 WHERE " . $primaryKeyColumn . " = '" . $row[$primaryKeyColumn] . "';";
						print_r("[" . date("Y-m-d H:i:s") . '] Running: ' . $updateQuery . "\n");
					}
					else {
						$updateQuery = "UPDATE " . $publicationTable . " SET " . $pdfColumn . " = 0 WHERE " . $primaryKeyColumn . " = '" . $row[$primaryKeyColumn] . "';";
						print_r("[" . date("Y-m-d H:i:s") . '] Running: ' . $updateQuery . "\n");
					}
					break;
			}
			print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $updateQuery . "\n");
			$queryStatement = $dbconn->query($updateQuery);
			if ($queryStatement === false) {
				//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
				print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
				
			}
		}
		
		$dbconn = NULL;
	}

	function getDbData($schemaMetaData, $pathToCSVFileDirectory){
		$dbTables = $schemaMetaData['tables'];
		//var_dump($schemaMetaData);
		$tablesData = [];
		foreach ($dbTables as $dbTable) {
			$tablesData[$dbTable['table_name']] = getDbTableDataFromCSV($pathToCSVFileDirectory . '/' . $dbTable['table_name'] . '.csv');
		}
		return $tablesData;
	}


	function getDbTableDataFromCSV($pathToCSVFile){
		//var_dump($pathToCSVFile);
		$result = [];
		$csvFileHandle = fopen($pathToCSVFile, 'r');
		while ($row = fgetcsv($csvFileHandle, 0, "\t")) {
			$result[] = $row;
		}
		
		return $result;

	}

	function selectDBColumnDataByKeys ($dbconn, $schema, $table, $column, $keyField, $keyValues) {
		$result = [];
		foreach ($keyValues as $keyValue){
			$result = array_merge($result, selectDBColumnDataByKey ($dbconn, $schema, $table, $column, $keyField, $keyValue));
		}
		return $result;
	}

	function selectDBColumnDataByKey ($dbconn, $schema, $table, $column, $keyField, $keyValue) {
		print_r("[" . date("Y-m-d H:i:s") . "] Running select ". $column . " from \"" . $schema . "\"." . $table . " where " . $keyField . " = " . $keyValue . "\n");
		$queryStatement = $dbconn->query("select " . $column . " from \"" . $schema . "\"."  . $table . " where " . $keyField . " = " . $keyValue);
		if ($queryStatement === false) {
			print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
			return null;
		}
		$result = [];
			//print_r("select " . $queryStatement);
		foreach ($queryStatement as $row) {
			$result[] = $row[$column];
		}

		return $result;
		

	}

	/*
		Function: findTableMetaDataByTableName
		Purpose: Searches an Array of Tables for a table with a specified tableName
		Args:
			$tableName: A string containing the name of the table
			$tablesMetaData: An Array of Associative Arrays containing information for all the tables
		Returns: An Associative Array with the information about the table or NULL if the table is not found
	*/
	function findTableMetaDataByTableName($tableName, $tablesMetaData){
		foreach ($tablesMetaData as $tableMetaData){
			if($tableMetaData["table_name"]==$tableName){
				return $tableMetaData;
			}
		}
		return null;
	}

	/*
		Function: findWorksheetDataByWorksheetName
		Purpose: Searches all work sheets from gsheets for a particular worksheet with a given name
		Args:
			$worksheetName: Then name of the worksheet we want
			$workSheetsData: An Associative Array containing the worksheetData
		Returns: An Array of Arrays with the worksheet data
	*/
	function findWorksheetDataByWorksheetName($worksheetName, $workSheetsData) {
		foreach ($workSheetsData as $worksheetTitle => $workSheetData) {
			if ($worksheetName == $worksheetTitle) {
				return $workSheetData;
			}
		}
		return NULL;
	}

	/*
		Function: generateSchema
		Purpose: Generates database queries for generating a schema with all associated tables
		Args:
			$schemaMetaData: An Associative Array with schema and all table data (This is a representation of the gsheets_db_tables.json file in PHP)
		Returns: An Array with all the queries for generating the schema and associated tables	
	*/
	function generateSchema($schemaMetaData){
		$tableGenerationQueries = [];
		//$schema = $schemaMetaData['schema'];
		$schema = $schemaMetaData['schema'];
		$createSchemaQuery = 'CREATE SCHEMA IF NOT EXISTS "' . $schema . '"';
		$tableGenerationQueries[] = $createSchemaQuery;	

		$tablesMetaData = $schemaMetaData['tables'];
		foreach ($tablesMetaData as $tableMetaData){
			$tableGenerationQueries = array_merge($tableGenerationQueries, generateTable($schema, $tableMetaData, $tablesMetaData));
		}

		return $tableGenerationQueries;
	}

	/*
		Function: generateTable
		Purpose: Generates database queries for generating a table
		Args:
			$schema: A string containing the database schema name
			$tableMetaData: An Associative Array with the meta data for the table we wish to create (An entry from the tables attribute in gsheets_db_tables.json)
			$tablesMetaData: An Array of Associative arrays containing all tables in the database (need for if we have to create relationship to other tables)
		Returns: An Array with all the queries for generating the table			
	*/
	function generateTable($schema, $tableMetaData, $tablesMetaData){
		$tableGenerationQueries = [];
		if (!in_array($tableMetaData["table_name"], $GLOBALS['generatedTables'])) {
			$GLOBALS['generatedTables'][]=$tableMetaData["table_name"];
			$tableName = $tableMetaData["table_name"];
			$tablePrimaryKeyFieldName = $tableMetaData["primary_key_field_name"];
			$tableFieldsData = $tableMetaData["table_fields"];
			//$tableGenerationQueries = array_merge($tableGenerationQueries, generateFieldDetails($schema, $tableName, $tablePrimaryKeyFieldName, $tableFieldsData, $tablesMetaData));
			if ($tablePrimaryKeyFieldName == "NULL") {
				//echo "null route";
				$tableGenerationQueries = array_merge($tableGenerationQueries, generateFieldDetails($schema, $tableName, NULL, $tableFieldsData, $tablesMetaData));
			}
			else {
				$tableGenerationQueries = array_merge($tableGenerationQueries, generateFieldDetails($schema, $tableName, $tablePrimaryKeyFieldName, $tableFieldsData, $tablesMetaData));
			}
			
		}
		return $tableGenerationQueries;
	}

	/*
		Function: loadSchemaWithWorksheetsData
		Purpose: Loads Data into a database schema
		Args:
			$worksheetsData: An Associative Array containing all the worksheet data to be loaded
			$schemaMetaData: An Associative Array containing the schema of the entire database we wish to load with data
		Returns: An Array with all the queries for loading data into the databas schema
	*/
	function loadSchemaWithWorksheetsData($worksheetsData, $schemaMetaData, $dbconn) {
		//$insertQueries = [];
		$schema = $schemaMetaData['schema'];
		$tablesMetaData = $schemaMetaData['tables'];
		foreach($tablesMetaData as $tableMetaData) {
			loadTableWithWorksheetsData($worksheetsData, $schema, $tableMetaData, $tablesMetaData, $dbconn);
		}
	}

	/*
		Function: getWorksheetDatumAtColumnHeaderAndRow
		Purpose: Gets a data item in the worksheet
		Args:
			$worksheetsData: An Array of Arrays representing the worksheet data to be loaded.  Header data is in row 0
			$columnHeader: A string containing the Header (column) for the datum we want
			$row: An integer containing the row number of the datum we want
		Returns: An string with the data at the column and row
	*/
	function getWorksheetDatumAtColumnHeaderAndRow ($worksheetData, $columnHeader, $row) {
		for ($i=0; $i<count($worksheetData[0]); $i++){
			if (strtolower($worksheetData[0][$i]) == strtolower($columnHeader)) {
				return $worksheetData[$row][$i];
			}
		}
		return NULL;
	}

	/*
		Function: endsWith
		Purpose: Checks to see if a string ends with a substring
		Args:
			$haystack: The string to search
			$needle: The substring
		Returns: Returns True if substring is at the end of the string and False if otherwise
	*/
	function endsWith($haystack, $needle)
	{
	    $length = strlen($needle);
	    if ($length == 0) {
	        return true;
	    }

	    return (substr($haystack, -$length) === $needle);
	}

	function updateReferenceTableWithForeignKeyFromTable ($tableData, $foreignKeyField, $referenceTableSchema, $referenceTableName, $primaryKeyField, $tableDataColumn, $tableDataData,  $dbconn){
		for ($i=1; $i<count($tableData); $i++) {
			$primaryKeyValue = getWorksheetDatumAtColumnHeaderAndRow($tableData, $foreignKeyField, $i);
			if (trim($primaryKeyValue) !== '') {

				print_r(  "[" . date("Y-m-d H:i:s") . "] Checking if " . $primaryKeyField . " has value " . $primaryKeyValue . " in any rows of table '" .  $referenceTableSchema . "." . $referenceTableName . "'\n");
				print_r(  "[" . date("Y-m-d H:i:s") . "] Running: select count(*) from \"" . $referenceTableSchema . "\"." . $referenceTableName . " where " . $primaryKeyField . " = " . $primaryKeyValue . "\n");
				$queryStatement = $dbconn->query("select count(*) from \"" . $referenceTableSchema . "\"." . $referenceTableName . " where " . $primaryKeyField . " = " . $primaryKeyValue);
				if ($queryStatement === false) {
					//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
					print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
					continue;
				}

				$numberOfEntriesFound = $queryStatement->fetchColumn();
				if ($numberOfEntriesFound != 0) {
					print_r(  "[" . date("Y-m-d H:i:s") . "] Entry " . $primaryKeyField . " = " . $primaryKeyValue . " already exists in '". $referenceTableName . "' table......Adding table data!\n");
					if ($tableDataColumn!=null) {
						$tableDataEntry = null;
						print_r(  "[" . date("Y-m-d H:i:s") . "] Getting current table data\n");
						print_r(  "[" . date("Y-m-d H:i:s") . "] Running: select " . $tableDataColumn . " from " . $referenceTableSchema . "." . $referenceTableName . " where " . $primaryKeyField . " = " . str_replace("\\", "\\\\", $primaryKeyValue) . "\n");
						$queryStatement = $dbconn->query("select " . $tableDataColumn . " from " . $referenceTableSchema . "." . $referenceTableName . " where " . $primaryKeyField . " = " . str_replace("\\", "\\\\", $primaryKeyValue));
						foreach ($queryStatement as $row) {
							$tableDataEntry = $row[$tableDataColumn];
						}
						$tableDataEntries = explode ("|", $tableDataEntry);
						$tableDataEntries[] = $tableDataData;
						$tableDataEntriesNoDuplicates = array_unique($tableDataEntries);

						$updateQuery = "update " . $referenceTableSchema . "." . $referenceTableName . " set " . $tableDataColumn . " = '" . str_replace("\\", "\\\\", str_replace("'","''", implode("|", $tableDataEntriesNoDuplicates))) . "' where "  . $primaryKeyField . " = " .  str_replace("\\", "\\\\", $primaryKeyValue);
						print_r(  "[" . date("Y-m-d H:i:s") . "] Inserting updated table data\n");
						print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $updateQuery  . "\n");
						$queryStatement = $dbconn->query($updateQuery);
					}
				}
				else {
					if ($tableDataColumn!=null) {
						$insertQuery = "insert into  " . $referenceTableSchema . "." . $referenceTableName . "(" . $primaryKeyField . ", " . $tableDataColumn . ") values (" . $primaryKeyValue . ",'" .  str_replace("\\", "\\\\", str_replace("'","''", $tableDataData)) . "')" ;
					}
					else {
						$insertQuery = "insert into  " . $referenceTableSchema . "." . $referenceTableName . "(" . $primaryKeyField . ") values (" . $primaryKeyValue . ")" ;
					}
					print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $insertQuery . "\n");
					$queryStatement = $dbconn->query($insertQuery);
					if ($queryStatement === false) {
						//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
						print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
						continue;
					}
				}
			}
		}
	}

	function update_external_publications_foreign_keys ($table_schema, $table_name, $tableData, $foreignKeyField, $dbconn) {
		for ($i=1; $i<count($tableData); $i++) {
			$sourceIdentifiersData = getWorksheetDatumAtColumnHeaderAndRow($tableData, $foreignKeyField, $i);
			$sourceIdentifiersDataArray = explode("|", $sourceIdentifiersData);
			foreach ($sourceIdentifiersDataArray as $sourceIdentifier) {
				$sourceIdentifierArray = explode (":", $sourceIdentifier);

		
				
				$externalDataSource = $sourceIdentifierArray[0];
				$sourceid = '';
				for ($j=1; $j<count($sourceIdentifierArray); $j++){
					if ($sourceIdentifierArray[$j] != NULL) {
						$sourceid = $sourceid . $sourceIdentifierArray[$j];
						if ($j<count($sourceIdentifierArray)-1) {
							$sourceid = $sourceid . ':';
						}
					}	
				}

				if (endsWith($sourceIdentifiersData, ':')){
					$sourceid = $sourceid . ':';
				}

				if (trim($sourceIdentifier) !== '') {
					//var_dump("identifer is " . $sourceIdentifier);
					print_r(  "[" . date("Y-m-d H:i:s") . "] Checking if " . $table_schema . "." . $sourceIdentifier . " already exists in '" . $table_name . "' table\n");
					print_r(  "[" . date("Y-m-d H:i:s") . "] Running: select count(*) from  " . $table_schema . "."  . $table_name . " where source_identifier = '" . $sourceIdentifier . "'\n");
					$queryStatement = $dbconn->query("select count(*) from " . $table_schema . "."  . $table_name . " where source_identifier = '" . $sourceIdentifier . "'");
					if ($queryStatement === false) {
						//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
						print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
						continue;
					}
					
					$publicationSourceIdentifiersLoaded = $queryStatement->fetchColumn();
					if ($publicationSourceIdentifiersLoaded != 0) {
						print_r(  "[" . date("Y-m-d H:i:s") . "] Entry " . $sourceIdentifier . " already exists in '" . $table_name . "' table......Moving on!\n");
					}
					else {
						switch (strtolower($externalDataSource)) {
							case 'biorxiv':
							case 'bioarchive':
								$bioArchiveUrl = "https://api.biorxiv.org/details/biorxiv/";

							
								//var_dump($tableData);
								//$sourceIdentierColumnName = $foreignKeyField;
								//var_dump($sourceIdentierColumnName);
								

								print_r(  "[" . date("Y-m-d H:i:s") . "] Entry " . $sourceIdentifier . " does NOT exist!....Grabbing required data from " . $bioArchiveUrl . $sourceid . "\n") ;
								$curlHandle = curl_init();
								$curlSettings = [];
								$curlSettings[CURLOPT_URL] = $bioArchiveUrl . $sourceid;
								$curlSettings[CURLOPT_RETURNTRANSFER] = true;
								curl_setopt_array($curlHandle, $curlSettings);
								$returnedData = curl_exec($curlHandle);
								$returnedDataJSON = json_decode($returnedData, true);
								$publicationData =  $returnedDataJSON['collection'][count($returnedDataJSON['collection'])-1];
								$insertQuery = "insert into "  . $table_schema . "." . $table_name . "(";
								$insertColumns = '';
								$insertValues = '';
								$publicationSource = '';
								$sourceIdentifierPrefix = '';
								$version = '';
							
								foreach ($publicationData as $key => $bioArchivePublicationDatum) {
									switch ($key) {
										case 'version':
											$version = $bioArchivePublicationDatum;
											break;
										case 'server':
											$insertColumns = $insertColumns . 'source, journal, ';
											$insertValues = $insertValues . "'" . $bioArchivePublicationDatum . "'" . ', ' . "'" . str_replace("\\", "\\\\", str_replace("'","''",$bioArchivePublicationDatum)) . "', ";
											$sourceIdentifierPrefix = $bioArchivePublicationDatum;
											break;
										case 'doi':
											$insertColumns = $insertColumns . 'sourceid, ';
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$bioArchivePublicationDatum)) . "'" . ', ';
											$publicationSource = $bioArchivePublicationDatum;
											break;
										case 'title':
										case 'abstract':
										case 'authors':
										case 'type':
											$insertColumns = $insertColumns . $key . ', ';
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$bioArchivePublicationDatum)) . "'" . ', ';
											break;
										case 'author_corresponding_institution':
											$insertColumns = $insertColumns . 'address, ';
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$bioArchivePublicationDatum)) . "'" . ', ';
											break;
										case 'date':
											$insertColumns = $insertColumns . 'date, year, ';
											$year = explode("-", $bioArchivePublicationDatum)[0];
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$bioArchivePublicationDatum)) . "'" . ', ' . "'" . $year . "', ";
											break;
										case 'published':
											$insertColumns = $insertColumns . 'published_doi, ';
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$bioArchivePublicationDatum)) . "'" . ', ';

									}
									
									
								}

								$insertColumns = $insertColumns . 'url, doi, source_identifier' ;
								$insertValues = $insertValues . "'https://www.biorxiv.org/content/" . $publicationSource . 'v' . $version . "', '" . $publicationSource . "', '" .  $sourceIdentifierPrefix . ":" . $publicationSource . "'";
								

								$insertQuery = $insertQuery . $insertColumns . ") VALUES ( " . $insertValues . ")";
								print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $insertQuery . "\n");
								$queryStatement = $dbconn->query($insertQuery);
								if ($queryStatement === false) {
									//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
									print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
									
								}
								curl_close($curlHandle);
								break;
							case 'pmid':
							case 'pubmed':
								print_r(  "[" . date("Y-m-d H:i:s") . "] Entry " . $sourceIdentifier . " does NOT exist!....Grabbing required data from pubmed id (" . $sourceid . ")\n") ;
								$pubmedPublicationDataString = pmdataJSON($sourceid);
								//var_dump($pubmedDataString);
								$pubmedPublicationData = json_decode($pubmedPublicationDataString, true);
								//var_dump($pubmedData);

								$insertColumns = '';
								$insertValues = "'PubMed', ";
								$insertQuery = "insert into " . $table_schema . "." . $table_name . " (source, ";

								foreach ($pubmedPublicationData as $key => $pubmedPublicationDatum) {


									
									switch ($key) {
										case 'pmid':
										case 'pubmed':
											$insertColumns = $insertColumns . "sourceid, ";
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$pubmedPublicationDatum)) . "', ";
											break;
										case 'identifier':
											$insertColumns = $insertColumns . "source_identifier, ";
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$pubmedPublicationDatum)) . "', ";
											break;
										case 'year':
											$insertColumns = $insertColumns . "year, ";
											if (ctype_digit($pubmedPublicationDatum)) {
												$insertValues = $insertValues . "'" . $pubmedPublicationDatum . "', ";
											}
											else {
												$insertValues = $insertValues . 'NULL' . ', ';
											}
											break;
										case 'pubdate':
											$insertColumns = $insertColumns . "date, ";
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$pubmedPublicationDatum)) . "', ";
											break;
										case 'title':
										
										case 'journal':
										case 'volume':
										case 'number':
										case 'pages':
										case 'abstract':
										case 'url':
										case 'address':
										case 'authors':
										case 'keywords':
										case 'doi':
										case 'issn':
										case 'essn':
										case 'language':
										case 'type':
										//case 'pdf':
											$insertColumns = $insertColumns . $key . ', ';
											$insertValues = $insertValues . "'" . str_replace("\\", "\\\\", str_replace("'","''",$pubmedPublicationDatum)). "'" . ', ';
											break;		
									}
								}
								
								if (endsWith($insertColumns, ', ')) {
									$insertColumns = substr($insertColumns, 0, -2);
								}

								if (endsWith($insertValues, ', ')) {
									$insertValues = substr($insertValues, 0, -2);
								}
								$insertQuery = $insertQuery . $insertColumns . ") VALUES ( " . $insertValues . ")";
								print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $insertQuery . "\n");
								$queryStatement = $dbconn->query($insertQuery);
								if ($queryStatement === false) {
									//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
									print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
									
								}
								
								break;
						}
					}
				}
			}
		}
	}
	
	/*
		Function: loadTableWithWorksheetsData
		Purpose: Generate queries for loading worksheet data into a database
		Args:
			$worksheetsData: Array of Arrays representing the worksheet data to be loaded
			$schema: String containing name of the database schema that contains the table
			$tableMetaData: Associative Array containing information about the table to be loaded
			$tablesMetaData: Array of Associative Arrays containing all tables (In case we need to populate foreign tables)
		Returns: An array of queries for inserting data into the database
		Note: This function will only load data for non Join tables (Tables generated from Many-to-many relationships).
	*/
	function loadTableWithWorksheetsData($worksheetsData, $schema, $tableMetaData, $tablesMetaData, $dbconn) {
		
		//$insertQueries = [];
		
		if (!in_array($tableMetaData["table_name"], $GLOBALS['loadedTables'])) {
			$worksheetName = $tableMetaData['table_name'];
			$GLOBALS['loadedTables'][] = $worksheetName;
			//$worksheetData = findWorksheetDataByWorksheetName($worksheetName, $worksheetsData);
			/*for ($i=0; $i<count($tableMetaData["table_fields"]); $i++) {
				if ($tableMetaData["table_fields"][$i]["ref_table"] != "") {
					$referenceTable = findTableMetaDataByTableName($tableMetaData["table_fields"][$i]["ref_table"], $tablesMetaData);
					$insertQueries = array_merge($insertQueries, loadTableWithWorksheetsData($worksheetsData, $schema, $referenceTable, $tablesMetaData));
				}
			}*/

			if (array_key_exists('clear_data', $tableMetaData) && $tableMetaData['clear_data']) {
				//$insertQueries[] = 'DELETE FROM ' . $tableMetaData['table_name'];
				print_r(  "[" . date("Y-m-d H:i:s") . "] Running " . 'DELETE FROM "' . $schema . '".' . $tableMetaData['table_name'] . "\n");
				$queryStatement = $dbconn->query('DELETE FROM "' . $schema . '".'  . $tableMetaData['table_name']);
				if ($queryStatement === false) {
					//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
					print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
					exit;
					
				}
			}

			$insertQueryPrefix = 'INSERT INTO "' . $schema . '".' . $worksheetName . ' (';
			$fieldReplacementStringPrefix = ' ON CONFLICT (' . $tableMetaData["primary_key_field_name"] . ') DO UPDATE SET ';
			for ($i=0; $i<count($tableMetaData["table_fields"]); $i++) {
				if ($tableMetaData["table_fields"][$i]["ref_table"] != "") {
					$referenceTable = findTableMetaDataByTableName($tableMetaData["table_fields"][$i]["ref_table"], $tablesMetaData);
					if ($referenceTable == null) {
						print_r(  "[" . date("Y-m-d H:i:s") . "] Error: Table " . $tableMetaData["table_fields"][$i]["ref_table"] . " has not been defined in the json template \n");
						exit;
						//$insertQueries[] = $errorMsg;
						//return $insertQueries;
					}
					loadTableWithWorksheetsData($worksheetsData, $schema, $referenceTable, $tablesMetaData, $dbconn);
					if (array_key_exists('update_from_ext_source', $tableMetaData["table_fields"][$i]) && $tableMetaData["table_fields"][$i]['update_from_ext_source']){
						$field_to_check = $tableMetaData["table_fields"][$i]['field_name'];
						update_external_publications_foreign_keys ($schema, $tableMetaData["table_fields"][$i]["ref_table"], $worksheetsData[$worksheetName], $field_to_check, $dbconn);
					}
					else if (array_key_exists('update_ref_table_if_key_not_found', $tableMetaData["table_fields"][$i]) && $tableMetaData["table_fields"][$i]['update_ref_table_if_key_not_found']) {
						$field_to_check = $tableMetaData["table_fields"][$i]['field_name'];
						if (array_key_exists('add_table_and_column_info_to', $tableMetaData["table_fields"][$i])) {
							updateReferenceTableWithForeignKeyFromTable($worksheetsData[$worksheetName], $field_to_check, $schema, $tableMetaData["table_fields"][$i]["ref_table"], $tableMetaData["table_fields"][$i]["ref_table_key"], $tableMetaData["table_fields"][$i]['add_table_and_column_info_to'], $tableMetaData['table_name'] . "-" . $tableMetaData["table_fields"][$i]["field_name"],  $dbconn);
						}
						else {
							updateReferenceTableWithForeignKeyFromTable($worksheetsData[$worksheetName], $field_to_check, $schema, $tableMetaData["table_fields"][$i]["ref_table"], $tableMetaData["table_fields"][$i]["ref_table_key"], NULL, NULL,  $dbconn);
						}
					}
					//$insertQueries = array_merge($insertQueries, loadTableWithWorksheetsData($worksheetsData, $schema, $referenceTable, $tablesMetaData, $dbconn));
					if (($tableMetaData["table_fields"][$i]["export_table"] == "" && ($tableMetaData["table_fields"][$i]["ref_type"] != "Many-to-many" || (array_key_exists('only_display_in_join_table', $tableMetaData["table_fields"][$i]) && !$tableMetaData["table_fields"][$i]['only_display_in_join_table']))) || 
						(array_key_exists('field_present_in_csv', $tableMetaData["table_fields"][$i]) && $tableMetaData["table_fields"][$i]["field_present_in_csv"])) {
						$fieldName = $tableMetaData["table_fields"][$i]['field_name'];
						$insertQueryPrefix = $insertQueryPrefix . '"' . $fieldName . '"';
						if ($i<count($tableMetaData["table_fields"])-1) {
							$insertQueryPrefix = $insertQueryPrefix . ', ';
						}
					}

				}
				else if ($tableMetaData["table_fields"][$i]["field_type"] !== "SERIAL" && $tableMetaData["table_fields"][$i]["export_table"] == "" || (array_key_exists('field_present_in_csv', $tableMetaData["table_fields"][$i]) && $tableMetaData["table_fields"][$i]["field_present_in_csv"])) {
					$fieldName = $tableMetaData["table_fields"][$i]['field_name'];
					$insertQueryPrefix = $insertQueryPrefix . '"' . $fieldName . '"';
					if ($i<count($tableMetaData["table_fields"])-1) {
						$insertQueryPrefix = $insertQueryPrefix . ', ';
					}
				}
			}

			if (endsWith($insertQueryPrefix, ', ')) {
				$insertQueryPrefix = substr($insertQueryPrefix, 0, -2);
			}
			$insertQueryPrefix = $insertQueryPrefix . ') VALUES (';



			for ($h=1; $h<count($worksheetsData[$worksheetName]); $h++) {
				if (count($worksheetsData[$worksheetName][0]) > count($worksheetsData[$worksheetName][$h])){
					continue;
				}
				$insertQuery = $insertQueryPrefix;
				$fieldReplacementString = $fieldReplacementStringPrefix;
				
				
				for ($i=0; $i<count($tableMetaData["table_fields"]); $i++) {
					if ($tableMetaData["table_fields"][$i]["field_type"] === "SERIAL"){
						continue;
					}

					if (($tableMetaData["table_fields"][$i]["export_table"] == "" && ($tableMetaData["table_fields"][$i]["ref_type"] != "Many-to-many" || (array_key_exists('only_display_in_join_table', $tableMetaData["table_fields"][$i]) && !$tableMetaData["table_fields"][$i]['only_display_in_join_table']))) ||
						(array_key_exists('field_present_in_csv', $tableMetaData["table_fields"][$i]) && $tableMetaData["table_fields"][$i]["field_present_in_csv"])){
						$fieldName = $tableMetaData["table_fields"][$i]['field_name'];
						$fieldValue = utf8_encode(getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$worksheetName], $fieldName, $h));
						if (!is_null($fieldValue)) {
							if (trim($fieldValue) == '' || trim($fieldValue) == 'NA' || trim($fieldValue) == 'N/A'){
								if ($tableMetaData["table_fields"][$i]["field_type"]=='TIMESTAMP' || $tableMetaData["table_fields"][$i]["field_type"]=='INTEGER' || $tableMetaData["table_fields"][$i]["field_type"]=='FLOAT' || $tableMetaData["table_fields"][$i]["field_type"]=='BIGINT' || $tableMetaData["table_fields"][$i]["field_type"]=='NUMERIC' || $tableMetaData["table_fields"][$i]["field_type"]=='ENUM' || 
									$tableMetaData["table_fields"][$i]["field_type"]=='JSON' || $tableMetaData["table_fields"][$i]["field_type"]=='JSONB' || $tableMetaData["table_fields"][$i]["ref_type"] != "") {
									$insertQuery = $insertQuery . 'NULL';
									if ($fieldName != $tableMetaData["primary_key_field_name"] ) {
										$fieldReplacementString = $fieldReplacementString . $fieldName . ' = NULL';
									}
								}
								else {
									$insertQuery = $insertQuery . "'" . str_replace("\\", "\\\\", str_replace("'","''",$fieldValue)) . "'";
									if ($fieldName != $tableMetaData["primary_key_field_name"] ) {
										$fieldReplacementString = $fieldReplacementString . $fieldName . " = '" . str_replace("\\", "\\\\", str_replace("'","''",$fieldValue)) . "'";
									}
								}
							}
							else {
								$insertQuery = $insertQuery . "'" . str_replace("\\", "\\\\", str_replace("'","''",$fieldValue)) . "'";
								if ($fieldName != $tableMetaData["primary_key_field_name"] ) {
									$fieldReplacementString = $fieldReplacementString . $fieldName . " = '" . str_replace("\\", "\\\\", str_replace("'","''",$fieldValue)) . "'";
								}
							}
						}
						else {
							print_r(  "[" . date("Y-m-d H:i:s") . "] Error: Field " . $fieldName . " not found in sheet " . $worksheetName . "\n");
							exit;
							//$insertQueries[] = $errorMsg;
							
						}
						if ($i<count($tableMetaData["table_fields"])-1) {
							$insertQuery = $insertQuery . ', ';
							if ($fieldName != $tableMetaData["primary_key_field_name"] ) {
								$fieldReplacementString = $fieldReplacementString . ', ';
							}
						}
					}
				}
				if (endsWith($insertQuery, ', ')) {
					$insertQuery = substr($insertQuery, 0, -2);
				}

				if (endsWith($fieldReplacementString, ', ')) {
					$fieldReplacementString = substr($fieldReplacementString, 0, -2);
				}

				$insertQuery = $insertQuery . ')';
				if ($fieldReplacementString != $fieldReplacementStringPrefix) {
					$insertQuery = $insertQuery . ' ' . $fieldReplacementString;
				}
				
				//$insertQueries[] = $insertQuery;
				print_r(  "[" . date("Y-m-d H:i:s") . "] Running " . $insertQuery . "\n");
				$queryStatement = $dbconn->query($insertQuery);
				if ($queryStatement === false) {
					//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
					print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
					exit;
					
				}
			}
			//$GLOBALS['loadedTables'][] = $worksheetName;
			
			//return $insertQueries;
		}
		/*else {
			
			return $insertQueries;
		}*/
		
	}

	function loadImportedFields ($worksheetsData, $schemaMetaData, $dbconn) {
		//$insertQueries = [];
		$schema = $schemaMetaData['schema'];
		$tablesMetaData = $schemaMetaData['tables'];
		foreach ($tablesMetaData as $tableMetaData) {
			$tableName = $tableMetaData['table_name'];
			$tablePrimaryKey = $tableMetaData['primary_key_field_name'];
			$tableData = $worksheetsData[$tableMetaData['table_name']];
			
			foreach ($tableMetaData['table_fields'] as $fieldData) {
				//if ($fieldData['ref_table'] != '' && $fieldData['ref_type'] == 'field_import') {
				if ($fieldData['export_table'] != '') {
					$export_table = $fieldData['export_table'];
					$export_table_key_field = $fieldData['export_table_key_field'];
					$export_field = $fieldData['export_field'];
					$import_key_source_field = $fieldData['import_key_source_field'];
					
					for ($i=1; $i<count($tableData); $i++) {
						$import_keys_array = [];
						$primaryKeyValueAtRow = getWorksheetDatumAtColumnHeaderAndRow($tableData, $tablePrimaryKey, $i);
						if (array_key_exists("import_key_source_table", $fieldData)) {
							if (!array_key_exists("import_key_source_table_key_field", $fieldData) || !array_key_exists("import_key_source_table_key_values", $fieldData) || !array_key_exists("import_key_source_table_key_field", $fieldData)) {
								print_r(  "[" . date("Y-m-d H:i:s") . "] Error: Missing 'import_key_source_table_key_field' or 'import_key_source_table_key_values' or 'import_key_source_table_key_field' in field data ''");
								exit;
							}

							$unprocessed_import_keys_arrays = selectDBColumnDataByKeys ($dbconn, $schema, $fieldData['import_key_source_table'], $fieldData['import_key_source_field'], $fieldData['import_key_source_table_key_field'], explode("|",getWorksheetDatumAtColumnHeaderAndRow($tableData, $fieldData['import_key_source_table_key_values'], $i)));
							foreach($unprocessed_import_keys_arrays as $unprocessed_import_keys_array){
								$import_keys_array = array_merge($import_keys_array, explode("|", $unprocessed_import_keys_array));
							}
							//print_r("hubba hubba.......");
							//var_dump($import_keys_array);

						}
						else {
							$import_keys = getWorksheetDatumAtColumnHeaderAndRow($tableData, $import_key_source_field, $i);
							$import_keys_array = explode("|", $import_keys);
						}
						
						$import_data = '';
						for ($j=0; $j<count($import_keys_array); $j++){
							$import_key = $import_keys_array[$j];
							if (trim($import_key)!=='') {
								print_r(  "[" . date("Y-m-d H:i:s") . "] Getting " . $export_field . " data from table \"" . $schema . "\"." . $export_table . " at ". $export_table_key_field . "='" . $import_key . "'\n");
								print_r(  "[" . date("Y-m-d H:i:s") . "] Running: select distinct " . $export_field . " from \"" . $schema . "\"."  . $export_table . " where " . $export_table_key_field . " = '" . $import_key . "'\n");
								$queryStatement = $dbconn->query("select distinct " . $export_field . " from \"" . $schema . "\"."  . $export_table . " where " . $export_table_key_field . " = '" . $import_key . "'");
								//$queryStatement->setFetchMode(PDO::FETCH_ASSOC);
								if ($queryStatement === false) {
								//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
									print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
									continue;
								}
								//print_r("select " . $queryStatement);
								foreach ($queryStatement as $row) {
									if ($row[$export_field]!='') {
										$import_data = $import_data . $row[$export_field];
										if (!array_key_exists("keep_imported_value_duplicates", $fieldData) || !$fieldData['keep_imported_value_duplicates']) {
											$unprocessed_import_data_array = explode("|", $import_data);
											$processed_import_data_array = array_unique($unprocessed_import_data_array);
											$processed_import_data = implode("|", $processed_import_data_array);
											$import_data =  $processed_import_data  ;
										}
										if ($import_data != '') {
											$import_data = $import_data . "|";
										}
									}

									
								}
								if ($j==count($import_keys_array)-1 && $import_data != '') {
									$import_data = substr($import_data, 0, -1);
								}
								/*if ($j<count($import_keys_array)-1 && $import_data != '' ) {
									$import_data = $import_data . "|";
								}*/
							}
						}
						if ($import_data != '') {
							$insertQuery = "update \"" . $schema . "\"." . $tableName . " set " . $fieldData['field_name'] . " = '" . str_replace("\\", "\\\\", str_replace("'","''",$import_data)) . "' where " . $tablePrimaryKey . "='" . $primaryKeyValueAtRow . "'";
							print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $insertQuery . "\n");
							$queryStatement = $dbconn->query($insertQuery);
							if ($queryStatement === false) {
								//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
								print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
								exit;
								
							}
						}
					}

				}
				
			}
		}
		//return $insertQueries;
	}

	/*
		Function: loadManyToManyTableWithWorksheetsData
		Purpose: Generates queries to load data into database Join tables
		Args:
			$worksheetsData: Array of Arrays representing the worksheet data to be loaded
			$schemaMetaData: An Associative Array with schema and all table data (This is a representation of the gsheets_db_tables.json file in PHP)
		Returns: An array of queries for inserting data into the join tables
		Note: This function will only load data for Join tables (Tables generated from Many-to-many relationships).
	*/
	function loadManyToManyTableWithWorksheetsData ($worksheetsData, $schemaMetaData, $dbconn){
		//$insertQueries = [];
		$schema = $schemaMetaData['schema'];
		$tablesMetaData = $schemaMetaData['tables'];
		foreach ($tablesMetaData as $tableMetaData) {
			foreach ($tableMetaData['table_fields'] as $fieldData) {
				if ($fieldData['ref_table'] != '' && $fieldData['ref_type'] == 'Many-to-many') {
					$join_table_name = '';
					$manyToManyTable0 = $tableMetaData['table_name'];
					$manyToManyTable0PrimKeyFieldName = $tableMetaData['primary_key_field_name'];
					$manyToManyTable1 = $fieldData['ref_table'];

					if (array_key_exists('join_table_name', $fieldData)) {
						$join_table_name = $fieldData['join_table_name'];
					}
					else {
						$join_table_name = $manyToManyTable0 . "_" . $manyToManyTable1;
					}
					
					if (!in_array($join_table_name, $GLOBALS['loadedTables']) && (!array_key_exists('clear_join_table', $fieldData) || $fieldData['clear_join_table'])) {
			
						$GLOBALS['loadedTables'][] = $join_table_name;
						//$insertQueries[] = 'DELETE FROM ' . $join_table_name;
						print_r(  "[" . date("Y-m-d H:i:s") . "] Running: DELETE FROM \"" . $schema . "\"."  . $join_table_name . " \n");
						$queryStatement = $dbconn->query('DELETE FROM "' . $schema . '".' . $join_table_name);
						if ($queryStatement === false) {
							//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
							print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
							exit;
							
						}
					}
					


					for ($i=1; $i<count($worksheetsData[$manyToManyTable0]); $i++) {
						if (count($worksheetsData[$manyToManyTable0][0]) > count($worksheetsData[$manyToManyTable0][$i])){
							continue;
						}

						//$supplimentalInsertFields = '';
						//$supplimentalInsertValues = '';
						$suplimentalInserstionData = [];
						$manyToManyTable0PrimKeyValue = getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$manyToManyTable0], $manyToManyTable0PrimKeyFieldName, $i);
						if (!array_key_exists('only_display_in_join_table', $fieldData) || $fieldData['only_display_in_join_table']){
							$manyToManyFK1DataArray = array(getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$manyToManyTable0], $fieldData['field_name'], $i));
						}
						else {
							$manyToManyFK1DataArray = selectDBColumnDataByKey($dbconn, $schema, $manyToManyTable0, $fieldData['field_name'], $manyToManyTable0PrimKeyFieldName, "'" . str_replace("\\", "\\\\", str_replace("'","''", $manyToManyTable0PrimKeyValue)). "'");
						}
						//$manyToManyFK1Data = getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$manyToManyTable0], $fieldData['field_name'], $i);
						
						
						$manyToManyFK1Data = null;
						if (count ($manyToManyFK1DataArray) >= 1) {
							$manyToManyFK1Data = $manyToManyFK1DataArray[0];
						}
						$manyToManyFK1TableData = findTableMetaDataByTableName($manyToManyTable1, $tablesMetaData);
						$manyToManyFK1DataType = getFieldTypeDetails($fieldData['ref_table_key'], $manyToManyFK1TableData["table_fields"])['field_type'];
						$manyToManyFK1Values = [];

						if (!is_null ($manyToManyFK1Data)) {
							$manyToManyFK1Values = explode('|', $manyToManyFK1Data);
						}

						if (array_key_exists("supplimental_fields", $fieldData)){
							$supplimental_fields = $fieldData["supplimental_fields"];
							foreach ($supplimental_fields as $supplimental_field) {
								if ($supplimental_field['field_strategy'] == 'select') {
									$suplimentalInserstionDataArray = selectDBColumnDataByKey($dbconn, $schema, $manyToManyTable0, $supplimental_field['field_name'], $manyToManyTable0PrimKeyFieldName, "'" . str_replace("\\", "\\\\", str_replace("'","''", $manyToManyTable0PrimKeyValue)) . "'");
									if (count($suplimentalInserstionDataArray) >= 1) {
										if (count($manyToManyFK1Values) > 1) {
											$implodedSuplimentalInserstionData = $suplimentalInserstionDataArray[0];
											$suplimentalInserstionData[$supplimental_field['field_name']] = explode("|", $implodedSuplimentalInserstionData);
										}
										else {
											$suplimentalInserstionData[$supplimental_field['field_name']] = $suplimentalInserstionDataArray;
										}
									} 
									//$suplimentalInserstionData[$supplimental_field['field_name']] = explode("|", getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$manyToManyTable0], $supplimental_field['field_name'], $i));
								}
								
							}

							if (!is_null ($manyToManyFK1Values)) {
								$manyToManyFK1Values = explode('|', $manyToManyFK1Data);

								foreach ($supplimental_fields as $supplimental_field) {
									if ($supplimental_field['field_strategy'] == 'constant')  {
										$suplimentalInserstionData[$supplimental_field['field_name']] = array_fill(0, count($manyToManyFK1Values), $supplimental_field['field_value']);
									}
								}
							}
							//var_dump($suplimentalInserstionData);
							/*$supplimental_fields = $fieldData["supplimental_fields"];
							foreach ($supplimental_fields as $supplimental_field) {
								if ($supplimentalInsertFields!='') {
									$supplimentalInsertFields = $supplimentalInsertFields . ', ';
									$supplimentalInsertValues = $supplimentalInsertValues . ', ';
								}
								$supplimentalInsertFields = $supplimentalInsertFields . $supplimental_field['field_name'];
								if ($supplimental_field['field_strategy'] == 'constant') {
									$supplimentalInsertValues = $supplimentalInsertValues . "'" . $supplimental_field['field_value'] . "'";
								}
								else {
									$supplimentalInsertValues = $supplimentalInsertValues . "'" . getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$manyToManyTable0], $supplimental_field['field_name'], $i) . "'";
								}

							}*/
						}

						/*if ($supplimentalInsertFields!='') {
							$supplimentalInsertFields = ', ' . $supplimentalInsertFields;
							$supplimentalInsertValues = ', ' . $supplimentalInsertValues;
						}*/

						//$manyToManyFK0Data = getWorksheetDatumAtColumnHeaderAndRow($worksheetsData[$manyToManyTable0], $tableMetaData['primary_key_field_name'], $i);
						$manyToManyFK0Data = $manyToManyTable0PrimKeyValue;
						$manyToManyFK0DataType = getFieldTypeDetails($tableMetaData['primary_key_field_name'], $tableMetaData['table_fields'])['field_type'];

						if ($manyToManyFK0Data=='' || $manyToManyFK0Data=='N/A' || $manyToManyFK0Data=='NA') {
							
							if ($manyToManyFK1DataType =='TIMESTAMP' || $manyToManyFK1DataType =='INTEGER' || $manyToManyFK0DataType =='FLOAT' || $manyToManyFK0DataType=='BIGINT' || $manyToManyFK0DataType=='NUMERIC' ||
								$manyToManyFK1DataType =='JSON' || $manyToManyFK1DataType =='JSONB') {
								$manyToManyFK0Data = NULL;
							}
						}
						else {
							$manyToManyFK0Data= "'" . $manyToManyFK0Data ."'";
						}

						if (is_null ($manyToManyFK0Data)) {
							print_r(  "[" . date("Y-m-d H:i:s") . "] Error: Field " . $tableMetaData['primary_key_field_name'] . " not found in sheet " . $manyToManyTable0 . "\n");
							exit;
							//$insertQueries[] = $errorMsg;
						}
						


						

						if (!is_null ($manyToManyFK1Values)) {
							//$manyToManyFK1Values = explode('|', $manyToManyFK1Data);
							for ($j=0; $j<count($manyToManyFK1Values); $j++){
								$manyToManyFK1Value = $manyToManyFK1Values[$j];
								if ($manyToManyFK1Value =='' || $manyToManyFK1Value =='N/A' || $manyToManyFK1Value =='NA') {
							
									if ($manyToManyFK1DataType =='TIMESTAMP' || $manyToManyFK1DataType =='INTEGER' || $manyToManyFK1DataType =='FLOAT' || $manyToManyFK1DataType=='BIGINT' || $manyToManyFK1DataType=='NUMERIC' ||
										$manyToManyFK1DataType =='JSON' || $manyToManyFK1DataType =='JSONB') {
										$manyToManyFK1Value = NULL;
									}
								}
								else {
									$manyToManyFK1Value = "'" . $manyToManyFK1Value . "'";
								}

								if (trim($manyToManyFK1Value)!=='') {
									$supplimentalInsertFields = '';
									$supplimentalInsertValues = '';
									foreach($suplimentalInserstionData as  $supplimentalInsertField => $supplimentalInsertFieldValues){
										if (count($manyToManyFK1Values) < count($supplimentalInsertFieldValues)) {
											print_r(  "[" . date("Y-m-d H:i:s") . "] Error: The are are too many values for table '" . $tableMetaData['table_name'] . "' at column '" . $fieldData['field_name'] . "' at row '" . $i . "'");
											//$insertQueries[] = $insertQuery;
											
											exit;
										}

										$supplimentalInsertFields = $supplimentalInsertFields . $supplimentalInsertField;
										if ($j > count($supplimentalInsertFieldValues)-1) {
											$supplimentalInsertValues = $supplimentalInsertValues . "''";
										}
										else {
											$supplimentalInsertValues = $supplimentalInsertValues . "'" .   str_replace("\\", "\\\\", str_replace("'","''",$supplimentalInsertFieldValues[$j])) . "'";
										}

										$supplimentalInsertFields = $supplimentalInsertFields . ', ';
										$supplimentalInsertValues = $supplimentalInsertValues . ', ';

										
									}
									

									if ($supplimentalInsertFields != '') {
										$supplimentalInsertFields = substr($supplimentalInsertFields, 0, -2);
										$supplimentalInsertValues = substr($supplimentalInsertValues, 0, -2);
										$insertQuery = 'INSERT INTO "' . $schema . '".' . $join_table_name . " (" . $tableMetaData['primary_key_field_name'] . ", " . $fieldData['ref_table_key'] . ", " . $supplimentalInsertFields . ") VALUES (" . $manyToManyFK0Data . ", " . $manyToManyFK1Value . ", " . $supplimentalInsertValues . ")" ;
									}
									else {
										$insertQuery = 'INSERT INTO "' . $schema . '".' . $join_table_name . " (" . $tableMetaData['primary_key_field_name'] . ", " . $fieldData['ref_table_key'] . ") VALUES (" . $manyToManyFK0Data . ", " . $manyToManyFK1Value . ")" ;
									}
									//print_r($insertQuery . "\n");
									//$insertQueries[] = $insertQuery;
									print_r(  "[" . date("Y-m-d H:i:s") . "] Running: " . $insertQuery . "\n");
									$queryStatement = $dbconn->query($insertQuery);
									if ($queryStatement === false) {
										//echo "[" . date("Y-m-d H:i:s") . "] Failed!\n";
										print_r(  "[" . date("Y-m-d H:i:s") . "] Error: " . $dbconn->errorInfo()[2] . "\n");
										exit;
										
									}
									
									
								}
							}
						}
						else {
							print_r(  "[" . date("Y-m-d H:i:s") . "] Error: Field " . $fieldData['ref_table_key'] . " not found in sheet " . $manyToManyTable1 . "\n");
							exit;
							//$insertQueries[] = $errorMsg;
						}
					}
				}
			}
		}
		//return $insertQueries;

	}

	/*
		Function: getFieldTypeDetails
		Purpose: Information about a field
		Args:
			$tableFieldName: A string containing the name of the field for which we want the field details
			$tableFields: An Array of Associative Arrays  containing all the field data for the table
		Returns: An Associative Array containing information about fields
	*/
	function getFieldTypeDetails($tableFieldName, $tableFields) {
		$result = [];
		for ($i=0; $i<sizeof($tableFields); $i++) {
			if ($tableFields[$i]['field_name'] == $tableFieldName){
				$result['field_type'] = $tableFields[$i]['field_type'];
				$result['field_length'] = $tableFields[$i]['field_length'];
				break;
			}
		}
		return $result;
	}


	/*
		Function: generateFieldTypeSQLString
		Purpose: Generates the appropriate SQL substring for different types of fields
		Args:
			$tableFieldName: A string containing the name of the field for which we want the field details
			$tableFields: An Array of Associative Arrays  containing all the field data for the table
		Returns: A string containing the SQL substring for different column types
	*/
	function generateFieldTypeSQLString($tableFieldName, $tableFields) {
		$fieldInfo = getFieldTypeDetails($tableFieldName, $tableFields);

		$result = '';
		if (count($fieldInfo)>0){
			switch (strtolower($fieldInfo['field_type'])) {
				case 'char':
				case 'varchar':
					$result = $fieldInfo['field_type'] . '(' . $fieldInfo['field_length'] .')';
					break;
				default:
					$result = $fieldInfo['field_type'];
					break;
			}
		}
		return $result;
	}

	/*
		Function: generateFieldDetails
		Purpose: Generates SQL queries for generating a single table
		Args:
			$schema: A string containing the schema for the table to be created
			$tableName: A string containing the name of the table to be created
			$tablePrimaryKeyFieldName: String containing the primary key of the table to be created
			$tableFieldsData: An Array of Associative Arrays  containing all the field data for the table
			$tablesMetaData: An Array of Associative Arrays  containing all the tables in the schema (Needed if this particular table references other tables)
		Returns: An Array containing the SQL queries for creating a table
	*/
	function generateFieldDetails($schema, $tableName, $tablePrimaryKeyFieldName, $tableFieldsData, $tablesMetaData){
		$tableGenerationQueries = [];
		$sanitized_table_name = $tableName;
		switch (strtolower($sanitized_table_name)){
			case 'references':
			case 'desc':
				$sanitized_table_name=$sanitized_table_name.'_';
				break;
		}

		$createTableQuery = '';
		$primaryKeyTypeSQLString = '';
		if ($tablePrimaryKeyFieldName!=NULL) {
			$primaryKeyTypeSQLString = generateFieldTypeSQLString($tablePrimaryKeyFieldName, $tableFieldsData);
			if ($primaryKeyTypeSQLString == "") {
				$tableGenerationQueries[] = "Error: Specified primary key field '" . $tablePrimaryKeyFieldName . "' was not found in fields configuration for table '" . $tableName . "'";
				return $tableGenerationQueries;
			}
			
			$createTableQuery = 'CREATE TABLE IF NOT EXISTS "' . $schema . '".' . $sanitized_table_name . ' (' . $tablePrimaryKeyFieldName . ' ' . $primaryKeyTypeSQLString . ' PRIMARY KEY, ';
			
		}
		else {
			$createTableQuery = 'CREATE TABLE IF NOT EXISTS "' . $schema . '".' . $sanitized_table_name . ' (';
		}
		$uniquenessConstraints = '';
		$joinTables = array();
		
		for($i=0; $i<sizeof($tableFieldsData); $i++){
			//echo "dealing with " . $tableFieldsData[$i]["field_name"] . ", ";
			if (($tableFieldsData[$i]["ref_table"]=="" || (array_key_exists('only_display_in_join_table', $tableFieldsData[$i]) && !$tableFieldsData[$i]['only_display_in_join_table'] && $tableFieldsData[$i]["ref_type"]=="Many-to-many")) && $tableFieldsData[$i]["field_name"]!=$tablePrimaryKeyFieldName){
				$sanitized_field_name=$tableFieldsData[$i]["field_name"];
				switch (strtolower($sanitized_field_name)){
				case 'desc':
					$sanitized_field_name=$sanitized_field_name.'_';
					break;
				}
				$sanitized_field_name='"' . $sanitized_field_name . '"';
				
				$fieldTypeToSwitch = '';
				$fieldTypeLength = '';
				if (array_key_exists('only_display_in_join_table', $tableFieldsData[$i]) && !$tableFieldsData[$i]['only_display_in_join_table']) {
					$fieldTypeToSwitch = 'varchar';
					$fieldTypeLength = '512';
				}
				else {
					$fieldTypeToSwitch = strtolower($tableFieldsData[$i]["field_type"]);
					$fieldTypeLength = $tableFieldsData[$i]["field_length"];
				}
				
				switch($fieldTypeToSwitch){
					case 'int':
					case 'enum':
					case 'char':
					case 'varchar':
						if ($fieldTypeToSwitch == 'enum') {
							$createTableQuery = $createTableQuery . $sanitized_field_name . ' ' . $fieldTypeToSwitch . '(\'' . implode($tableFieldsData[$i]["field_length"], "','") . '\')';
						}
						else if ($fieldTypeToSwitch == 'int') {
							$createTableQuery = $createTableQuery . $sanitized_field_name . ' ' . $fieldTypeToSwitch;
						}
						else {
							$createTableQuery = $createTableQuery . $sanitized_field_name . ' ' . $fieldTypeToSwitch . ' (' . $fieldTypeLength . ')';
						}
						if ($tableFieldsData[$i]["unique"]){
							$uniquenessConstraints = $uniquenessConstraints . ", " . 'UNIQUE (' . $sanitized_field_name . ')';
							//$createTableQuery = $createTableQuery . ' UNIQUE, ';
						}
						

						if (!$tableFieldsData[$i]["nullable"]){
							$createTableQuery = $createTableQuery . ' NOT NULL, ';
						}
						else {
							$createTableQuery = $createTableQuery . ', ';
						}
						
						break;
					default:
						$createTableQuery = $createTableQuery .  $sanitized_field_name . ' ' . $tableFieldsData[$i]["field_type"];
						if ($tableFieldsData[$i]["unique"]){
							$uniquenessConstraints = $uniquenessConstraints . "," .  'UNIQUE (' . $sanitized_field_name . ')';
							//$createTableQuery = $createTableQuery . ' UNIQUE, ';
						}
						
						if (!$tableFieldsData[$i]["nullable"]){
							$createTableQuery = $createTableQuery . ' NOT NULL, ';
						}
						else {
							$createTableQuery = $createTableQuery . ', ';
						}
						break;
				}
			}
			if ($tableFieldsData[$i]["ref_table"]!=""){
				$referenceTable = findTableMetaDataByTableName($tableFieldsData[$i]["ref_table"], $tablesMetaData);
				if ($referenceTable != null){
					$tableGenerationQueries = array_merge($tableGenerationQueries, generateTable($schema, $referenceTable, $tablesMetaData));
					$referenceTableFields = $referenceTable['table_fields'];
					$referenceTableKeyType = '';
					$referenceTableKeyLength = '';
					foreach ($referenceTableFields as $referenceTableField){
						if ($referenceTableField['field_name'] == $tableFieldsData[$i]["ref_table_key"]) {
							$referenceTableKeyType = $referenceTableField['field_type'];
						}
					}

					if ($referenceTableKeyType=='' && $tableFieldsData[$i]["ref_table_key"]!='NULL'){
						$tableGenerationQueries[] = "Error: Foreign key field '" . $tableFieldsData[$i]["ref_table_key"] . "' not found";
						return $tableGenerationQueries;
					}

					if ($referenceTableKeyType !== $tableFieldsData[$i]["field_type"] && $tableFieldsData[$i]["ref_type"]=="Many-to-one") {
						$errorMsg = "Error: Field type mismatch between foreign key column " .  $sanitized_table_name . "->" . $tableFieldsData[$i]["field_name"] . " (". $tableFieldsData[$i]["field_type"] .") and ";
						$errorMsg = $errorMsg . $tableFieldsData[$i]["ref_table"] . "->" . $tableFieldsData[$i]["ref_table_key"] . " (" . $referenceTableKeyType .")";
						$tableGenerationQueries[] = $errorMsg;
						return $tableGenerationQueries;
					}

					if ($referenceTableKeyLength !== $tableFieldsData[$i]["field_length"] && $tableFieldsData[$i]["ref_type"]=="Many-to-one") {
						$errorMsg = "Error: Field size mismatch between foreign key column " .  $sanitized_table_name . "->" . $tableFieldsData[$i]["field_name"] . " (". $tableFieldsData[$i]["field_length"] .") and ";
						$errorMsg = $errorMsg . $tableFieldsData[$i]["ref_table"] . "->" . $tableFieldsData[$i]["ref_table_key"] . " (" . $referenceTableKeyLength .")";
						$tableGenerationQueries[] = $errorMsg;
						return $tableGenerationQueries;
					}

					$supplimentalFieldQueryString = '';
					$supplimentalUniquenessConstraints = '';
					if ($tableFieldsData[$i]["ref_type"]=="Many-to-many") {
						if (array_key_exists("supplimental_fields", $tableFieldsData[$i])){
							$supplimental_fields = $tableFieldsData[$i]["supplimental_fields"];
							
							foreach ($supplimental_fields as $supplimental_field) {
								if ($supplimentalFieldQueryString == '') {
									$supplimentalFieldQueryString = '|';
								}

								$supplimentalFieldTypeString = "";
								switch (strtolower($supplimental_field['field_type'])) {
									case 'char':
									case 'varchar':
										$supplimentalFieldTypeString = $supplimental_field['field_type'] . '(' . $supplimental_field['field_length'] .')';
										break;
									default:
										$supplimentalFieldTypeString = $supplimental_field['field_type'];
										break;
								}

								if ($supplimentalFieldQueryString != "|") {
									$supplimentalFieldQueryString = $supplimentalFieldQueryString . ', ';
								}

								$supplimentalFieldQueryString = $supplimentalFieldQueryString . $supplimental_field['field_name'] . ' ' . $supplimentalFieldTypeString;

								if ($supplimental_field["unique"]){
									$supplimentalUniquenessConstraints = "," . $supplimentalUniquenessConstraints . 'UNIQUE (' . $supplimental_field['field_name'] . ')';
									
								}

								if (!$supplimental_field['nullable']) {
									$supplimentalFieldQueryString = $supplimentalFieldQueryString . ' NOT NULL'; 
								}
							}
						}
						$join_table_name_string = '';
						if (array_key_exists("join_table_name", $tableFieldsData[$i])){
							 $join_table_name_string = $tableFieldsData[$i]['join_table_name'];
						}
						else {
							$join_table_name_string = $tableName . '_' . $tableFieldsData[$i]["ref_table"];
						}

						$joinTables[] = $tableName . "|" .
						$tableFieldsData[$i]["ref_type"] . "|" .
						$tableFieldsData[$i]["ref_table"] . "|" .
						$tableFieldsData[$i]["ref_table_key"] . "|" .
						$tableFieldsData[$i]["field_name"] . "|" .
						$referenceTableKeyType . "|" .
						$tableFieldsData[$i]["field_length"] . "|" .
						$join_table_name_string . $supplimentalFieldQueryString;

					}
					else if ($tableFieldsData[$i]["ref_type"]=="Many-to-one") {
						$joinTables[] = $tableName . "|" .
						$tableFieldsData[$i]["ref_type"] . "|" .
						$tableFieldsData[$i]["ref_table"] . "|" .
						$tableFieldsData[$i]["ref_table_key"] . "|" .
						$tableFieldsData[$i]["field_name"] . "|" .
						$referenceTableKeyType . "|" .
						$tableFieldsData[$i]["field_length"] . "|" .
						$supplimentalFieldQueryString;
					}

					
				}
			}

			
		}
		
		foreach ($joinTables as $joinTable){
			$joinTableNames = explode("|", $joinTable);
			if ($joinTableNames[1] == "Many-to-one") {
				$foreign_key_table = $joinTableNames[2];
				$foreign_key_table_key = $joinTableNames[3];
				$foreign_key_field_name = $joinTableNames[4];
				$foreign_key_field_type = $joinTableNames[5];
				$foreign_key_field_length = $joinTableNames[6];

				switch (strtolower($foreign_key_field_name)){
					case 'references':
					case 'desc':
						$sanitized_fieldName1=$sanitized_fieldName1.'_';
						break;
				}
				$foreignKeyTypeString = "";
				switch (strtolower($foreign_key_field_type)) {
					case 'char':
					case 'varchar':
						$foreignKeyTypeString = $foreign_key_field_type . '(' . $foreign_key_field_length .')';
						break;
					default:
						$foreignKeyTypeString = $foreign_key_field_type;
						break;
				}
				$createTableQuery = $createTableQuery . $foreign_key_field_name . ' ' . $foreignKeyTypeString . ', FOREIGN KEY (' . $foreign_key_field_name . ') REFERENCES "' . $schema . '".' . $foreign_key_table . ' ' . '(' . $foreign_key_table_key . ')  ON DELETE CASCADE ON UPDATE CASCADE, ';
			}
		}
		$createTableQuery = substr($createTableQuery, 0, -2);
		$createTableQuery = $createTableQuery . $uniquenessConstraints;
		$createTableQuery = $createTableQuery . ')';
		
		
		$tableGenerationQueries[]= $createTableQuery;
		

		foreach ($joinTables as $joinTable){
			$joinTableNames = explode("|", $joinTable);
			
			if ($joinTableNames[1] == "Many-to-many") {
				$foreign_key_table0 = $joinTableNames[0];
				$foreign_key_table1 = $joinTableNames[2];
				$foreign_key_table_key = $joinTableNames[3];
				$foreign_key_field_name = $joinTableNames[4];
				$foreign_key_field_type = $joinTableNames[5];
				$foreign_key_field_length = $joinTableNames[6];

				$supplimental_fields_query_string='';
				//var_dump($joinTableNames);
				if (count($joinTableNames) >= 9){
					$supplimental_fields_query_string =  $joinTableNames[8];
				}

				
				$join_table_name = $joinTableNames[7];
				



				//$sanitized_fieldName0 = $joinTableNames[0];
				//$sanitized_fieldName1 = $joinTableNames[1];
				
				switch (strtolower($foreign_key_table0)){
					case 'references':
					case 'desc':
						$foreign_key_table0=$foreign_key_table0.'_';
						break;
				}

				switch (strtolower($foreign_key_table1)){
					case 'references':
					case 'desc':
						$foreign_key_table1=$foreign_key_table1.'_';
						break;
				}

				
				$foreignKey1TypeString = "";
				switch (strtolower($foreign_key_field_type)) {
					case 'char':
					case 'varchar':
						$foreignKey1TypeString = $foreign_key_field_type . '(' . $foreign_key_field_length .')';
						break;
					default:
						$foreignKey1TypeString = $foreign_key_field_type;
						break;
				}

				$createJoinTableQuery = '';

				if ($tablePrimaryKeyFieldName == NULL) {
					$tableGenerationQueries[] = "Error: Many-to-many columns must have a primary key";
				}

				if ($supplimental_fields_query_string != ''){
					$createJoinTableQuery = 'CREATE TABLE IF NOT EXISTS "' . $schema . '".' . $join_table_name . ' (' . $tablePrimaryKeyFieldName . ' ' . $primaryKeyTypeSQLString . ', ' . $supplimental_fields_query_string . ', FOREIGN KEY (' . $tablePrimaryKeyFieldName . ') REFERENCES "' . $schema . '".' . $foreign_key_table0 . '(' . $tablePrimaryKeyFieldName . ') ON DELETE CASCADE ON UPDATE CASCADE, ' . $foreign_key_table_key . ' ' . $foreignKey1TypeString  . ', FOREIGN KEY (' . $foreign_key_table_key . ') REFERENCES "' . $schema . '".' . $foreign_key_table1 . '(' . $foreign_key_table_key . ') ON DELETE CASCADE ON UPDATE CASCADE)'; 
				}
				else {

					$createJoinTableQuery = 'CREATE TABLE IF NOT EXISTS "' . $schema . '".' . $join_table_name . ' (' . $tablePrimaryKeyFieldName . ' ' . $primaryKeyTypeSQLString . ', FOREIGN KEY (' . $tablePrimaryKeyFieldName . ') REFERENCES "' . $schema . '".' . $foreign_key_table0 . '(' . $tablePrimaryKeyFieldName . ') ON DELETE CASCADE ON UPDATE CASCADE, ' . $foreign_key_table_key . ' ' . $foreignKey1TypeString  . ', FOREIGN KEY (' . $foreign_key_table_key . ') REFERENCES "' . $schema . '".' . $foreign_key_table1 . '(' . $foreign_key_table_key . ')  ON DELETE CASCADE ON UPDATE CASCADE)'; 
					
					
				}

				$tableGenerationQueries[] = $createJoinTableQuery;
				
			}
		}
		//$createJSONTableQuery = 'CREATE TABLE IF NOT EXISTS ' . $schema . '_json.' . $sanitized_table_name . ' (id SERIAL PRIMARY KEY, ' . $sanitized_table_name . '_id INTEGER, ' . ' json_data jsonb)';
		//$tableGenerationQueries[]= $createJSONTableQuery;
		
		return $tableGenerationQueries;
	}
	
	
	
