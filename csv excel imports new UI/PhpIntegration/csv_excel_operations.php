<?php
	include "../PhpIntegration/integration_v2.php";
	include "../PhpExcel/PHPExcel.php";
	
	class Operations
	{
		const model_irmodeldata = "ir.model.data";

		public static function is_empty_record($record)
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

		public static function search_related_field($relatedModelList,$field)
		{
			$result = array();
			$result['found'] = 0;
			$result['isExternalId'] = 0;

			for($i=0;$i<count($relatedModelList);$i++)
			{
				if($relatedModelList[$i]["relatedField"] === $field)
				{
					$result['found'] = true;
					$result['model'] = $relatedModelList[$i]["model"];

					if($relatedModelList[$i]["isExternalId"] == true)
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

		public static function upsert_record_model($model,$values,$isFirstColExtId)
		{
			$result = 0;
			$firstCol = array_keys($values)[0];
			$value = $values[$firstCol];

			unset($values[$firstCol]);

			if($isFirstColExtId==true)
			{

				$condition = array(array('name','=',$value));
				$searchResult = Integration::search_and_read_records(self::model_irmodeldata,array('res_id'),$condition,array());

				print_r($searchResult);
				if(Integration::success_read($searchResult))
				{
					$resId = $searchResult[0]['res_id'];
					$uid = Integration::update_record($model,$resId,$values);
					if(Integration::success($uid))
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
					/*echo "<pre>";
					echo "$model";
					print_r($values);
					echo "</pre>";*/
					$genId = Integration::create_record($model,$values);
					echo "<pre>";
					echo "genID ";
					print_r($genId);
					echo "</pre>";
					
					if(Integration::success($genId))
					{
						//echo "created";
						$result = $genId;
					}
					else
					{
						//echo "NOT created";
						$result = null;
					}
					/*
					echo "<pre>";
					echo "<br><br>";
					echo "kani dri:";
					print_r($result);
					echo "</pre>";*/
				}
			}
			else
			{
				if($firstCol == 'id')
				{
					$condition = array(array($firstCol,'=',$value));
					$searchResult = Integration::search_and_read_records($model,array('id'),$condition,array());
					$id = $searchResult[0]['id'];

					if(Integration::success_read($searchResult))
					{
						$uid = Integration::update_record($model,$id,$values);
						if(Integration::success($uid))
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
						$genId = Integration::create_record($model,$values);
						if(Integration::success($genId))
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

			// "RESULT@!!! - " .$result;

			return $result;
		}

		public static function upsert_record_model_data($model,$values,$isFirstColExtId)
		{
			$result = 0;
			$firstCol = array_keys($values)[0];
			$value = $values[$firstCol];

			$condition = array(array('name','=',$value));
			$searchResult = Integration::search_and_read_records($model,array('id'),$condition,array());

			if(Integration::success_read($searchResult))
			{
				$id = $searchResult[0]['id'];
				$uid = Integration::update_record($model,$id,$values);
				if(Integration::success($uid))
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
				$genId = Integration::create_record($model,$values);
				if(Integration::success($genId))
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
	}
?>