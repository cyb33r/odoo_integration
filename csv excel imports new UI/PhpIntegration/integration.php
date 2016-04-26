<?php
	session_start();
	require_once "../Xmlrpc/ripcord.php";
	require_once "../PhpExcel/PHPExcel.php";

    class Integration
	{
		################# DECLARATIONS #################

		private static $url;
		private static $dbName;
	    private static $username;
		private static $password;
		private static $uid;
		private static $odooModels;
		const model_irmodeldata = "ir.model.data";
	
		################# PUBLIC METHODS #################
		
		public static function login($url,$dbName,$username,$password)
		{
			$result = false;
			self::$url = $url;
			self::$dbName = $dbName;
			self::$username = $username;
			self::$password = $password;
			
			$common = ripcord::client($url . "/xmlrpc/2/common");
			$common->version();
			self::$uid = $common->authenticate($dbName, $username , $password, array());
			self::$odooModels = ripcord::client($url . "/xmlrpc/2/object");

			if(self::success(self::$uid))
			{
				$result = true;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		public static function readable($model)
		{
			$readable = self::execute_method($model,'check_access_rights',array('read'),array('raise_exception'=>0));

			if($readable == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}	

		public static function writeable($model)
		{
			$writeable = self::execute_method($model,'check_access_rights',array('write'),array('raise_exception'=>0));

			if($writeable == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}	

		public static function search_record($model,$condition,$mapping)
		{
			return self::execute_method($model,'search',array($condition),$mapping);
		}	
				
		public static function read_record($model,$fields,$condition,$mapping)
		{
			$id = self::search_record($model,$condition,$mapping);
			return self::execute_method($model,'read',array($id),array('fields'=>$fields));	 
		}

		public static function model_fields_list($model)
		{
			return self::execute_method($model,'fields_get',array(),array('attributes' => array('string', 'help', 'type')));
		}
		
		public static function search_and_read_records($model,$fields,$condition,$mapping)
		{
			$fieldsArray = array();
			array_push($fieldsArray,array('fields'=>$fields));
			$condAndMapArray = array_merge($fieldsArray[0],$mapping);
			
			return self::execute_method($model,'search_read',array($condition),$condAndMapArray,array());
		}

		public static function create_record($model,$values)
		{
			return self::execute_method($model,'create',array($values),array());
		}
		
		public static function update_record($model,$id,$values)
		{
			$paramsArray = array();			
			if(is_array($id))
			{
				array_push($paramsArray,$id);
			}
			else
			{
				array_push($paramsArray,array($id));
			}
			
			array_push($paramsArray,$values);
			return self::execute_method($model,'write',$paramsArray,array());
		}
		
		
		public static function upsert_record_model($model,$values,$isFirstColExtId)
		{
			$result = 0;
			$firstCol = array_keys($values)[0];
			$value = $values[$firstCol];

			unset($values[$firstCol]);

			if($isFirstColExtId===true)
			{

				$condition = array(array('name','=',$value));
				$searchResult = self::search_and_read_records(self::model_irmodeldata,array('res_id'),$condition,array());

				if(self::success_read($searchResult))
				{
					$resId = $searchResult[0]['res_id'];
					$uid = self::update_record($model,$resId,$values);
					if(self::success($uid))
					{
						$result = $resId;
					}
					else
					{
						$result = null;
					}
				}
				else
				{	
					$genId = self::create_record($model,$values);
					if(self::success($genId))
					{
						$result = $genId;
					}
					else
					{
						$result = null;
					}
				}
			}
			else
			{
				if($firstCol == 'id')
				{
					$condition = array(array($firstCol,'=',$value));
					$searchResult = self::search_and_read_records($model,array('id'),$condition,array());
					$id = $searchResult[0]['id'];

					if(self::success_read($searchResult))
					{
						$uid = self::update_record($model,$id,$values);
						if(self::success($uid))
						{
							$result = $id;
						}
						else
						{
							$result = null;
						}
					}
					else
					{	
						$genId = self::create_record($model,$values);
						if(self::success($genId))
						{
							$result = $genId;
						}
						else
						{
							$result = null;
						}
					}
				}
			}

			return $result;
		}

		public static function upsert_record_model_data($model,$values,$isFirstColExtId)
		{
			$result = 0;
			$firstCol = array_keys($values)[0];
			$value = $values[$firstCol];

			$condition = array(array('name','=',$value));
			$searchResult = self::search_and_read_records($model,array('id'),$condition,array());

			if(self::success_read($searchResult))
			{
				$id = $searchResult[0]['id'];
				$uid = self::update_record($model,$id,$values);
				if(self::success($uid))
				{
					$result = $id;
				}
				else
				{
					$result = null;
				}
			}
			else
			{	
				$genId = self::create_record($model,$values);
				if(self::success($genId))
				{
					$result = $genId ;
				}
				else
				{
					$result = null;
				}
			}

			return $result;
		}


		public static function get_updated_code($model,$id)
		{
			return self::execute_method($model,'name_get',array(array($id)),array());
		}

		public static function delete_record($model,$id)
		{
			$paramsArray = array();	
			if(is_array($id))
			{
				array_push($paramsArray,$id);
			}
			else
			{
				array_push($paramsArray,array($id));
			}
			
			return self::execute_method($model,'unlink',$paramsArray,array());
		}

		public static function success($id)
		{
			$result = false;
			if(is_array($id))
			{
				$result = false;
			}
			else
			{
				if($id > 0)
				{
					$result = true;
				}
				else
				{
					$result = false;
				}
			}

			return $result;
		}

		public static function success_read($result)
		{
			if(!empty($result))
			{
				return true;
			}
			else
			{
				return false;
			}
		}


		public static function csv_import($model,$uploadedCsvFile,$columnDelimeter,$columnEnclosure,$isFirstColExtId,$relatedModelList)
		{
			$_SESSION["trxLog"] = $_SESSION["trxLog"] . getDateTime() . "Processing " . "\n";
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
			$validReadingFile = false;

			try
			{
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
					$validRecords = true;

					//loop thorugh the records
					for($recordIndex=0; $recordIndex < $recordsCount; $recordIndex++)
					{
						$values = array();
						
						//loop through the fields
						for($fieldsIndex=0; $fieldsIndex < count($fields); $fieldsIndex++)
						{
							$gotoProcess = true;

							//execute formatting the values
							if($gotoProcess === true)
							{
								//actual record value
								$record = $records[$recordIndex][$fieldsIndex];

								if($record != "")
								{
									//actual field value
									$field = $fields[$fieldsIndex];

									//search if it is a related field
									$relatedfieldIndex = self::search_related_field($relatedModelList,$field);

									//check if the related field is found
									if($relatedfieldIndex['found'] === true)
									{
										//check if the related field is an external id
										if($relatedfieldIndex['isExternalId'] === true)
										{
											//get the values
											$resId = "res_id";
											$modelField = array($resId);
											$cond = array(array('name', '=', $record));
											$result = self::search_and_read_records(self::model_irmodeldata,$modelField,$cond,array());
											
											//if the related field is found
											if(self::success_read($result))
											{
												//actual record
												$record = $result[0][$resId];
											}
											else
											{
												//the record on the related field was not found. Exit
												$validRecords = false;
												$_SESSION["trxLog"] = $_SESSION["trxLog"] . getDateTime() . "Related Field not found on record #" . ($recordIndex + 1) . "\n";
												break;
											}
										}
										else
										{
											//get the value
											$relatedModel = $relatedfieldIndex['model'];
											$modelField = array("id");
											$cond = array(array('id', '=', $record));

											//if the related field is found
											$result = self::search_and_read_records($relatedModel,$modelField,$cond,array());
											if(self::success_read($result))
											{
												//actual record
												$record = $result[0]["id"];
											}
											else
											{
												//the record on the related field was not found. Exit
												$validRecords = false;
												$_SESSION["trxLog"] = $_SESSION["trxLog"] . getDateTime() . "Related Field not found on record #" . ($recordIndex + 1) . "\n";
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
						if($validRecords === true)
						{
							$firstCol = $fields[0];

							//create record for the model
							$id = self::upsert_record_model($model,$values,$isFirstColExtId);
							
							if(self::success($id))
							{
								//create record for the ir_model_data table if the records are external
								if($isFirstColExtId === true)
								{
									//format values
									$relatedValues = array('name' => $records[$recordIndex][0], 'module' => $model , 'model' => $model , 'res_id' => $id);
									$relatedId = self::upsert_record_model_data(self::model_irmodeldata,$relatedValues,$isFirstColExtId);

									if(self::success($relatedId))
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
				else
				{
					$_SESSION["trxLog"] = $_SESSION["trxLog"] . getDateTime() . "No records detected in the CSV file!" . "\n";
				}
			}
			else
			{
				$_SESSION["trxLog"] = $_SESSION["trxLog"] . getDateTime() . "An unexpected error occured while opening the CSV file!" . "\n";
			}

			return $status;
		}

		public static function excel_import($model,$uploadedExcelFile,$isFirstColExtId,$relatedModelList)
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

			$failedCount = 0;
			$savedCount = 0;

		
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
				
				if(!self::is_empty_record($row))
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

			$recordsCount = count($records);

			//check if there are records
			if($recordsCount > 0)
			{
				$validRecords = true;

				//loop thorugh the records
				for($recordIndex=0; $recordIndex < $recordsCount; $recordIndex++)
				{
					$values = array();
					
					//loop through the fields
					for($fieldsIndex=0; $fieldsIndex < count($fields); $fieldsIndex++)
					{
						$gotoProcess = true;

						//execute formatting the values
						if($gotoProcess === true)
						{
							//actual record value
							$record = $records[$recordIndex][$fieldsIndex];

							if($record != "")
							{
								//actual field value
								$field = $fields[$fieldsIndex];

								//search if it is a related field
								$relatedfieldIndex = self::search_related_field($relatedModelList,$field);

								//check if the related field is found
								if($relatedfieldIndex['found'] === true)
								{
									//check if the related field is an external id
									if($relatedfieldIndex['isExternalId'] === true)
									{
										//get the values
										$resId = "res_id";
										$modelField = array($resId);
										$cond = array(array('name', '=', $record));
										$result = self::search_and_read_records(self::model_irmodeldata,$modelField,$cond,array());
										
										//if the related field is found
										if(self::success_read($result))
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
										//get the value
										$relatedModel = $relatedfieldIndex['model'];
										$modelField = array("id");
										$cond = array(array('id', '=', $record));

										//if the related field is found
										$result = self::search_and_read_records($relatedModel,$modelField,$cond,array());
										if(self::success_read($result))
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

					echo "<pre>";
					print_r($values);
					echo "</pre>";

					//check if record is valid
					if($validRecords === true)
					{
						$firstCol = $fields[0];

						//create record for the model
						$id = self::upsert_record_model($model,$values,$isFirstColExtId);
						
						if(self::success($id))
						{
							//create record for the ir_model_data table if the records are external
							if($isFirstColExtId === true)
							{
								//format values
								$relatedValues = array('name' => $records[$recordIndex][0], 'module' => $model , 'model' => $model , 'res_id' => $id);
								$relatedId = self::upsert_record_model_data(self::model_irmodeldata,$relatedValues,$isFirstColExtId);

								if(self::success($relatedId))
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

			return $status;
		}

		public static function get_csv_fields($uploadedCsvFile,$columnDelimeter,$columnEnclosure)
		{
			$fields = array();
			$csvFile = fopen($uploadedCsvFile,"r");
			$lineCount = 0;

			//re-define the qoute character
			if($columnEnclosure == "")
			{
				$columnEnclosure = '"';
			}

			//loop through the opened csv file
			while(!feof($csvFile))
			{
				$lineCount++;
				//get the current line record of the csv
				$csvFileValue = fgetcsv($csvFile,0,$columnDelimeter,$columnEnclosure);

				//check if the csv line record is now a blank row
				if(!empty($csvFileValue[0]) && count($csvFileValue) > 1)
				{
					//if first row, assign the $fields for the field name
					//else, assign it to $records as a record
					if($lineCount==1)
					{
						$fields = $csvFileValue;
						break;
					}
				}
			}
			//close the file
			fclose($csvFile);

			return $fields;
		}

		################# PRIVATE METHODS #################

		private static function search_related_field($relatedModelList,$field)
		{
			$result = array();
			$result['found'] = false;
			$result['isExternalId'] = false;

			for($i=0;$i<count($relatedModelList);$i++)
			{
				if($relatedModelList[$i]["relatedField"] === $field)
				{
					$result['found'] = true;
					$result['model'] = $relatedModelList[$i]["model"];
					if($relatedModelList[$i]["isExternalId"] === true)
					{
						$result['isExternalId'] = true;
					}
					else
					{
						$result['isExternalId'] = false;
					}
					
					break;
				}
			}

			return $result;
		}

		private static function search_related_field_is_external($relatedModelList,$relatedField)
		{
			for($i=0;$i<count($relatedModelList);$i++)
			{
				if($relatedModelList[$i]["relatedField"] === $relatedField)
				{
					return $i;
				}
			}

			return -1;
		}

		private static function execute_method($model,$method,$params,$mapping)
		{
			return self::$odooModels->execute_kw(self::$dbName,self::$uid,self::$password,$model,$method,$params,$mapping);
		}

		
		public static function excel_get_number_of_lines($uploadedFile)
		{
			$lineCount = 0;
			$inputFileType = PHPExcel_IOFactory::identify($uploadedFile);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($uploadedFile);

			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			$lineCount = count($sheetData);

			return $lineCount;
		}

		private static function is_empty_record($record)
		{
			$columnCtr=0;
			for($i=0;$i<count($record);$i++)
			{
				if($record[array_keys($record)[$i]] != null && $record[array_keys($record)[$i]] != "")
				{
					$columnCtr++;
				}
			}

			if($columnCtr==0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
?>