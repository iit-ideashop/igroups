<?php
	include_once('../globals.php');
	echo "<div id=\"unisidebar\">\n<div id=\"igroupslogo\"></div>\n";
	if (isset($_SESSION['userID']) && !$_GET['logout'])
		require('../menu.php');
	else
		require('login.php');
	echo "</div>\n";
	if(isset($message))
		echo "<div id=\"messageBox\">$message</div>";
?>
