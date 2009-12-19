<?php
	include_once('globals.php');
	
	echo "<div id=\"sidebar\">\n<!-- <div id=\"igroupslogo\"></div>-->\n";
	if (isset($_SESSION['userID']) && !$_GET['logout'])
		require('menu.php');
	else
		require('login.php');
	echo "</div>\n";
	if(isset($message))
		echo "<div id=\"messageBox\">$message</div>\n";
	else
		//echo "<div id=\"topSpacer\"></div>\n";
?>
