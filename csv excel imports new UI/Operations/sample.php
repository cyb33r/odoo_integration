<?php
	for($i=0;$i<10;$i++)
	{
		echo "<script>alert('This is inside a JS loop!');</script>";
		flush();
		//echo "sample";
	}
?>