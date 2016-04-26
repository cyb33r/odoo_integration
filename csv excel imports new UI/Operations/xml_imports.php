<?php
	session_start();
?>
<DOCTYPE html>
<html>
	<head>
		<title>XML Imports Tool</title>
		<link type="text/css" rel="stylesheet" href="../Styles/layout.css"/>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
		<script src="../SpreadJS/jszip.js"></script>
		<script src="../SpreadJS/xlsx.js"></script>

		<script>
			var file = null;
			var tag_imports = "imports";
			var tag_format = "format";
			var tag_type = "type";
			var tag_fileName = "file_name";
			var tag_modelName = "model_name";

			var tag_files = "files";
			var tag_file = "file";
			var tag_relatedModelName = "related_model_name";
			var tag_relatedModels = "related_models";
			var tag_relatedModel = "related_model";
			var fileNames = new Array();
			var filesValid = new Array();
			var statusMsgIndex = 4;
			var readyToImport = false;
			var filesCount = 0;
			var submitting = false;

			function validateConfigFile(filePath)
			{
				var fileName = filePath.split('.').pop();

				if((fileName == "xml"))
				{
					return true;
				}
				else
				{
					return false;
				}
			}

			$(document).ready(function()
			{

				function clear()
				{
					$("#tableList tr").remove(); 
					$("#configFile").val(""); 
					$("#generalInfo").html(""); 
					$("#xml_imports_submit").prop("disabled",false);
					$("#xml_status").css("background-color","");
					$("#xml_status").html("");
					readyToImport = false;
					submitting = false;
				}

				function openLogWindow()
				{
					window.open('log_window.php', 'Transaction Log', 'width=580,height=590');
				}

				$("#xml_imports_submit").prop("disabled",false);

				$("#xml_imports_submit").click(function(e)
				{
					if(readyToImport==false || submitting == true)
					{
						return false;
					}
					else
					{
						submitting = true;
					}
				});

				$(document).on('change','.fileUpload',function(e){
					readyToImport = false;
					var id = e.target.id;
					var row = parseInt(id.match(/\d+/)[0]);
					var rowFileName = fileNames[row];
					var fileList = e.target.files;
					var file = fileList[0];
					var fileName = file.name;
					var table =document.getElementById("tableList");
					var cell = table.rows[row].cells[statusMsgIndex];

					if(rowFileName == fileName)
					{
						filesValid[row] = true;
						cell.innerHTML = "Ready";
						cell.style.backgroundColor = "#FF8000";
					}
					else
					{
						filesValid[row] = false;
						cell.innerHTML = "Wrong File!";
						cell.style.backgroundColor = "#FF0000";
					}

					var filesAllReady = false;
					for(var i=0;i<filesCount;i++)
					{
						if(filesValid[i] == true)
						{
							filesAllReady = true;
						}
						else
						{
							filesAllReady = false;
							break;
						}
					}

					var xmlStatusMsg = "";
					if(filesAllReady == true)
					{
						xmlStatusMsg = "All file(s) are ready to be imported!";
						readyToImport = true;
					}
					else
					{
						xmlStatusMsg = "Browse file(s) accordingly and check that the file uploaded matched.";
					}

					$("#xml_status").html(xmlStatusMsg);
				});

				$("#xml_imports_refresh").click(function(e)
				{
					clear();
				});

				$("#xml_imports_log").click(function(e)
				{
					if(readyToImport == true)
					{
						openLogWindow();
					}
					else
					{
						return false;
					}
				});

				$("#configFile").change(function(e)
				{
					$("#tableList tr").remove(); 
					$("#xml_imports_submit").prop("disabled",false);
					$("#xml_status").css("background-color","");
					$("#xml_status").html("");
					readyToImport = false;
					submitting = false;

					var fileList = e.target.files;
					file = fileList[0];
					var configFileName = file.name;
					var configFileType = configFileName.split('.').pop();
					if(validateConfigFile(configFileName))
					{

						var tmpPath = URL.createObjectURL(event.target.files[0]);
	    				$("xml").fadeIn("fast").attr('src',URL.createObjectURL(event.target.files[0]));

	    				var xmlHttp = new XMLHttpRequest();
	    				xmlHttp.open("GET",tmpPath,false);
	    				xmlHttp.send();

	    				var xmlDoc = xmlHttp.responseXML;
	    				var type = xmlDoc.getElementsByTagName(tag_type)[0].childNodes[0].nodeValue;

	    				if(file.name !=null && file.name!="" )
	    				{
	    					if(type.toLowerCase() == "csv" || type.toLowerCase() == "excel")
		    				{
		    					var fileExtension = ".csv";
		    					if(type.toLowerCase() == "excel")
		    					{
		    						fileExtension = ".xlsx,.xls";
		    					}

			    				var filesParentNode = xmlDoc.getElementsByTagName(tag_files)[0];
			    				var filesChildNode = filesParentNode.getElementsByTagName(tag_file);
			    				filesCount = filesChildNode.length
			    				var filesArray = new Array();
			    				var currentRow = 0;

			    				$("#generalInfo").html
			    				(
			    					'Configuration: ' + configFileName + '<br>' +
			    					'File Format: ' + type + '<br>'
			    				);

			    				for(var i=0;i<filesCount;i++)
			    				{
									var table = document.getElementById("tableList");

									var row = table.insertRow(-1);
									var column1 = row.insertCell(0);
									column1.style.width = '20px';
									var column2 = row.insertCell(1);
									column2.style.width = '300px';
									var column3 = row.insertCell(2);
									column3.style.width = '90px';
									var column4 = row.insertCell(3);
									column4.style.width = '200px';
									var column5 = row.insertCell(4);
									column5.style.textAlign = 'center';
									column5.style.backgroundColor = "white";
									column5.style.fontWeight = "bold";

									var fileName = filesChildNode[i].getElementsByTagName(tag_fileName)[0].childNodes[0].nodeValue;
									var model = filesChildNode[i].getElementsByTagName(tag_modelName)[0].childNodes[0].nodeValue;
									var relatedModelsParentNode = filesChildNode[i].getElementsByTagName(tag_relatedModels)[0];
									var relatedModelsChildNode = relatedModelsParentNode.getElementsByTagName(tag_relatedModel);
									var relatedModels = new Array();

									for(var j=0;j<relatedModelsChildNode.length;j++)
									{
										relatedModels.push(relatedModelsChildNode[j].getElementsByTagName(tag_relatedModelName)[0].childNodes[0].nodeValue);
									}

									var column3Value = '<input type="file" id="uploadedFile[' + i + ']" name="uploadedFile[' + i;
									column3Value += ']" class="fileUpload" accept="' + fileExtension + '" style="width: 90px; margin-left:2px;">';

									var column1Value = i+1;
									
									var column4Value = '<progress id="progress[' + i +']" class="progress" ';
									column4Value += 'value="0" max="100">sample</progress>';
									/*column4Value += '<div id="percent['+ i +']" class="precent">0%</div>';*/
									/*column4Value += '<div id="status[' + i + ']"></div>';*/
									

									var column5Value = "Waiting for file...";

									fileNames[i] = fileName;
									filesValid[i] = false;
									var column2Value = "<strong>File Name:</strong>" + fileName + "<br>";
									column2Value += "<strong>Model:</strong>" + model + "<br>";
									column2Value += "<strong>Related Model(s): </strong>" + relatedModels.join(", ");
									column1.innerHTML = column1Value;
									column2.innerHTML = column2Value;
									column3.innerHTML = column3Value;
									column4.innerHTML = column4Value;
									column5.innerHTML = column5Value;

									filesValid[i] = false;
									
			    				}

			    				$("#xml_status").html("Configuration file loaded. Browse all the file(s) to import accordingly.");
		    				}
		    				else
		    				{
		    					$("#xml_imports_submit").prop("disabled",true);
		    					$("#xml_status").css("color","red");
		    					$("#xml_status").html('Invalid ' + '"'+ type +'"' + ' file type! Check the configuration file for the <type> tag!');
		    				}
	    				}
					}
					else
					{
						$("#xml_imports_submit").prop("disabled",true);
    					$("#xml_status").css("color","red");
    					$("#xml_status").html('Invalid ' + '"'+ configFileType +'"' + ' file type for the configuration file! Upload XML files only!<type> tag!');
					}
					
				});
			});
			
		</script>
	</head>
		
	<body id="body">	
		<div id="ajaxResponse" style="display:none;"></div>
		<ul>
		  <li><a href="server_setup.php">Server Setup</a></li>
		  <li><a class="active" href="xml_imports.php">XML Imports</a></li>
		  <li><a href="basic_imports.php">Basic Imports</a></li>
		  <li><a href="direct_imports.php">Direct Imports</a></li>
		</ul>

		<div id="xml_imports_holder">
			<div class="bgTranslucentBase xml_imports_bgTranslucentCenter"></div>
			<div class="bgTranslucentBase bgTranslucentBottom"></div>
			<div id="xml_imports_formData">
				<form id="form" name="form" method="post" enctype="multipart/form-data" action="xml_imports_submit_process.php" target="hiddenFrame">
					<div id="header">
						<div id="configHolder">
							Config File:<input type="file" name="configFile" id="configFile" accept=".xml" style="width: 400px">
						</div>
						<div id="xml_refresh_holder">
							<button id="xml_imports_refresh" type="button">
								<img src="../Images/refresh-icon.png"/>
							</button>
						</div>
						<div id="xml_log_holder">
							<button id="xml_imports_log" type="button">
									<img src="../Images/log-icon.png"/>
							</button>
						</div>
					</div>
					<div id="generalInfoBg">
						<div id="generalInfo" style="margin-left: 10px; margin-top: 2px"></div>
					</div>
					<div id="fileList">
						<table id="tableList"></table>
					</div>
					<iframe id="hiddenFrame" name="hiddenFrame" style="display:none;"></iframe>
					<input id="xml_imports_submit" type="submit" value="Import" name="submit">	
				</form>
			</div>
			<div id="xml_status_holder">
				<div id="xml_status"></div>
			</div>
		</div>
	</body>
</html>
