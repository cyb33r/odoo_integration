<DOCTYPE html>
<html>
	<head>
			<link type="text/css" rel="stylesheet" href="../Styles/layout.css"/>

			<!--   -->
			<ul>
			  	  <li><a class="active" href="server_setup.php">Server Setup</a></li>
				  <li><a href="xml_imports.php">XML Imports</a></li>
				  <li><a href="basic_imports.php">Basic Imports</a></li>
				  <li><a href="direct_imports.php">Direct Imports</a></li>
			</ul>

			<?php
			
			try
			{
				//set the errors to display in the browser
				//ini_set('display_startup_errors',1);
				//ini_set('display_errors',1);

				//Initial values of the variables
				$url = "";//"http://192.168.1.215:8069";
				$dbName = "";//"rmx_sandbox";
				$username = "";//"admin";
				$password = "";//"csctest";
				$mdbFilePath = "";
				$host = "";
				$dbName2 = "";
				$username2 = "";
				$password2 = "";

				$xmlPath = "../Configuration/config.xml";
				$xml = $xml = new DOMDocument();
				$xml->load($xmlPath);
				$mdbFilePathNodes = $xml->getElementsByTagName('mdbfilepath');
				$hostNodes = $xml->getElementsByTagName('host');
				$dbNameDirectNodes = $xml->getElementsByTagName('dbnameDirect');
				$usernameDirectNodes = $xml->getElementsByTagName('usernameDirect');
				$passwordDirectNodes = $xml->getElementsByTagName('passwordDirect');
				$urlNodes = $xml->getElementsByTagName('url');
				$dbNameNodes = $xml->getElementsByTagName('dbname');
				$usernameNodes = $xml->getElementsByTagName('username');
				$passwordNodes = $xml->getElementsByTagName('password');

				$msg = "";
				
				if(isset($_POST["submit"]))
				{
					if ($mdbFilePathNodes->length > 0) 
					{
						foreach($mdbFilePathNodes as $mdbFilePathNode)
						{
							$mdbFilePathNode->nodeValue = trim($_POST["mdbfilepath"]);
						}
					}

					if ($hostNodes->length > 0) 
					{
						foreach($hostNodes as $hostNode)
						{
							$hostNode->nodeValue = trim($_POST["host"]);
						}
					}

					if ($dbNameDirectNodes->length > 0) 
					{
						foreach($dbNameDirectNodes as $dbNameDirectNode)
						{
							$dbNameDirectNode->nodeValue = trim($_POST["dbname2"]);
						}
					}

					if ($usernameDirectNodes->length > 0) 
					{
						foreach($usernameDirectNodes as $usernameDirectNode)
						{
							$usernameDirectNode->nodeValue = trim($_POST["username2"]);
						}
					}

					if ($passwordDirectNodes->length > 0) 
					{
						foreach($passwordDirectNodes as $passwordDirectNode)
						{
							$passwordDirectNode->nodeValue = trim($_POST["password2"]);
						}
					}

					if ($urlNodes->length > 0) 
					{
						foreach($urlNodes as $urlNode)
						{
							$urlNode->nodeValue = trim($_POST["url"]);
						}
					}
					
					if ($dbNameNodes->length > 0) 
					{
						foreach($dbNameNodes as $dbNameNode)
						{
							 $dbNameNode->nodeValue = trim($_POST["dbname"]);
						}
					}

					if ($usernameNodes->length > 0) 
					{
						foreach($usernameNodes as $usernameNode)
						{
							$usernameNode->nodeValue = trim($_POST["username"]);
						}
					}

					if ($passwordNodes->length > 0) 
					{
						foreach($passwordNodes as $passwordNode)
						{
							$passwordNode->nodeValue = trim($_POST["password"]);
						}
					}

					$xml->save($xmlPath);
					$msg = "Successfully saved configuration!";
				}
				
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

				if ($dbNameDirectNodes->length > 0) 
				{
					foreach($dbNameDirectNodes as $dbNameDirectNode)
					{
						$dbName2 = $dbNameDirectNode->nodeValue;
					}
				}

				if ($usernameDirectNodes->length > 0) 
				{
					foreach($usernameDirectNodes as $usernameDirectNode)
					{
						$username2 = $usernameDirectNode->nodeValue;
					}
				}

				if ($passwordDirectNodes->length > 0) 
				{
					foreach($passwordDirectNodes as $passwordDirectNode)
					{
						$password2 = $passwordDirectNode->nodeValue;
					}
				}

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

				if ($usernameNodes->length > 0) 
				{
					foreach($usernameNodes as $usernameNode)
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
			}
			catch(Exception $ex)
			{
				$msg = $ex->getMessage();
			}
		?>
	</head>
		
	<body>	
			<div id="server_holder">
				<div class="bgTranslucentBase server_bgTranslucentCenter"></div>
				<div class="bgTranslucentBase server_bgTranslucentBottom"></div>
				
				<div id="formData">
					<form id="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
						<table>
							<tr>
								Basic & XML Imports
							</tr>
							<tr>
								<td>URL:</td>
								<td><input type="text" name="url" value="<?php echo $url; ?>" enabled style="width: 230px"></td>
							</tr>
							<tr>
								<td>DB Name:</td>
								<td><input type="text" name="dbname" value="<?php echo $dbName; ?>" style="width: 230px"></td>
							</tr>
							<tr>
								<td>Username:</td>
								<td><input type="text" name="username" value="<?php echo $username; ?>" style="width: 230px"></td>
							</tr>
							<tr>
								<td>Password:</td>
								<td><input type="password" name="password" value="<?php echo $password; ?>" style="width: 230px"></td>
							</tr>
							<tr>
								<td><strong>Direct Imports</strong></td>
							</tr>
							<tr>
								<td>MDB File Path:</td>
								<td><input type="text" name="mdbfilepath" value="<?php echo $mdbFilePath; ?> " enabled style="width: 230px"></td>
							</tr>
							<tr>
								<td>Host:</td>
								<td><input type="text" name="host" value="<?php echo $host; ?>" enabled style="width: 230px"></td>
							</tr>
							<tr>
								<td>DB Name:</td>
								<td><input type="text" name="dbname2" value="<?php echo $dbName2; ?>" style="width: 230px"></td>
							</tr>
							<tr>
								<td>Username:</td>
								<td><input type="text" name="username2" value="<?php echo $username2; ?>" style="width: 230px"></td>
							</tr>
							<tr>
								<td>Password:</td>
								<td><input type="password" name="password2" value="<?php echo $password2; ?>" style="width: 230px"></td>
							</tr>
						</table>
						<input id="server_submit" type="submit" value="Save" name="submit">	
					</form>
				</div>

				<div id="server_statusMessage">
					<div id="message"><?php echo $msg?><div>
				</div>
			</div>	
	</body>
</html>
