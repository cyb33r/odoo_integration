<?php
	session_start();

	//copy the content of the integration.php
	//include "../PhpIntegration/integration_v2.php";
	include "../PhpIntegration/csv_excel_operations.php";
	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
	flush();
	//Initial values of the variables
	$url = "";//"http://192.168.1.215:8069";
	$dbName = "";//"rmx_sandbox";
	$username = "";//"admin";
	$password = "";//"csctest";

	$count = 0;
	$fileName = "";
	$uploadedFile = "";
	$csvDelimeter2 = "";
	$csvEnclosure2 = "";
	$fieldsArray = "";
	$modelsList = array();
	$relatedModels = "";
	$relatedFields = "";
	$headers = array();
	$msg="";
	$fileType = "csv";

	$xmlPath = "../Configuration/config.xml";
	$xml = $xml = new DOMDocument();
	$xml->load($xmlPath);
	$urlNodes = $xml->getElementsByTagName('url');
	$dbNameNodes = $xml->getElementsByTagName('dbname');
	$usernamelNodes = $xml->getElementsByTagName('username');
	$passwordNodes = $xml->getElementsByTagName('password');

	$errorMsg = "";
	$validConfigFile = false;
	$log = "";

	if ($urlNodes->length > 0) 
	{
		foreach($urlNodes as $urlNode)
		{
			$url = $urlNode->nodeValue;
		}
	}
	
	if ($dbNameNodes->length > 0) 
	{
		foreach($dbNameNodes as $dbNameNode)
		{
			 $dbName = $dbNameNode->nodeValue;
		}
	}

	if ($usernamelNodes->length > 0) 
	{
		foreach($usernamelNodes as $usernameNode)
		{
			$username = $usernameNode->nodeValue;
		}
	}

	if ($passwordNodes->length > 0) 
	{
		foreach($passwordNodes as $passwordNode)
		{
			$password = $passwordNode->nodeValue;
		}
	}

	
	//login to odoo
	if(Integration::login($url,$dbName,$username,$password))
	{
		$log .= getDateTime() . "Logged-in to: $dbName as $username" . "\n";
		//triggered when the csv upload is clicked
		if(isset($_POST["submit"]))
		{

			$configFilePath = $_FILES['configFile']['tmp_name'];
			$xml = $xml = new DOMDocument();
			$xml->load($configFilePath);
			$typeNode = $xml->getElementsByTagName("type");
			$type = $typeNode->item(0)->nodeValue;
			$filesNode = $xml->getElementsByTagName('file');
			$filesPath = $_FILES['uploadedFile']['tmp_name'];
			$filesName = $_FILES['uploadedFile']['name'];

			$fileCtr = 0;
			
			foreach($filesNode as $file)
			{
				echo 
					"<script>" .
					/*"$(parent.document).ready(function () {" . */
					"var table = parent.document.getElementById('tableList');" .
					"var cell = table.rows[" . $fileCtr . "].cells[4];" .
					"cell.innerHTML = 'Waiting for process...';" .
					/*"});". */
					"</script>";
				flush();
				$fileCtr++;
			}

			$fileCtr = 0;

			$log .= getDateTime() . "Reading config file" . "\n";
			$log .= getDateTime() . "Starting import" . "\n";
			foreach($filesNode as $file)
			{	
				echo 
					"<script>" .
					/*"$(parent.document).ready(function () {" .*/
					"var table = parent.document.getElementById('tableList');" .
					"var cell = table.rows[" . $fileCtr . "].cells[4];" .
					"cell.innerHTML = '0%';" .
					/*"});". */
					"</script>";

				flush();
				$filePath = $filesPath[$fileCtr];

				$modelName = $file->getElementsByTagName('model_name')->item(0)->nodeValue;
				$modelName = str_replace("_",".",$modelName);
				$firstColIsExtId = $file->getElementsByTagName('first_column_is_external_id')->item(0)->nodeValue;
		
				$relatedModelsNode = $file->getElementsByTagName('related_model');

				$relatedModelCtr = 0;
				$relatedModelList = array();
				foreach ($relatedModelsNode as $relatedModel) 
				{
				
					$relatedModelName = $relatedModel->getElementsByTagName('related_model_name')->item(0)->nodeValue;
					$relatedModelName = str_replace("_",".",$relatedModelName);
					$relatedField = $relatedModel->getElementsByTagName('related_field')->item(0)->nodeValue;
					$isExternalId = $relatedModel->getElementsByTagName('related_field_is_external_id')->item(0)->nodeValue;

					
					$relatedModelList[$relatedModelCtr]["model"] = $relatedModelName;
					$relatedModelList[$relatedModelCtr]["relatedField"] = $relatedField;
					$relatedModelList[$relatedModelCtr]["isExternalId"] = $isExternalId;

					$relatedModelCtr++;

				}

				if(strtolower($type)=="csv")
				{
					$delimeter = $xml->getElementsByTagName('delimeter')->item(0)->nodeValue;
					$enclosure = $xml->getElementsByTagName('enclosure')->item(0)->nodeValue;
					$result = csv_import($filesName,$fileCtr,$modelName,$filePath,$delimeter,$enclosure,$isExternalId,$relatedModelList);
				}
				else
				{
					$result = excel_import($filesName,$fileCtr,$modelName,$filePath,$isExternalId,$relatedModelList);
				}

				$fileCtr++;
			}

			echo 
					"<script>" .
					"var xmlStatus = parent.document.getElementById('xml_status');" .
					"xmlStatus.innerHTML = 'Imports process finished. Refer to the Transation Log for more details.';" .
					"</script>";
			flush();

			$log .= getDateTime() . "Import End" . "\n";
			$_SESSION["trxLog"] = $log;

			/*
			$logFile = fopen("../Transaction Log/trx_log.txt","w");
			fwrite($logFile, $log );
			fclose($logFile);*/
		}
	}
	else
	{
		$log .= getDateTime() . "Failed to login: $dbName as $username" . "\n";
		echo 
					"<script>" .
					"var xmlStatus = parent.document.getElementById('xml_status');" .
					"xmlStatus.innerHTML = 'Failed to log-in! Make sure that the configuration is correct!';" .
					"</script>";
		flush();
		//die("Failed to log-in! Make sure that the configuration is correct!");
	}
	
    function isCsv($fileType)
	{
		if($fileType == "csv")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function initiate_import($filesName,$recordsCount,$records,$fields,$fileIndex,$model,$isFirstColExtId,$relatedModelList)
	{
		global $log;
		$log .= getDateTime() . "Processing " . $filesName[$fileIndex] . "\n";
		echo 
					"<script>" .
					"var xmlStatus = parent.document.getElementById('xml_status');" .
					"xmlStatus.innerHTML = 'Processing imports...';" .
					"</script>";
		flush();

		$statuses = array();
		/* status
		 * 0 - Failed to saved all records
		 * 1 - Sucessfully saved all records
		 * 2 - Some records failed and some records are saved*/
		$failedAll = 0;
		$savedAll = 1;
		$savedSome = 2;

		$status = $failedAll;
		$failedCount = 0;
		$savedCount = 0;
		$lineCount = 0;

		$statusMarker = "#FFFFFF";
		$statusMessage = "";

		$validRecords = true;
		$progressIncrement = 100 / $recordsCount;

		//loop thorugh the records
		for($recordIndex=0; $recordIndex < $recordsCount; $recordIndex++)
		{
			$values = array();
		
			//loop through the fields
			for($fieldsIndex=0; $fieldsIndex < count($fields); $fieldsIndex++)
			{
				//actual record value
				$record = $records[$recordIndex][$fieldsIndex];

				if($record != "")
				{
					//actual field value
					$field = $fields[$fieldsIndex];

		
					//search if it is a related field
					$relatedfieldIndex = Operations::search_related_field($relatedModelList,$field);

					//check if the related field is found
					if($relatedfieldIndex['found'] == true)
					{
					
						//check if the related field is an external id
						if($relatedfieldIndex['isExternalId'] == true)
						{
							$resId = "res_id";
							$relatedModel = Operations::model_irmodeldata;
							$modelField = array($resId);
							$cond = array(array('name', '=', $record));
						}
						else
						{
							//get the value
							$resId = "id";
							$relatedModel = $relatedfieldIndex['model'];
							$modelField = array("id");
							$cond = array(array('id', '=', $record));
						}

						//if the related field is found
						$result = Integration::search_and_read_records($relatedModel,$modelField,$cond,array());
						if(Integration::success_read($result))
						{
							//actual record
							$record = $result[0][$resId];
							$validRecords = true;
						}
						else
						{
							//the record on the related field was not found. Exit
							$log .= getDateTime() . "Related Field not found on record #" . ($recordIndex + 1)  . " of " . $filesName[$fileIndex] . "\n";
							$validRecords = false;
							break;
						}
					}

					//actual values
					$values += [$field => $record];
				}				
			}

			//check if record is valid
			if((bool)$validRecords == true)
			{
				$firstCol = $fields[0];

				//create record for the model
			
				$id = Operations::upsert_record_model($model,$values,$isFirstColExtId);
				
				if(Integration::success($id))
				{
					//create record for the ir_model_data table if the records are external
					if($isFirstColExtId == true)
					{
						//format values
						$relatedValues = array('name' => $records[$recordIndex][0], 'module' => $model , 'model' => $model , 'res_id' => $id);
						$relatedId = Operations::upsert_record_model_data(Operations::model_irmodeldata,$relatedValues,$isFirstColExtId);

						if(Integration::success($relatedId))
						{
							$savedCount++;
						}
						else
						{
							$failedCount++;
						}
					}
					else
					{
						$savedCount++;
					}
				}
				else
				{
					$failedCount++;
				}
			}
			else
			{
				$failedCount++;
			}

			if($recordIndex < $recordsCount)
			{
				$progressStatus += $progressIncrement;
			}
			else
			{
				$progressStatus = 100;
			}

			echo 
				"<script>" .
				"var progressBar = parent.document.getElementById('progress[" . $fileIndex . "]');" .
				"progressBar.value = Math.ceil(" . $progressStatus . ");" .
				"var table = parent.document.getElementById('tableList');" .
				"var cell = table.rows[" . $fileIndex . "].cells[4];" .
				"cell.innerHTML = String(Math.ceil(" . $progressStatus . ")) + '%';" .
				"</script>";

			flush();
		}

		//set the return value to the result of the import
		if($recordsCount == $savedCount)
		{
			$status	= $savedAll;
			$statusMarker = "'rgb(' + 4 + ',' + 255 + ',' + 4 + ')'";
			$statusMessage = "Successfully saved all records!";
			$log .= getDateTime() . "Status: Saved all in " . $filesName[$fileIndex] . "\n";
		}
		else if($recordsCount == $failedCount)
		{
			$status	= $failedAll;
			$statusMarker = "'rgb(' + 255 + ',' + 0 + ',' + 0 + ')'";
			$statusMessage = "All records unsuccessfully saved!";
			$log .= getDateTime() . "Status: Failed all in " . $filesName[$fileIndex] . "\n";
		}
		else if($savedCount > 0 && $failedCount > 0)
		{
			$status = $savedSome;
			$statusMarker = "'rgb(' + 255 + ',' + 255 + ',' + 0 + ')'";
			$statusMessage = "Failed to save some records!";
			$log .= getDateTime() . "Status: Partially failed in " . $filesName[$fileIndex] . "\n";
		}

		$statuses = array('status'=>$status,'status_marker'=>$statusMarker,'status_message'=>$statusMessage);

		return $statuses;
	}

	function getDateTime()
	{
		return  date("m/d/y G:i:s") . ": ";
	}

	function csv_import($filesName,$fileIndex,$model,$uploadedCsvFile,$columnDelimeter,$columnEnclosure,$isFirstColExtId,$relatedModelList)
	{
		$fields = array();
		$records = array();
		$validReadingFile = false;
		$statusMessage = "";
		$statusMarker = "";
		$status= "";

		try
		{
			//open the uploaded csv file
			$csvFile = fopen($uploadedCsvFile,"r");

			//loop through the opened csv file
			while(!feof($csvFile))
			{
				//get the current line record of the csv
				$csvFileValue = fgetcsv($csvFile,0,$columnDelimeter,$columnEnclosure);

				//check if the csv line record is now a blank row
				if(!empty($csvFileValue[0]) && count($csvFileValue) > 1)
				{
					$lineCount++;
					//if first row, assign the $fields for the field name
					//else, assign it to $records as a record
					if($lineCount==1)
					{
						$fields = $csvFileValue;
					}
					else
					{
						array_push($records, $csvFileValue);
					}
				}
			}
			//close the csv file reading
			fclose($csvFile);
			$validReadingFile = true;
		}
		catch(Exception $ex)
		{
			$validReadingFile = false;
		}
		
		if($validReadingFile == true)
		{
			$recordsCount = count($records);

			//check if there are records
			if($recordsCount > 0)
			{
				$importStatus = initiate_import($filesName,$recordsCount,$records,$fields,$fileIndex,$model,$isFirstColExtId,$relatedModelList);
				$status = $importStatus['status'];
				$statusMarker = $importStatus['status_marker'];
				$statusMessage = $importStatus['status_message'];
			}	
			else
			{
				$log .= getDateTime() . "No records detected in the CSV file!" . "\n";
				$statusMessage = "No records detected in the CSV file!";
			}
		}
		else
		{
			$log .= getDateTime() . "An unexpected error occured while opening the CSV file!" . "\n";
			$statusMessage = "An unexpected error occured while opening the CSV file!";
		}

								
		echo 
			"<script type='text/javascript' id='runscript4'>" .
			/*"$(parent.document).ready(function () {" . */
			"var table = parent.document.getElementById('tableList');" .
			"var cell = table.rows[" . $fileIndex . "].cells[4];" .
			"cell.style.backgroundColor = " . $statusMarker . ";" .
			"cell.innerHTML ='" . $statusMessage . "';" .
			/*"});". */
			"</script>";
			flush();

		return $status;
	}

	function excel_import($filesName,$fileIndex,$model,$uploadedExcelFile,$isFirstColExtId,$relatedModelList)
	{
		global $log;
		$fields = array();
		$records = array();
		$validReadingFile = false;
		$statusMessage = "";
		$statusMarker = "";
		$status = "";

		try
		{
			$inputFileType = PHPExcel_IOFactory::identify($uploadedExcelFile);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($uploadedExcelFile);

			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			//loop through the opened exel file
			for($i=1;$i<=count($sheetData);$i++)
			{
				$row = array_values($sheetData[$i]);

				//check if the row have some values
				
				if(!Operations::is_empty_record($row))
				{
					if($i > 1)
					{
						$records[$i-2] = array();
					}

					for($j=0;$j<count($row);$j++)
					{
						$col = $row[$j];
						if($i==1)
						{
							array_push($fields, $col);
						}
						else
						{
							array_push($records[$i-2], $col);
						}
					}
				}
			}
			$validReadingFile = true;
		}
		catch(Exception $ex)
		{
			$validReadingFile = false;
		}

		if($validReadingFile == true)
		{
			$recordsCount = count($records);

			if($recordsCount > 0)
			{
				$importStatus = initiate_import($filesName,$recordsCount,$records,$fields,$fileIndex,$model,$isFirstColExtId,$relatedModelList);
				$status = $importStatus['status'];
				$statusMarker = $importStatus['status_marker'];
				$statusMessage = $importStatus['status_message'];
			}
			else
			{
				$log .= getDateTime() . "No records detected in the Excel file!" . "\n";
				$statusMessage = "No records detected in the Excel file!";
			}
		}
		else
		{
			$log .= getDateTime() . "An unexpected error occured while opening the Excel file!" . "\n";
			$statusMessage = "An unexpected error occured while opening the Excel file!";
		}

		echo 
			"<script>" .
			/*"$(parent.document).ready(function () {" . */
			"var table = parent.document.getElementById('tableList');" .
			"var cell = table.rows[" . $fileIndex . "].cells[4];" .
			"cell.style.backgroundColor = " . $statusMarker . ";" .
			"cell.innerHTML ='" . $statusMessage . "';" .
			/*"});". */
			"</script>";
		flush();

		return $status;
	}
			
?>
