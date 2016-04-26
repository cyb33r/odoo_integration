<?php
	include "../PhpIntegration/integration_v2.php";
	
	$xmlPath = "../Configuration/config.xml";
	$xml = $xml = new DOMDocument();
	$xml->load($xmlPath);
	$hostNodes = $xml->getElementsByTagName('host');
	$dbNameNodes = $xml->getElementsByTagName('dbnameDirect');
	$usernameNodes = $xml->getElementsByTagName('usernameDirect');
	$passwordNodes = $xml->getElementsByTagName('passwordDirect');

	if ($hostNodes->length > 0) 
	{
		foreach($hostNodes as $hostNode)
		{
			$host = $hostNode->nodeValue;
		}
	}

	if ($dbNameNodes->length > 0) 
	{
		foreach($dbNameNodes as $dbNameNode)
		{
			$dbName = $dbNameNode->nodeValue;
		}
	}

	if ($usernameNodes->length > 0) 
	{
		foreach($usernameNodes as $usernameNode)
		{
			$user = $usernameNode->nodeValue;
		}
	}

	if ($passwordNodes->length > 0) 
	{
		foreach($passwordNodes as $passwordNode)
		{
			$password = $passwordNode->nodeValue;
		}
	}

	$pgConnection = pg_connect("host=$host port=5432 dbname=$dbName user=$user password=$password")
						or die("Failed to connect to $dbName@$host");

	$vendorID = 168;
	$success = false;

	pg_query("BEGIN");

	$resultClearData = pg_query($pgConnection,"SELECT f_yc_clean_tables()");
	$resultClearTmpTable = pg_query($pgConnection, "SELECT f_yc_clean_temp_tables()");
	$resultClearTmpConvertTable = pg_query($pgConnection,"SELECT f_yc_clean_temp_convert_tables()");

	if($resultClearData && $resultClearTmpTable && $resultClearTmpConvertTable)
	{
		$recordsMainGroup = json_decode($_POST['recordsMainGroup']);
		$recordsSubGroup = json_decode($_POST['recordsSubGroup']);
		$recordsPages = json_decode($_POST['recordsPages']);
		$recordsModels = json_decode($_POST['recordsModels']);
		$recordsComponents = json_decode($_POST['recordsComponents']);
		$recordsComponentTitles = json_decode($_POST['recordsComponentTitles']);
		//file_put_contents('php://stderr', print_r($recordsPages, TRUE));

		/*Dump Main Group*/
		for($i=0;$i<count($recordsMainGroup);$i++)
		{
			$id = $recordsMainGroup[$i][0];
			$mainGroup = str_replace("'", "''", $recordsMainGroup[$i][1]);
			$printOrder = $recordsMainGroup[$i][2];
			$pgSql = "INSERT INTO x_tmp_maingroup VALUES ($id,'$mainGroup',$printOrder)";
			$resultMainGroup = pg_query($pgConnection,$pgSql);

			if(!$resultMainGroup)
			{
				break;
			}
		}

		if($resultMainGroup)
		{
			/*Dump Sub Group*/
			for($i=0;$i<count($recordsSubGroup);$i++)
			{
				$id = $recordsSubGroup[$i][0];
				$mainGroupID = $recordsSubGroup[$i][1];
				$subGroup = str_replace("'", "''", $recordsSubGroup[$i][2]);;
				$prefix = str_replace("'", "''", $recordsSubGroup[$i][3]);;
				$printOrder = $recordsSubGroup[$i][4];
				$pgSql = "INSERT INTO x_tmp_subgroup VALUES ($id,$mainGroupID,'$subGroup','$prefix',$printOrder)";
				$resultSubGroup = pg_query($pgConnection,$pgSql);

				if(!$resultSubGroup)
				{
					break;
				}
			}
			
			if($resultSubGroup)
			{
				/*Dumb pages */
				for($i=0;$i<count($recordsPages);$i++)
				{
					
					$id = $recordsPages[$i][0];
					$mainGroupID = $recordsPages[$i][1];
					$subGroupID = $recordsPages[$i][2];
					$pageNumber = $recordsPages[$i][3];
					$blackBox = str_replace("'", "''", $recordsPages[$i][4]);
					$pageHeader = str_replace("'", "''", $recordsPages[$i][5]);
					$pageHeader1 = str_replace("'", "''", $recordsPages[$i][6]);
					$configuator = decbin($recordsPages[$i][7]);
					$pgSql = "INSERT INTO x_tmp_pages VALUES ($id,$mainGroupID,$subGroupID,$pageNumber,'$blackBox','$pageHeader','$pageHeader1',$configuator)";
					$resultPages = pg_query($pgConnection,$pgSql);
					
					if(!$resultPages)
					{
						break;
					}
				}

				if($resultPages)
				{
					/*Dump models*/
					for($i=0;$i<count($recordsModels);$i++)
					{
						$id = $recordsModels[$i][0];
						$pageID = $recordsModels[$i][1];
						$mainGroupID = $recordsModels[$i][2];
						$pageNumber = $recordsModels[$i][3];
						$subGroupID = $recordsModels[$i][4];
						$partNumber = str_replace("'", "''", $recordsModels[$i][5]);
						$description = str_replace("'", "''", $recordsModels[$i][6]);
						$price = $recordsModels[$i][7];
						$printOrderID = $recordsModels[$i][8];
						$noteID = str_replace("'", "''", $recordsModels[$i][9]);
						$componentTitle = str_replace("'", "''", $recordsModels[$i][10]);
						$modelID = str_replace("'", "''", $recordsModels[$i][11]);
						$priceComment = str_replace("'", "''", $recordsModels[$i][12]);
						$multiplier = $recordsModels[$i][13];
						$commissionID = $recordsModels[$i][14];
						$manufacturerID = $recordsModels[$i][15];
						$productGroupID = $recordsModels[$i][16];
						$arrow = decbin($recordsModels[$i][17]);

						if($printOrderID == null)
						{
							$printOrderID = "null";
						}

						$pgSql = "INSERT INTO x_tmp_models VALUES ($id,$pageID,$mainGroupID,$pageNumber,$subGroupID,'$partNumber','$description',$price,$printOrderID,'$noteID','$componentTitle','$modelID','$priceComment',$multiplier,$commissionID,$manufacturerID,$productGroupID,$arrow)";
						
						$resultModels = pg_query($pgConnection,$pgSql);

						if(!$resultModels)
						{
							break;
						}
					}

					if($resultModels)
					{
						/*Dump components*/
						for($i=0;$i<count($recordsComponents);$i++)
						{
							$id = $recordsComponents[$i][0];
							$pageID = $recordsComponents[$i][1];
							$mainGroupID = $recordsComponents[$i][2];
							$componentTitleID = $recordsComponents[$i][3];
							$partNumber = str_replace("'", "''", $recordsComponents[$i][4]);
							$description = str_replace("'", "''", $recordsComponents[$i][5]);
							$price = $recordsComponents[$i][6];
							$printOrderID = $recordsComponents[$i][7];
							$subGroupID = $recordsComponents[$i][8];
							$arrow = decbin($recordsComponents[$i][9]);
							$multiplier = $recordsComponents[$i][10];
							$configuratorDescription = str_replace("'", "''", $recordsComponents[$i][11]);
							$mustInclude = decbin($recordsComponents[$i][12]);
							$otherDiscount = (double)$recordsComponents[$i][13];
							$productGroupID = $recordsComponents[$i][14];
							$lineComment = str_replace("'", "''", $recordsComponents[$i][15]);
							$note = str_replace("'", "''", $recordsComponents[$i][16]);

							if($printOrderID == null)
							{
								$printOrderID = "null";							
							}

							if($componentTitleID ==  null)
							{
								$componentTitleID = "null";
							}

							$pgSql = "INSERT INTO x_tmp_components VALUES ($id,$pageID,$mainGroupID,$componentTitleID,'$partNumber','$description',$price,$printOrderID,$subGroupID,$arrow,$multiplier,'$configuratorDescription',$mustInclude,$otherDiscount,$productGroupID,'$lineComment','$note')";

							$resultComponents = pg_query($pgConnection,$pgSql);

							if(!$resultComponents)
							{
								break;
							}
						}

						if($resultComponents)
						{
							/*Dump component titles*/
							for($i=0;$i<count($recordsComponentTitles);$i++)
							{
								$id = $recordsComponentTitles[$i][0];
								$mainGroupID = $recordsComponentTitles[$i][1];
								$componentTitle = str_replace("'", "''", $recordsComponentTitles[$i][2]);
								$sequence = $recordsComponentTitles[$i][3];
								$multipleItems = decbin($recordsComponentTitles[$i][4]);
								$separateLineItem = decbin($recordsComponentTitles[$i][5]);
								$mustInclude = decbin($recordsComponentTitles[$i][6]);
								$cannotSelect = decbin($recordsComponentTitles[$i][7]);

								$pgSql = "INSERT INTO x_tmp_componenttitles VALUES ($id,$mainGroupID,'$componentTitle',$sequence,$multipleItems,$separateLineItem,$mustInclude,$cannotSelect)";

								$resultComponentTitles = pg_query($pgConnection,$pgSql);

								if(!$resultComponentTitles)
								{
									break;
								}
							}

							if($resultComponentTitles)
							{
								/*CONVERT TABLE DATA*/
								if(pg_query($pgConenction,"SELECT f_yc_arrange_to_categories_convert($vendorID)"))
								{
									if(pg_query($pgConenction,"SELECT f_yc_arrange_to_parts_convert($vendorID)"))
									{
										if(pg_query($pgConenction,"SELECT f_yc_arrange_to_optioncategories_convert()"))
										{
											if(pg_query($pgConenction,"SELECT f_yc_arrange_to_options_convert()"))
											{
												if(pg_query($pgConenction,"SELECT f_yc_arrange_to_accessories_convert()"))
												{
													/*DUMP CONVERTED DATA*/
													if(pg_query($pgConenction,"SELECT f_yc_convert_temp_categories($vendorID)"))
													{
														if(pg_query($pgConenction,"SELECT f_yc_convert_temp_parts($vendorID)"))
														{
															if(pg_query($pgConenction,"SELECT f_yc_convert_temp_optioncategories($vendorID)"))
															{
																if(pg_query($pgConenction,"SELECT f_yc_convert_temp_options($vendorID)"))
																{
																	if(pg_query($pgConenction,"SELECT f_yc_convert_temp_accessories($vendorID)"))
																	{
																		$success = true;
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}	
		}
	}

	if($success)
	{
		pg_query("COMMIT");
	}
	else
	{
		pg_query("ROLLBACK");
	}
			
	pg_close($pgConnection);
?>