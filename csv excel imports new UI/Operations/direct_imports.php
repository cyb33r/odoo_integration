<DOCTYPE html>
<html>
	<head>
		<title>Direct Imports Tool</title>
		<link type="text/css" rel="stylesheet" href="../Styles/layout.css"/>

		<ul>
	  	  <li><a href="server_setup.php">Server Setup</a></li>
		  <li><a href="xml_imports.php">XML Imports</a></li>
		  <li><a href="basic_imports.php">Basic Imports</a></li>
		  <li><a class="active" href="direct_imports.php">Direct Imports</a></li>
		</ul>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script type="text/javascript">

			$(document).ready(function(){
				var mdbFilePath = "";

				$("#direct_imports_submit").click(function(e)
				{
					var mdbDir = document.getElementById("mdbFilePath");
					var connectionString = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" + mdbDir.innerHTML;
					//var connectionString = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" + "C:\\Users\\Ecxelym-1\\Desktop\\INSTALLERS\\YCA Electronic Pricebook\\epb.mdb";
					var connection = new ActiveXObject("ADODB.Connection");
					var statusMsg = "";
					var sql = "";
					var recordCount = 0;
					var recordsIndex = 0;
					var adUseClient= 3;

					connection.Open(connectionString);

					/*Main Group*/
                    var recordSetMainGroup = new ActiveXObject("ADODB.Recordset");
					sql = "SELECT * FROM [Main Group]";

					recordSetMainGroup.CursorLocation = adUseClient;
					recordSetMainGroup.Open(sql,connection);

					recordCount = recordSetMainGroup.RecordCount;
					var recordsMainGroup = new Array(recordCount);
					recordsIndex = 0;

					while(!recordSetMainGroup.eof)	
					{
						var id = recordSetMainGroup.fields("ID").value;
						var mainGroup = recordSetMainGroup.fields("Main Group").value;
						var printOrder = recordSetMainGroup.fields("Print Order").value;
						recordsMainGroup[recordsIndex] = new Array(3);
						recordsMainGroup[recordsIndex][0] = id;
						recordsMainGroup[recordsIndex][1] = mainGroup;
						recordsMainGroup[recordsIndex][2] = printOrder;

						recordSetMainGroup.MoveNext();
						recordsIndex++;
						
					}

					/*Sub Group*/
					var recordSetSubGroup = new ActiveXObject("ADODB.Recordset");
					sql = "SELECT * FROM [Sub Group]";

					recordSetSubGroup.CursorLocation = adUseClient;
					recordSetSubGroup.Open(sql,connection);

					recordCount = recordSetSubGroup.RecordCount;
					var recordsSubGroup = new Array(recordCount);
					recordsIndex = 0;

					while(!recordSetSubGroup.eof)	
					{
						var id = recordSetSubGroup.fields("ID").value;
						var mainGroupID = recordSetSubGroup.fields("Main Group ID").value;
						var subGroup = recordSetSubGroup.fields("Sub Group").value;
						var prefix = recordSetSubGroup.fields("Prefix").value;
						var printOrder = recordSetSubGroup.fields("Print Order").value;

						recordsSubGroup[recordsIndex] = new Array(5);
						recordsSubGroup[recordsIndex][0] = id;
						recordsSubGroup[recordsIndex][1] = mainGroupID;
						recordsSubGroup[recordsIndex][2] = subGroup;
						recordsSubGroup[recordsIndex][3] = prefix;
						recordsSubGroup[recordsIndex][4] = printOrder;

						recordSetSubGroup.MoveNext();
						recordsIndex++;
						
					}

					/*pages*/
					var recordSetPages = new ActiveXObject("ADODB.Recordset");
					sql = "SELECT * FROM pages";

					recordSetPages.CursorLocation = adUseClient;
					recordSetPages.Open(sql,connection);

					recordCount = recordSetPages.RecordCount;
					var recordsPages = new Array(recordCount);
					recordsIndex = 0;

					while(!recordSetPages.eof)	
					{
						var id = recordSetPages.fields("ID").value;
						var mainGroupID = recordSetPages.fields("Main Group ID").value;
						var subGroupID = recordSetPages.fields("Sub Group ID").value;
						var pageNumber = recordSetPages.fields("Page Number").value;
						var blackBox = recordSetPages.fields("Black Box").value;
						var pageHeader = recordSetPages.fields("Page Header").value;
						var pageHeader1 = recordSetPages.fields("Page Header1").value;
						var configuator = recordSetPages.fields("Configuator").value;

						recordsPages[recordsIndex] = new Array(8);
						recordsPages[recordsIndex][0] = id;
						recordsPages[recordsIndex][1] = mainGroupID;
						recordsPages[recordsIndex][2] = subGroupID;
						recordsPages[recordsIndex][3] = pageNumber;
						recordsPages[recordsIndex][4] = blackBox;
						recordsPages[recordsIndex][5] = pageHeader;
						recordsPages[recordsIndex][6] = pageHeader1;
						recordsPages[recordsIndex][7] = configuator;

						recordSetPages.MoveNext();
						recordsIndex++;
						
					}

					/*models*/
					var recordSetModels = new ActiveXObject("ADODB.Recordset");
					sql = "SELECT * FROM models";

					recordSetModels.CursorLocation = adUseClient;
					recordSetModels.Open(sql,connection);

					recordCount = recordSetModels.RecordCount;
					var recordsModels = new Array(recordCount);
					recordsIndex = 0;

					while(!recordSetModels.eof)	
					{
						var id = recordSetModels.fields("ID").value;
						var pageID = recordSetModels.fields("Page ID").value;
						var mainGroupID = recordSetModels.fields("Main Group ID").value;
						var pageNumber = recordSetModels.fields("Page Number").value;
						var subGroupID = recordSetModels.fields("Sub Group ID").value;
						var partNumber = recordSetModels.fields("Part Number").value;
						var description = recordSetModels.fields("Description").value;
						var price = recordSetModels.fields("Price").value;
						var printOrderID = recordSetModels.fields("Print Order ID").value;
						var noteID = recordSetModels.fields("Note ID").value;
						var componentTitle = recordSetModels.fields("Component Title").value;
						var modelID = recordSetModels.fields("Model ID").value;
						var priceComment = recordSetModels.fields("Price Comment").value;
						var multiplier = recordSetModels.fields("Multiplier").value;
						var commissionID = recordSetModels.fields("CommissionID").value;
						var manufacturerID = recordSetModels.fields("ManufacturerID").value;
						var productGroupID = recordSetModels.fields("ProductGroupID").value;
						var arrow = recordSetModels.fields("Arrow").value;

						recordsModels[recordsIndex] = new Array(18);
						recordsModels[recordsIndex][0] = id;
						recordsModels[recordsIndex][1] = pageID;
						recordsModels[recordsIndex][2] = mainGroupID;
						recordsModels[recordsIndex][3] = pageNumber;
						recordsModels[recordsIndex][4] = subGroupID;
						recordsModels[recordsIndex][5] = partNumber;
						recordsModels[recordsIndex][6] = description;
						recordsModels[recordsIndex][7] = price;
						recordsModels[recordsIndex][8] = printOrderID;
						recordsModels[recordsIndex][9] = noteID;
						recordsModels[recordsIndex][10] = componentTitle;
						recordsModels[recordsIndex][11] = modelID;
						recordsModels[recordsIndex][12] = priceComment;
						recordsModels[recordsIndex][13] = multiplier;
						recordsModels[recordsIndex][14] = commissionID;
						recordsModels[recordsIndex][15] = manufacturerID;
						recordsModels[recordsIndex][16] = productGroupID;
						recordsModels[recordsIndex][17] = arrow;

						recordSetModels.MoveNext();
						recordsIndex++;
						
					}

					/*components*/
					var recordSetComponents= new ActiveXObject("ADODB.Recordset");
					sql = "SELECT * FROM components";

					recordSetComponents.CursorLocation = adUseClient;
					recordSetComponents.Open(sql,connection);

					recordCount = recordSetComponents.RecordCount;
					var recordsComponents = new Array(recordCount);
					recordsIndex = 0;

					while(!recordSetComponents.eof)	
					{
						var id = recordSetComponents.fields("ID").value;
						var pageID = recordSetComponents.fields("Page ID").value;
						var mainGroupID = recordSetComponents.fields("Main Group ID").value;
						var componentTitleID = recordSetComponents.fields("Component Title ID").value;
						var partNumber = recordSetComponents.fields("Part Number").value;
						var description = recordSetComponents.fields("Description").value;
						var price = recordSetComponents.fields("Price").value;
						var printOrderID = recordSetComponents.fields("Print Order ID").value;
						var subGroupID = recordSetComponents.fields("Sub Group ID").value;
						var arrow = recordSetComponents.fields("Arrow").value;
						var multiplier = recordSetComponents.fields("Multiplier").value;
						var configuratorDescription = recordSetComponents.fields("ConfiguratorDescription").value;
						var mustInclude = recordSetComponents.fields("MustInclude").value;
						var otherDiscount = recordSetComponents.fields("OtherDiscount").value;
						var productGroupID = recordSetComponents.fields("ProductGroupID").value;
						var lineComment = recordSetComponents.fields("LineComment").value;
						var note = recordSetComponents.fields("Note").value;

						recordsComponents[recordsIndex] = new Array(18);
						recordsComponents[recordsIndex][0] = id;
						recordsComponents[recordsIndex][1] = pageID;
						recordsComponents[recordsIndex][2] = mainGroupID;
						recordsComponents[recordsIndex][3] = componentTitleID;
						recordsComponents[recordsIndex][4] = partNumber;
						recordsComponents[recordsIndex][5] = description;
						recordsComponents[recordsIndex][6] = price;
						recordsComponents[recordsIndex][7] = printOrderID;
						recordsComponents[recordsIndex][8] = subGroupID;
						recordsComponents[recordsIndex][9] = arrow;
						recordsComponents[recordsIndex][10] = multiplier;
						recordsComponents[recordsIndex][11] = configuratorDescription;
						recordsComponents[recordsIndex][12] = mustInclude;
						recordsComponents[recordsIndex][13] = multiplier;
						recordsComponents[recordsIndex][14] = otherDiscount;
						recordsComponents[recordsIndex][15] = productGroupID;
						recordsComponents[recordsIndex][16] = lineComment;
						recordsComponents[recordsIndex][17] = note;

						recordSetComponents.MoveNext();
						recordsIndex++;
						
					}

					/*component titles*/
					var recordSetComponentTitles= new ActiveXObject("ADODB.Recordset");
					sql = "SELECT * FROM [component titles]";

					recordSetComponentTitles.CursorLocation = adUseClient;
					recordSetComponentTitles.Open(sql,connection);

					recordCount = recordSetComponentTitles.RecordCount;
					var recordsComponentTitles = new Array(recordCount);
					recordsIndex = 0;

					while(!recordSetComponentTitles.eof)	
					{
						var id = recordSetComponentTitles.fields("ID").value;
						var mainGroupID = recordSetComponentTitles.fields("Main Group ID").value;
						var componentTitle = recordSetComponentTitles.fields("Component Title").value;
						var sequence = recordSetComponentTitles.fields("Sequence").value;
						var multipleItems = recordSetComponentTitles.fields("MultipleItems").value;
						var separateLineItem = recordSetComponentTitles.fields("SeparateLineItem").value;
						var mustInclude = recordSetComponentTitles.fields("MustInclude").value;
						var cannotSelect = recordSetComponentTitles.fields("CannotSelect").value;
						
						recordsComponentTitles[recordsIndex] = new Array(8);
						recordsComponentTitles[recordsIndex][0] = id;
						recordsComponentTitles[recordsIndex][1] = mainGroupID;
						recordsComponentTitles[recordsIndex][2] = componentTitle;
						recordsComponentTitles[recordsIndex][3] = sequence;
						recordsComponentTitles[recordsIndex][4] = multipleItems;
						recordsComponentTitles[recordsIndex][5] = separateLineItem;
						recordsComponentTitles[recordsIndex][6] = mustInclude;
						recordsComponentTitles[recordsIndex][7] = cannotSelect;

						recordSetComponentTitles.MoveNext();
						recordsIndex++;
						
					}

					$.ajax	
						({
	                        url: 'direct_imports_server_process.php',
	                        type: 'POST',
	                        async: false,
	                        data: 
	                        { 
	                         	'recordsMainGroup' : JSON.stringify(recordsMainGroup),
	                         	'recordsSubGroup' : JSON.stringify(recordsSubGroup),
	                         	'recordsPages' : JSON.stringify(recordsPages),
	                         	'recordsModels' : JSON.stringify(recordsModels),
	                         	'recordsComponents' : JSON.stringify(recordsComponents),
	                         	'recordsComponentTitles' : JSON.stringify(recordsComponentTitles)
	                        },
	                        success: function(data)
	                        {
								statusMsg = data;
	                        }
	                         
	                	});	
					
				});

				$('#mdbFile').change( function(event) {
					mdbFilePath = URL.createObjectURL(event.target.files[0]);
	    			$("img").fadeIn("fast").attr('src',URL.createObjectURL(event.target.files[0]));
				});
			});



		</script>

		<?php
			include "../PhpIntegration/integration_v2.php";
			ini_set('upload_max_filesize', '250M');
			ini_set('post_max_size', '250M');
			ini_set('display_startup_errors',1);
			ini_set('display_errors',1);

			$xmlPath = "../Configuration/config.xml";
			$xml = $xml = new DOMDocument();
			$xml->load($xmlPath);
			$mdbFilePathNodes = $xml->getElementsByTagName('mdbfilepath');
			$hostNodes = $xml->getElementsByTagName('host');
			$dbNameNodes = $xml->getElementsByTagName('dbnameDirect');

			if ($mdbFilePathNodes->length > 0) 
			{
				foreach($mdbFilePathNodes as $mdbFilePathNode)
				{
					$mdbFilePath = $mdbFilePathNode->nodeValue;
				}
			}

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


			if(isset($_POST["submit"]))
			{


			}

		?>
	</head>

	<body id="body">	
		<div id="direct_imports_holder">
			<div class="bgTranslucentBase direct_imports_bgTranslucentCenter"></div>
			<div class="bgTranslucentBase bgTranslucentBottomDirect"></div>
			<div id="direct_imports_formData">
				<div id="direct_imports_summary">
					Dump data from: 
					<div id="mdbFilePath">
						<?php echo $mdbFilePath;?>
					</div>

					<div>
						To: 
						<?php echo $dbName;?>
						@
						<?php echo $host;?>
					</div>
				</div>
				<form id="form" name="form" method="post" enctype="multipart/form-data">
					<input id="direct_imports_submit" type="submit" value="Import" name="submit">	
				</form>
			</div>
			<div id="direct_status_holder">
				<div id="direct_status"></div>
			</div>
		</div>
	</body>
</html>