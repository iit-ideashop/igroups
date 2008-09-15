<?php
	if (isset($_SESSION['userID']) && !$_GET['logout'])
	{
		require("menu.php");
	}
	else
	{
		require("login.php");
	}
	if(isset($message))
		echo "<div id=\"messageBox\">$message</div>";
?>
