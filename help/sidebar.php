<?php
	include_once('../globals.php');
	echo "<div id=\"uniSidebar\">\n";
	if (isset($_SESSION['userID']) && !$_GET['logout'])
		require('../dboard/menu.php');
	else
		require('../dboard/login.php');
	echo "</div>\n";
	if(isset($message))
		echo "<div id=\"messageBox\">$message</div>";
?>
