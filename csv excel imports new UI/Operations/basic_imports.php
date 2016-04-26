<?php
	session_start();
?>
<DOCTYPE html>
<html>
	<head>
		<title>Basic Imports Tool</title>
		<link type="text/css" rel="stylesheet" href="../Styles/layout.css"/>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="../SpreadJS/jszip.js"></script>
		<script src="../SpreadJS/xlsx.js"></script>

		<ul>
		  <li><a href="server_setup.php">Server Setup</a></li>
		  <li><a href="xml_imports.php">XML Imports</a></li>
		  <li><a class="active" href="basic_imports.php">Basic Imports</a></li>
		  <li><a href="direct_imports.php">Direct Imports</a></li>
		</ul>

		<?php
			//copy the content of the integration.php
			include "../PhpIntegration/integration.php";

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
			$log="";

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

			//triggered when the csv upload is clicked
			if(isset($_POST["submit"]))
			{
				//login to odoo
				if(Integration::login($url,$dbName,$username,$password))
				{
					$log .= getDateTime() . "Logged-in to: $dbName as $username" . "\n";
					$fields = array('model');
					$condition = array();
					$mapping = array();

					//read all the models in the ir_model
					$modelsArray = Integration::search_and_read_records("ir.model",$fields,$condition,$mapping);
					

					if(Integration::success_read($modelsArray))
					{
						for($i=0;$i<count($modelsArray);$i++)
						{
							//actual models list
							$modelsList[$i] = str_replace(".", "_", $modelsArray[$i]["model"]);
						}
					}

					$timeStart = microtime(true);

					$model = $_POST["models"];
					$fileName = $_FILES["uploadedFile"]["name"];

					if($fileName!="")
					{
						$log .= getDateTime() . "Starting import" . "\n";
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

							$_SESSION["trxLog"] = $log;

							if(isCsv($fileType))
							{
								$csvDelimeter2 = $_POST["delimeters2"];
								$csvEnclosure2 = $_POST["enclosures2"];

								if($csvEnclosure2 == "")
								{
									$csvEnclosure2 = '"';
								}

								$result = Integration::csv_import($model,$uploadedFile,$csvDelimeter2,$csvEnclosure2,$isFirstColExternalId,$relatedModelList);
							}
							else
							{
								$result = Integration::excel_import($model,$uploadedFile,$isFirstColExternalId,$relatedModelList);
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

				    $log = $_SESSION['trxLog'];
				    $log .= getDateTime() . "Import End" . "\n";
					
				}
				else
				{
					$log .= getDateTime() . "Failed to login: $dbName as $username" . "\n";
				}
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

			function getDateTime()
			{
				return  date("m/d/y G:i:s") . ": ";
			}
		?>
		<script>
			var headersList = new Array();
			var recordsList = new Array();
			var file;
			var fileType = "csv";

			$(document).ready(function(){

				var relatedModelCount = 0;
				var headersArray;

				function readCsv(e)
				{
					if(e!==null)
					{
						var fileList = e.target.files;
					    file = fileList[0];

						if(fileType == "csv")
						{
					    	var delimeter = document.getElementById("delimeters2").value;
					    	var enclosure = document.getElementById("enclosures2").value;

					    	if(
					    		enclosure == "")
					    	{
					    		enclosure = "\"";
					    	}

					    	loadText(file);

					    	function loadText(theFile)
					    	{		   
					    		var fileReader = new FileReader();
					    		
					    		fileReader.onload = function(loadedEvent)
					    		{
					    			var loadedText = loadedEvent.target.result;
					    			var headersText = loadedText.substr(0,loadedText.indexOf("\n"));
					    			var headersArray = headersText.split(delimeter);
					    			var headersArrayFilterEnclosure = new Array();
					    			var exp = new RegExp(enclosure,'g');

					    			for(var i=0;i<headersArray.length;i++)
					    			{

					    				headersArrayFilterEnclosure.push(headersArray[i].replace(exp,''));
					    			}

					    			headersList = headersArrayFilterEnclosure;

						    		var table = document.getElementById("recordTable");
						    		table.innerHTML = loadedText;
					    			/*document.getElementById("fileName").innerHTML = file.name;*/
					    		}

					    		fileReader.readAsText(theFile);						      	
					    	}
					    	
						}
						else
						{

							var f = fileList[0];
							
						    var reader = new FileReader();
						    var name = f.name;

						    reader.onload = function(e) 
						    {

						      var data = e.target.result;

						      var workbook = XLSX.read(data, {type: 'binary'});
						      var sheetName = workbook.SheetNames[0];
						      var worksheet = workbook.Sheets[sheetName];
						      var exp = new RegExp('"','g');

						      recordsList[0] = new Array();
						      var recordIndex = 0;
						      var colIndex = 0;
						      for(col in worksheet)
						      {
						      					      	
						      	if(col[0] !== '!')
						      	{
						      		/*
						      		console.log(col);
						      		if(col.match(/\d+/)[0] == 1)
							      	{
							      		var colVal = JSON.stringify(worksheet[col].v).replace(exp,'');
							      		headersList.push(colVal)
							      	}
							      	else
							      	{
							      		break;
							      	}*/

							      	var colVal = JSON.stringify(worksheet[col].v).replace(exp,'');
							      	var colNumber = col.match(/\d+/)[0];

						      		if( colNumber == 1)
							      	{
							      		headersList.push(colVal)
							      	}
							      	else if(colNumber > 1)
							      	{
							      		if(recordIndex + 2 == colNumber)
							      		{
							      			recordsList[recordIndex][colIndex] = colVal;
							      			colIndex++;
							      		}
							      		else
							      		{
							      			recordIndex++;
							      			colIndex = 0;
							      			recordsList[recordIndex] = new Array();
							      			recordsList[recordIndex][colIndex] = colVal;
							      			colIndex++;
							      		}
							      	}
						      	}
						      }

						      	var table = document.getElementById("recordTable");
						      	var row = table.insertRow(-1);
								for(var colNumber=0;colNumber<headersList.length;colNumber++)
								{
									var col = row.insertCell(colNumber);
									col.style.width = '100px';
									col.innerHTML = headersList[colNumber];
									col.style.fontWeight = "bold";
								}

								for(var recNumber=0;recNumber<recordsList.length;recNumber++)
								{
									row = table.insertRow(-1);
									for(var colNumber=0;colNumber<recordsList[recNumber].length;colNumber++)
									{
										var col = row.insertCell(colNumber);
										col.style.width = '100px';
										col.innerHTML = recordsList[recNumber][colNumber];
									}
								}
						    };
						    reader.readAsBinaryString(f);
							/*document.getElementById("fileName").innerHTML = name;*/
						}	
					}
				}

				$("#recordTable").click(function(e)
				{
					var popup = window.open("", "_blank", "width=640,height=480,resizeable,scrollbars"),
				      table = document.getElementById("basic_recordTable");

				  popup.document.write(table.outerHTML);
				  popup.document.close();
				  if (window.focus) 
				    popup.focus();
				});

				//remove button in the related model
				$("body").on("click", ".delete", function (e) {
					$(this).parent("div").remove();
				});

				//browse csv button
				$("#uploadedFile").change(function(e){

					fileType = document.getElementById("fileType").value;

					if(validateFile())
					{
						document.getElementById("message").innerHTML = "";
						//var fileList = e.target.files;
					    //file = fileList[0];
						readCsv(e);
						//console.log(fileList);
					}
					else
					{
						document.getElementById("message").innerHTML = "Invalid file uploaded!";
						headersList = null;
					}
			    });

			    function openLogWindow()
				{
					window.open('log_window.php', 'Transaction Log', 'width=580,height=590');
				}

			    $("#basic_imports_log").click(function(e)
				{
					openLogWindow();

				});

			    $("#uploadedFile").click(function(e){
			    	if(fileType == "csv")
					{
						document.getElementById("uploadedFile").accept = ".csv";
					}
					else
					{
						document.getElementById("uploadedFile").accept = ".xlsx,.xls";
					}
			    });

			    $("#addModel").click(function(){
			    	var fieldArray = headersList;
			    	var relatedFields = "";
			    	
			    	for(var i=0;i < fieldArray.length; i++)
			    	{
			    		relatedFields +=  '<option value=' + fieldArray[i] + '>' + fieldArray[i] + '</option>';
			    	}

			    	var modelArray = <?php echo json_encode($modelsList); ?>;
			    	var relatedModels = "";
			    	
			    	for(var i=0;i < modelArray.length; i++)
			    	{
			    		relatedModels +=  '<option value=' + modelArray[i].replace("_",".") + '>' + modelArray[i] + '</option>';
			    	}

			        $("#relatedModelList").append
			        (
			        	'<div>'+
			        	'<hr>'+
			        	'> Related Model:'+
			        	'<br>' + 
			        	'&nbsp&nbsp' + 
						'<select name="relatedModels[]">'+
							relatedModels +
						'</select>'+
						'<br>' +
						'&nbsp&nbsp' + 
						'Related Field:'+
						'<br>' + 
						'&nbsp&nbsp' + 
						'<select name="relatedFields[]" style="width: 313px">'+
							relatedFields +
						'</select>'+

						'<br><input style="margin-left: 20px" type="checkbox" name="isExternalIds[]" value="' + relatedModelCount + '">External Id' +
						'<button class="delete" style="margin-left: 125px">Remove</button>'+
						'<br>' +
						'</div>'
			        );
			        relatedModelCount++;
			    });

				$("#fileType").change(function(e){
					fileType = document.getElementById("fileType").value;
					/*$("#fileName").empty();*/

					if(fileType == "csv")
					{
						$("#csvFormatting").show();
					}
					else
					{
						$("#csvFormatting").hide();
					}

					if(file == null)
					{
						//readCsv(e);
					}
					else
					{
						$("#relatedModelList").empty();
						if(validateFile())
						{
							readCsv(file);
						}
						else
						{
							document.getElementById("uploadedFile").value = "";
							headersList = null;
						}
					}
			    });

				function validateFile()
				{
					var filePath = document.getElementById("uploadedFile").value;
					var fileName = filePath.split('.').pop();

					if((fileName == "csv" && fileType == "csv") || (fileName == "xlsx" && fileType == "excel") || (fileName == "xls" && fileType == "excel"))
					{
						return true;
					}
					else
					{
						return false;
					}
				}

			});
		</script>
	</head>
		
	<body>	
			<div id="basic_holder">
				<div class="bgTranslucentBase basic_bgTranslucentTop"></div>
				<div class="bgTranslucentBase basic_bgTranslucentLeft"></div>		
				<div class="bgTranslucentBase basic_bgTranslucentRight"></div> 
				<div class="bgTranslucentBase bgTranslucentBottom"></div>

				<div id="formData">
					<form id="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
						<div id="top">
							<div id="fileFormat">
								File Format:
									<select name="fileType" id="fileType">
										<option value="csv">CSV</option>
										<option value="excel">Excel</option>
									</select>
							</div>
							<div id="csvFormatting">
									Delimeter:
										<select name="delimeters2" id="delimeters2">
											<?php $delimeter2 = array(",",";"); ?>
											<option value="<?php echo $delimeter2[0]; ?>"><?php echo $delimeter2[0]; ?></option>
											<option value="<?php echo $delimeter2[1]; ?>"><?php echo $delimeter2[1]; ?></option>
										</select>
									<br>
									Enclosure:
										<select name="enclosures2" id="enclosures2">
											<?php $enclosure2 = array('"',"|","'"); ?>
											<option value="<?php echo $enclosure2[0]; ?>"><?php echo $enclosure2[0]; ?></option>
											<option value="<?php echo $enclosure2[1]; ?>"><?php echo $enclosure2[1]; ?></option>
											<option value="<?php echo $enclosure2[2]; ?>"><?php echo $enclosure2[2]; ?></option>
										</select>
							</div>
							<div id="basic_refresh_holder">
								<button id="basic_imports_refresh" type="button">
									<img src="../Images/refresh-icon.png"/>
								</button>
							</div>
							<div id="basic_log_holder">
								<button id="basic_imports_log" type="button">
										<img src="../Images/log-icon.png"/>
								</button>
							</div>
						</div>
						<div id="left">
							<div id="uploadedFileList" style="margin-top: 0px">
								File to import:
								<br>
								<div>
									<input type="file" name="uploadedFile" id="uploadedFile" accept=".csv" style="width: 320px">
								</div>
								<br>
							</div>
							<div id="basic_recordTable">
								<table id="recordTable">

								</table>
							</div>
						</div>

						<div id="right">
							<div style="margin-left: 0px; margin-top: 0px">
								Model:
									<select name="models" style="width: 349px">
										<?php  
											for($i=0;$i<count($modelsList);$i++)
											{
												echo '<option value=' . str_replace("_", ".", $modelsList[$i]) . '>' . $modelsList[$i] . '</option>';
											}
										?>
									</select>
								<input type="checkbox" name="isFirstColExternalId" value="1">First column is External Id
								<div id="relatedModelList"></div>
								<div>
									<button id="addModel" type="button" onclick="addModel()" style="margin: 30px 0px 0px 318px; width: 100px; height: 30px">Add Model</button>
								</div>
							</div>
						</div>
						<input id="submit" type="submit" value="Import" name="submit">				
					</form>
				</div>


				<div id="bottom">
						<div id="message"><?php echo $msg; ?><div>	
				</div>

			

			</div>
	</body>
</html>
