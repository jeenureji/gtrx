<?php
	function removeBOMFromCSV($filepath){
		$content = file_get_contents($filepath); 
		file_put_contents($filepath, str_replace("\xEF\xBB\xBF",'', $content));
	}

	function getDbTableDataFromCSV($pathToCSVFile, $delimter="\t"){
		//var_dump($pathToCSVFile);
		removeBOMFromCSV($pathToCSVFile);
		$result = [];
		$csvFileHandle = fopen($pathToCSVFile, 'r');
		while ($row = fgetcsv($csvFileHandle, 0, $delimter)) {
			$result[] = $row;
		}
		
		return $result;

	}

	function undressVariable($dressedVariable) {
		$pattern = "/^\[(.+)]\$/";
		preg_match($pattern, $dressedVariable, $matches);
		if (count($matches)>1){
			return $matches[1];
		}
		else {
			return NULL;
		}
	}

	function deReferenceVariable($variable, $extractFileCSV, $rowIndex){
		$processedVariable = NULL;
		$undressedVariable = undressVariable($variable);
		if ($undressedVariable != NULL) {
			$processedVariable = $undressedVariable;
		}
		else {
			$processedVariable = $variable;
		}
		
		for ($i=0; $i<count($extractFileCSV[0]); $i++){
			if ($extractFileCSV[0][$i] === $processedVariable) {
				$possibleResult = $extractFileCSV[$rowIndex][$i];
				$undressedPossibleResult = undressVariable($possibleResult);
				if ($undressedPossibleResult != NULL) {
					return deReferenceVariable($undressedPossibleResult, $extractFileCSV, $rowIndex);
				}
				else {
					return $possibleResult;
				}
			}
		}
		return NULL;
	}

	function getIntLinkRefForIntDescriptionRef($interventionDescriptionReference) {
		if (preg_match("/^\[int_description_(\d+)\]$/", $interventionDescriptionReference, $match)){
			return 'int_link_' . $match[1];
		}
		return null;
	}

	function generateETLCSVHeaderFromExtract($extractFileCSVData) {
		$etlCSVHeader = [];
		

		return $etlCSVHeader;
	}

	//NB, make sure that the header is in the same order as the cases in generateETLCSVRowFromExtract
	function generateETLCSVFromExtract($extractFileCSVData, $gtrxMetaDataFieldsCSVData, $omimOrphaToHPOCSVData) {
		$result = [];
		for ($i=1; $i<count($extractFileCSVData); $i++){
			$result[] = generateETLCSVRowDataFromExtract($extractFileCSVData, $gtrxMetaDataFieldsCSVData, $omimOrphaToHPOCSVData, $i);

			
		}
		//print_r ($result));
		//print(json_last_error_msg());
		return convertETLDataArrayToCSVString($result);
	}

	function convertETLDataArrayToCSVString($etlCSVDataArray) {
		$csvHeader = '';
		$csvData = '';
		$headerArray=[];
		foreach($etlCSVDataArray[0] as $etlCSVHeader => $etlCSVData) {
			$headerArray[] = $etlCSVHeader;
			$csvHeader = $csvHeader . $etlCSVHeader;
			if ($etlCSVHeader !== array_key_last($etlCSVDataArray[0])){
				$csvHeader = $csvHeader . "\t";
			}
		}

		for ($i=0; $i<count($etlCSVDataArray); $i++) {
			for ($j=0; $j<count($headerArray); $j++) {	
				if (is_array($etlCSVDataArray[$i][$headerArray[$j]])) {
					$etlCSVDatumArray = [];
					foreach ($etlCSVDataArray[$i][$headerArray[$j]] as $etlCSVDatum) {
						$etlCSVDatumArray[] = $etlCSVDatum;
					}
					$etlCSVDataJSON = json_encode($etlCSVDatumArray, JSON_INVALID_UTF8_IGNORE);
					$csvData = $csvData . $etlCSVDataJSON;
					if ($j<count($headerArray)-1){
						$csvData = $csvData . "\t";
					}
				}
				else {
					$csvData = $csvData . $etlCSVDataArray[$i][$headerArray[$j]];
					if ($j<count($headerArray)-1){
						$csvData = $csvData  . "\t";
					}
				}
			}
			
			if ($i < count($etlCSVDataArray)-1) {
				$csvData = $csvData . "\n";
			}
		}
		return $csvHeader . "\n" . $csvData;
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

	function getWorksheetHeaderIndex($worksheetData, $columnHeader){
		for ($i=0; $i<count($worksheetData[0]); $i++){
			if (strtolower($worksheetData[0][$i]) == strtolower($columnHeader)) {
				return $i;
			}
		}
		return NULL;
	}

	function getOtherCSVInfoInfo($dataDictionaryData, $dataHeaders) {
		$headerIndexes = [];
		foreach ($dataHeaders as $dataHeader) {
			$headerIndex = getWorksheetHeaderIndex($dataDictionaryData, $dataHeader);
			if ($headerIndex === null){
				print_r("[" . date("Y-m-d H:i:s") . "] Error: Missing " . $dataHeader . " column in data dictionary csv file\n");
				exit;
			}

			$headerIndexes[$dataHeader] = $headerIndex;
		}
		

		$dataDictionaryInfo = [];

		for ($i=1; $i<count($dataDictionaryData); $i++)  {
			$dataDictionaryRowDatum = [];
			foreach ($headerIndexes as $header => $headerIndex) {
				$dataDictionaryRowDatum[$header] = $dataDictionaryData[$i][$headerIndex];
			}

			$dataDictionaryInfo[] = $dataDictionaryRowDatum;
			
		}
		return $dataDictionaryInfo;
	}

	function getRecord($records, $key, $value){
		foreach ($records as $record) {
			if (array_key_exists($key, $record) && strtolower($value) == strtolower($record[$key])){
				return $record;
			}
		}
		return null;
	}

	function generateETLCSVRowDataFromExtract($extractFileCSVData, $gtrxMetaDataFieldsCSVData, $omimOrphaToHPOCSVData, $rowIndex){
		$etlCSVDataArray = [];

		$gtrxMetaDataFieldsHeaders = ['record_id', 'nord_link', 'incidence_link', 'gene_location', 'gene_name', 'sequence_viewer_link', 'ncbi_gene_link', 'gene_symbol'];
		$gtrxMetaDataFieldsData = getOtherCSVInfoInfo($gtrxMetaDataFieldsCSVData, $gtrxMetaDataFieldsHeaders);
		
		/*$omimToGhrHeaders = ['condition_id', 'omim_id', 'ghr_name', 'ghr_description', 'ghr_page', 'pattern_of_inheritance', 'ghr_frequency_link', 'ghr_frequency_text'];
		$omimToGhrData = getOtherCSVInfoInfo($omimToGhrCSVData, $omimToGhrHeaders);

		$omimToGardMondoHeaders = ['omim_id', 'gard_id', 'gard_number', 'mondo_link'];
		$omimToGardMondoData = getOtherCSVInfoInfo($omimToGardMondoCSVData, $omimToGardMondoHeaders);

		$gardIndexHeaders = ['gard_id','gard_link', 'cond_name'];
		$gardIndexData = getOtherCSVInfoInfo($gardIndexCSVData, $gardIndexHeaders);

		$orphanetToOmimHeaders = ['ordo_id', 'ordo_name', 'omim_id'];
		$orphanetToOmimData = getOtherCSVInfoInfo($orphanetToOmimCSVData, $orphanetToOmimHeaders);*/

		$omimOrphaToHpoHeaders = ['condition_id', 'qualifier', 'hpo_id', 'frequency', 'aspect', 'value'];
		$omimOrphaToHpoData = getOtherCSVInfoInfo($omimOrphaToHPOCSVData, $omimOrphaToHpoHeaders);

		for ($i=0; $i<count($extractFileCSVData[0]); $i++){
			switch (strtolower($extractFileCSVData[0][$i])) {
				case 'record_id':
				case 'collapse_group_number':
				case 'emergency_note_yn':
				case 'emergency_note':
				case 'pattern_of_inheritance':
				case 'pattern_of_inheritance2':
				case 'subspecialist_yn':
				case 'subspecialist':
				case 'other_subspecialist':
				case 'split_1_yn':
				case 'dx_subcat_1':
				case 'dx_subcat_2':
				case 'db_hgnc_gene_id':
				case 'db_hgnc_gene_symbol':
				case 'freq_per_birth':
				case 'freq_per_birth2':
				case 'rcigm_clinical_summary':
				case 'rcigm_clinical_summary2':
				case 'review_dx_name_2':
					$dataValue = trim($extractFileCSVData[$rowIndex][$i]);
					$etlCSVDataArray[$extractFileCSVData[0][$i]] = $dataValue;

					if (strtolower($extractFileCSVData[0][$i]) === 'record_id'){
						$recordIdArray = explode("-", $dataValue);
						$recordId = $recordIdArray[count($recordIdArray)-1];
						
						$gtrxMetaDataFieldsMatchFlag=false;
						foreach($gtrxMetaDataFieldsData as $gtrxMetaDataFieldsDataRow) {
							if (strtolower($gtrxMetaDataFieldsDataRow['record_id']) === strtolower($dataValue)){
								$gtrxMetaDataFieldsMatchFlag=true;
								foreach($gtrxMetaDataFieldsDataRow as $gtrxMetaDataFieldsDataRowKey => $gtrxMetaDataFieldsDataRowValue){
									if ($gtrxMetaDataFieldsDataRowKey!=='record_id'){
										$etlCSVDataArray[$gtrxMetaDataFieldsDataRowKey] = $gtrxMetaDataFieldsDataRowValue;
									}
								}
							}
						}
						if (!$gtrxMetaDataFieldsMatchFlag) {
							foreach($gtrxMetaDataFieldsHeaders as $gtrxMetaDataFieldsHeader){
								if ($gtrxMetaDataFieldsHeader!=='record_id'){
									$etlCSVDataArray[$gtrxMetaDataFieldsHeader]="";
								}
							}
						}

						/*$omimToGhrMatchFlag=false;
						foreach($omimToGhrData as $omimToGhrDataRow) {
							if (strtolower($omimToGhrDataRow['condition_id']) === strtolower($recordId)){
								$omimToGhrMatchFlag=true;
								foreach($omimToGhrDataRow as $omimToGhrDataRowKey => $omimToGhrDataRowValue){
									$etlCSVDataArray[$omimToGhrDataRowKey] = $omimToGhrDataRowValue;
								}
							}
						}
						if (!$omimToGhrMatchFlag) {
							foreach($omimToGhrHeaders as $omimToGhrHeader){
								$etlCSVDataArray[$omimToGhrHeader]="";
							}
						}*/

						$hpoData = [];
						foreach($omimOrphaToHpoData as $omimOrphaToHpoDataRow) {
							if (strtolower($omimOrphaToHpoDataRow['condition_id']) === strtolower($recordId)){
								$hpoDatum = [];
								foreach($omimOrphaToHpoDataRow as $omimOrphaToHpoDataRowKey => $omimOrphaToHpoDataRowValue){
									if (strToLower($omimOrphaToHpoDataRowKey) !== 'condition_id') {
										$hpoDatum[$omimOrphaToHpoDataRowKey] = $omimOrphaToHpoDataRowValue;
									}
									
								}
								$hpoData[] = $hpoDatum;
							}
						}
						$etlCSVDataArray['hpo_data'] = $hpoData;

						/*$omimToGardMondoMatchFlag=false;
						foreach($omimToGardMondoData as $omimToGardMondoDataRow) {
							if (strtolower($omimToGardMondoDataRow['omim_id']) === strtolower($recordId)){
								$omimToGardMondoMatchFlag=true;
								foreach($omimToGardMondoDataRow as $omimToGardMondoDataRowKey => $omimToGardMondoDataRowValue){
									if (strtolower($omimToGardMondoDataRowKey) != 'omim_id'){
										$etlCSVDataArray[$omimToGardMondoDataRowKey] = $omimToGardMondoDataRowValue;
									}
									//I was hoping to get to the gard_index from the omim_to_gard_mondo using the gard_id field,
									//but the omim_id filed in here does not have all entries in omim_to_GHR
									
									if (strtolower($omimToGardMondoDataRowKey) == 'gard_number'){
										$gardInfo = getRecord($gardIndexData, 'gard_id', $omimToGardMondoDataRowValue);
										if ($gardInfo!=null){
											foreach ($gardInfo as $gardInfoKey => $gardInfoValue){
												if ($gardInfoKey != 'gard_id'){
													$etlCSVDataArray[$gardInfoKey]=$gardInfoValue;
												}
											}
										}
										else {
											foreach($gardIndexHeaders as $gardIndexHeader){
												if ($gardIndexHeader!='gard_id'){
													$etlCSVDataArray[$gardIndexHeader]="";
												}
											}
										}
									}
								}
							}
						}
						if (!$omimToGardMondoMatchFlag){
							foreach($omimToGardMondoHeaders as $omimToGardMondoHeader){
								if (strtolower($omimToGardMondoHeader)!='omim_id') {
									$etlCSVDataArray[$omimToGardMondoHeader]="";
								}
								if (strtolower($omimToGardMondoHeader)!='gard_number') {
									foreach($gardIndexHeaders as $gardIndexHeader){
										if ($gardIndexHeader!='gard_id'){
											$etlCSVDataArray[$gardIndexHeader]="";
										}
									}
								}
							}
						}
						

						$omimIdArray = explode(":", $recordId);
						$pureOmimId = $omimIdArray[count($omimIdArray)-1];
						$orphanetToOmimMatchFlag=false;
						foreach($orphanetToOmimData as $orphanetToOmimDataRow) {
							if (strtolower($orphanetToOmimDataRow['omim_id']) === strtolower($pureOmimId)){
								$orphanetToOmimMatchFlag=true;
								foreach($orphanetToOmimDataRow as $orphanetToOmimDataRowKey => $orphanetToOmimDataRowValue){
									if (strtolower($orphanetToOmimDataRowKey)!='omim_id'){
										$etlCSVDataArray[$orphanetToOmimDataRowKey] = $orphanetToOmimDataRowValue;
									}
								}
							}
						}
						if (!$orphanetToOmimMatchFlag){
							foreach($orphanetToOmimHeaders as $orphanetToOmimHeader){
								if ($orphanetToOmimHeader!='omim_id') {
									$etlCSVDataArray[$orphanetToOmimHeader]="";
								}
							}
						}*/
					}
					break;
				case 'condition_name':
				case 'condition_name_1':
				case 'condition_name_2':
				case 'condition_name_3':
				case 'condition_name_4':
				case 'condition_name_5':
				case 'condition_name_6':
				case 'condition_name_7':
				case 'condition_name_8':
				case 'condition_name_9':
				case 'condition_name_10':
					$explodedValue = explode(';', $extractFileCSVData[$rowIndex][$i]);
					if (count($explodedValue) >= 1) {
						$etlCSVDataArray[$extractFileCSVData[0][$i]] = trim($explodedValue[0]);
					}

					if (count($explodedValue) > 1) {
						$etlCSVDataArray[$extractFileCSVData[0][$i] . '_abbreviation'] = trim($explodedValue[1]);
					}
					else {
						$etlCSVDataArray[$extractFileCSVData[0][$i] . '_abbreviation'] = '';
					}

					/*if (strtolower($extractFileCSVData[0][$i]) === 'condition_name') {
						$gardIndexMatchFlag=false;
						foreach($gardIndexData as $gardIndexDataRow) {
							if (strtolower($gardIndexDataRow['cond_name']) === strtolower(trim($explodedValue[0]))){
								$gardIndexMatchFlag=true;
								foreach($gardIndexDataRow as $gardIndexDataRowKey => $gardIndexDataRowValue){
									if ($gardIndexDataRowKey!='gard_id'){
										$etlCSVDataArray[$gardIndexDataRowKey] = $gardIndexDataRowValue;
									}
								}
							}
						}
						if (!$gardIndexMatchFlag){
							foreach($gardIndexHeaders as $gardIndexHeader){
								if ($gardIndexHeader!='gard_id'){
									$etlCSVDataArray[$gardIndexHeader]="";
								}
							}
						}
					}*/
					break;

				case 'group_1_diseases':
				case 'group_2_diseases':
				case 'group_3_diseases':
					$derefencedValue = NULL;
					$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
					for ($j=0; $j<count($referenceVariables); $j++){
						$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
						if ($currentDereferencedValue != NULL) {
							if ($derefencedValue == NULL) {
								$derefencedValue = $currentDereferencedValue;
							}
							else {
								$derefencedValue =  $derefencedValue . $currentDereferencedValue;
								if ($j < count($referenceVariables)-1) {
									$derefencedValue =  $derefencedValue . ", ";
								}
							}
							
						}
					}
					
					if ($derefencedValue == NULL) {
						$etlCSVDataArray[$extractFileCSVData[0][$i]] = "";
						$etlCSVDataArray[$extractFileCSVData[0][$i] . '_abbreviation'] = "";
					}
					else {
						$explodedDereferencedValue = explode(';', $derefencedValue);
						if (count($explodedDereferencedValue) >= 1) {
							$etlCSVDataArray[$extractFileCSVData[0][$i]] = trim($explodedDereferencedValue[0]);
						}

						if (count($explodedDereferencedValue) > 1) {
							$etlCSVDataArray[$extractFileCSVData[0][$i] . '_abbreviation'] = trim($explodedDereferencedValue[1]);
						}
						else {
							$etlCSVDataArray[$extractFileCSVData[0][$i] . '_abbreviation'] = '';
						}
					}
					
					break;
			}

			if (preg_match("/^add_int_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "add_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][30+$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][30+$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][30+$match[1]])){
					$etlCSVDataArray['intervention_data'][30+$match[1]]['redcap_dump_index'] = 30+$match[1];
				}
			}

			if (preg_match("/^add_int_description_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "add_int_description";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][30+$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][30+$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][30+$match[1]])){
					$etlCSVDataArray['intervention_data'][30+$match[1]]['redcap_dump_index'] = 30+$match[1];
				}
			}

			if (preg_match("/^priority_class_drug(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "priority_class_drug";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^level2_group(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "intervention_group";
				$interventionLink = NULL;
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					
					if ($j==0) {
						$intLinkRef = getIntLinkRefForIntDescriptionRef($referenceVariables[$j]);
						$interventionLink = deReferenceVariable($intLinkRef, $extractFileCSVData, $rowIndex);
						if ($interventionLink == null){
							$interventionLink = '';
						}
					}
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
				$etlCSVDataArray['intervention_data'][$match[1]]['int_link'] = $interventionLink;
			}

			/*if (preg_match("/^int_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "int_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}*/

			if (preg_match("/^comment_intgroup_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "comment_intgroup";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^rev1_comm_intgroup_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "rev1_comm_intgroup";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = str_replace("\"","'", trim($extractFileCSVData[$rowIndex][$i]));
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	str_replace("\"","'", trim($derefencedValue));
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^subcat_choice_int_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "subcat_choice_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^timeframe_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "timeframe_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^timeframe_subcat1_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "timeframe_subcat1_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^timeframe_subcat2_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "timeframe_subcat2_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^age_use_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "age_use_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^contra_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "contra_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^contra_group_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "contra_group_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^rev1_eff_reclass_drug(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "rev1_eff_reclass_drug";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^qualscale_reclass_drug(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "qualscale_reclass_drug";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^evidence_note_yn_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "evidence_note_yn_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^evidence_comment_int(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "evidence_comment_int";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = str_replace("\"","'", trim($extractFileCSVData[$rowIndex][$i]));
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	str_replace("\"","'", trim($derefencedValue));
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^use_group_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "use_group";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['intervention_data'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['intervention_data'][$match[1]])){
					$etlCSVDataArray['intervention_data'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_omim_dx_name_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_omim_dx_name";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_omim_dx'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_omim_dx'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_omim_dx'][$match[1]])){
					$etlCSVDataArray['db_omim_dx'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_omim_id_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_omim_id";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_omim_dx'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_omim_dx'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_omim_dx'][$match[1]])){
					$etlCSVDataArray['db_omim_dx'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_omim_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_omim_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_omim_dx'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_omim_dx'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_omim_dx'][$match[1]])){
					$etlCSVDataArray['db_omim_dx'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_orphanet_dx_id_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_orphanet_dx_id";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_orphanet_dx'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_orphanet_dx'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_orphanet_dx'][$match[1]])){
					$etlCSVDataArray['db_orphanet_dx'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_orphanet_dx_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_orphanet_dx_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_orphanet_dx'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_orphanet_dx'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_orphanet_dx'][$match[1]])){
					$etlCSVDataArray['db_orphanet_dx'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_genereviews_name_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_genereviews_name";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_gene_reviews'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_gene_reviews'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_gene_reviews'][$match[1]])){
					$etlCSVDataArray['db_gene_reviews'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_genereviews_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_genereviews_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_gene_reviews'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_gene_reviews'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_gene_reviews'][$match[1]])){
					$etlCSVDataArray['db_gene_reviews'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_ghr_name_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_ghr_name";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_ghr'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_ghr'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_ghr'][$match[1]])){
					$etlCSVDataArray['db_ghr'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_ghr_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_ghr_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_ghr'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_ghr'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_ghr'][$match[1]])){
					$etlCSVDataArray['db_ghr'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_gard_disease_id_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_gard_disease_id";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_gard_disease'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_gard_disease'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_gard_disease'][$match[1]])){
					$etlCSVDataArray['db_gard_disease'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^db_gard_disease_id_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "db_gard_disease_id_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['db_gard_disease'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['db_gard_disease'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['db_gard_disease'][$match[1]])){
					$etlCSVDataArray['db_gard_disease'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_date_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid_date";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_notes_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid_notes";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_journal_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid_journal";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_pubmed_link_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid_pubmed_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}

			if (preg_match("/^pmid_title_(\d+)$/", $extractFileCSVData[0][$i], $match)){
				$derefencedValue = NULL;
				$jsonField = "pmid_title_link";
				$referenceVariables = explode(',', $extractFileCSVData[$rowIndex][$i]);
				for ($j=0; $j<count($referenceVariables); $j++){
					$currentDereferencedValue = deReferenceVariable($referenceVariables[$j], $extractFileCSVData, $rowIndex);
					if ($currentDereferencedValue != NULL) {
						if ($derefencedValue == NULL) {
							$derefencedValue = $currentDereferencedValue;
						}
						else {
							$derefencedValue =  $derefencedValue . $currentDereferencedValue;
							
						}
						if ($j < count($referenceVariables)-1) {
							$derefencedValue =  $derefencedValue . ", ";
						}
					}
				}
				if ($derefencedValue == NULL) {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] = trim($extractFileCSVData[$rowIndex][$i]);
				}
				else {
					$etlCSVDataArray['pubmed_publications'][$match[1]][$jsonField] =	trim($derefencedValue);
				}

				if (!array_key_exists('redcap_dump_index', $etlCSVDataArray['pubmed_publications'][$match[1]])){
					$etlCSVDataArray['pubmed_publications'][$match[1]]['redcap_dump_index'] = $match[1];
				}
			}
		}
		
		return $etlCSVDataArray;
	}