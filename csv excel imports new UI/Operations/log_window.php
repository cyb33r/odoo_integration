<DOCTYPE html>
<html>
	<body>
		<?php
		session_start();

			/*
			$logPath = "../Transaction Log/trx_log.txt";
			$logFile = fopen($logPath,"r");
			$txt = fread($logFile,filesize($logPath));*/

			if(isset($_SESSION['trxLog']) && !empty($_SESSION['trxLog'])) 
			{
			   	echo nl2br($_SESSION["trxLog"]);
			}
			else
			{
				echo "This is where the current transaction log will be placed.";
			}
		?>
	</body>
</html>