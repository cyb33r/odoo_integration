<?php

			//copy the content of the integration.php
			//include "../PhpIntegration/integration_v2.php";
			include "../PhpIntegration/csv_excel_operations.php";

			//set the errors to display in the browser
			ini_set('display_startup_errors',1);
			ini_set('display_errors',1);

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
				//triggered when the csv upload is clicked
				if(isset($_POST["submit"]))
				{
					$timeStart = microtime(true);

					$configFilePath = $_FILES['configFile']['tmp_name'];
					$xml = $xml = new DOMDocument();
					$xml->load($configFilePath);
					$typeNode = $xml->getElementsByTagName("type");
					$type = $typeNode->item(0)->nodeValue;
					$filesNode = $xml->getElementsByTagName('file');
					$filesPath = $_FILES['uploadedFile']['tmp_name'];

					$fileCtr = 0;
					foreach($filesNode as $file)
					{	
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
							$result = csv_import($fileCtr,$modelName,$filePath,$delimeter,$enclosure,$isExternalId,$relatedModelList);

							echo "<script type='text/javascript'>alert('atayoo na');</script>";

							if($result == 0)
							{
								//echo "Failed to save all records";
								
							}
							else if($result == 1)
							{
								//echo "Successfully saved all records";
							}
							else
							{
								//echo "Some records of fileName failed to save in ";
							}	
						}
						else
						{

						}

						$fileCtr++;
					}

					/*
					if($fileName!="")
					{
						$fileType = $_POST["fileType"];
						$uploadedFile = $_FILES['uploadedFile']['tmp_name'];
						$continueImport = true;

						if($fileType == "csv")
						{
							//check if the number of lines is more than 1
							if(!(count(file($uploadedFile,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) > 1))
							{
								$continueImport = false;
							}
						}

						if($continueImport)
						{
							$relatedModelList = array();

							if(isset($_POST["relatedModels"]))
							{
								$relatedModels = $_POST["relatedModels"];
								$relatedFields = $_POST["relatedFields"];
								$relatedModelsCount = count($relatedModels);
								
								if(isset($_POST["isExternalIds"]))
								{
									$isExternalIdFields = $_POST["isExternalIds"];
								}

								for($i=0;$i<$relatedModelsCount;$i++)
								{
									$isExternalId = false;
									if(!empty($isExternalIdFields))
									{
										if(in_array($i, $isExternalIdFields))
										{
											$isExternalId = true;
										}
									}

									$relatedModel = $relatedModels[$i];
									$relatedField = $relatedFields[$i];
									$relatedModelList[$i]["model"] = $relatedModel;
									$relatedModelList[$i]["relatedField"] = $relatedField;
									$relatedModelList[$i]["isExternalId"] = $isExternalId;
								}
							}
							
							if(isset($_POST["isFirstColExternalId"]))
							{
								$isFirstColExternalId = true;
							}
							else
							{
								$isFirstColExternalId = false;
							}

							
							if(isCsv($fileType))
							{
								$csvDelimeter2 = $_POST["delimeters2"];
								$csvEnclosure2 = $_POST["enclosures2"];

								if($csvEnclosure2 == "")
								{
									$csvEnclosure2 = '"';
								}

								//$result = csv_import($model,$uploadedFile,$csvDelimeter2,$csvEnclosure2,$isFirstColExternalId,$relatedModelList);
							}
							else
							{
								//$result = excel_import($model,$uploadedFile,$isFirstColExternalId,$relatedModelList);
							}
					
							if($result == 0)
							{
								$msg = "Failed to save all records of $fileName to " . str_replace(".", "_", $model);//$model!";
								
							}
							else if($result == 1)
							{
								$msg = "Successfully saved all records of $fileName to " . str_replace(".", "_", $model);
							}
							else
							{
								$msg = "Some records of $fileName failed to save in " . str_replace(".", "_", $model);
							}	
						}
						else
						{
							//invalid file, no rows
						}						
					}
					else
					{
						$msg = "No CSV file uploaded!";
					}


					$timeEnd = microtime(true);
					$exactTime = $timeEnd - $timeStart;
					$totalTime = $exactTime;

					$hours = 0;
  					$milliseconds = str_replace( "0.", '', $totalTime - floor( $totalTime ) );

					if ( $totalTime > 3600 )
					{
						$hours = floor( $totalTime / 3600 );
					}

				    $totalTime = $totalTime % 3600;


				    $formattedTime = str_pad( $hours, 2, '0', STR_PAD_LEFT ). gmdate( ':i:s', $totalTime ). ($milliseconds ? ".$milliseconds" : '');
	
					echo "Total elapsed time: = $formattedTime";
					echo "<br>";*/
					//echo $exactTime;
				}
			}
			else
			{
				die("Failed to log-in! Make sure that the configuration is correct!");
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

			function csv_import($fileIndex,$model,$uploadedCsvFile,$columnDelimeter,$columnEnclosure,$isFirstColExtId,$relatedModelList)
			{
				/* status
				 * 0 - Failed to saved all records
				 * 1 - Sucessfully saved all records
				 * 2 - Some records failed and some records are saved*/
				$failedAll = 0;
				$savedAll = 1;
				$savedSome = 2;

				$status = $failedAll;
				

				$fields = array();
				$records = array();
				//open the uploaded csv file
				$csvFile = fopen($uploadedCsvFile,"r");
				$lineCount = 0;
				$failedCount = 0;
				$savedCount = 0;

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

				$recordsCount = count($records);

				//check if there are records
				if($recordsCount > 0)
				{
					$validRecords = true;
					$progressIncrement = (int)ceil($recordsCount / 100);

					//loop thorugh the records
					for($recordIndex=0; $recordIndex < $recordsCount; $recordIndex++)
					{
						$values = array();
						
						//loop through the fields
						for($fieldsIndex=0; $fieldsIndex < count($fields); $fieldsIndex++)
						{
							$gotoProcess = true;

							//execute formatting the values
							if($gotoProcess == true)
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
										/*
										echo "<pre>";
										print_r($relatedfieldIndex);
										echo "</pre>";*/
									
										//check if the related field is an external id
										if($relatedfieldIndex['isExternalId'] == true)
										{
											echo "related is external";
											//echo "yeah";
											//get the values
											$resId = "res_id";
											$modelField = array($resId);
											$cond = array(array('name', '=', $record));
											$result = Integration::search_and_read_records(Operations::model_irmodeldata,$modelField,$cond,array());
											
											//if the related field is found
											if(Integration::success_read($result))
											{
												//actual record
												$record = $result[0][$resId];
											}
											else
											{
												//the record on the related field was not found. Exit
												$validRecords = false;
												break;
											}
										}
										else
										{
											echo "related is NOT external";
											//get the value
											$relatedModel = $relatedfieldIndex['model'];
											$modelField = array("id");
											$cond = array(array('id', '=', $record));

											//if the related field is found
											$result = Integration::search_and_read_records($relatedModel,$modelField,$cond,array());
											if(Integration::success_read($result))
											{
												//actual record
												$record = $result[0]["id"];
											}
											else
											{
												//the record on the related field was not found. Exit
												$validRecords = false;
												break;
											}
										}
									}	
									//actual values
									$values += [$field => $record];
								}
							}				
						}

						//check if record is valid
						if($validRecords == true)
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
					}

					//set the return value to the result of the import
					if($recordsCount == $savedCount)
					{
						$status	= $savedAll;
					}
					else if($recordsCount == $failedCount)
					{
						$status	= $failedAll;
					}
					else if($savedCount > 0 && $failedCount > 0)
					{
						$status = $savedSome;
					}
				}	

				echo "status = $status";

				return $status;
			}

			
		?>
