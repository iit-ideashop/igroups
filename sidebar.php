<?php
	if (isset($_SESSION['userID']))
	{
                require("menu.php");
        }
	else
	{
		require("login.php");
	}
?>
