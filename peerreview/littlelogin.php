<?php
include_once('classes/db.php');
session_start();
$db = new dbConnection();
include_once('includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?></title>
</head><body>
<p>Your session has expired due to inactivity. Please log in to continue.</p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>"><fieldset><legend>Login</legend>
<?php
	echo "<label>User name: <input name=\"username1\" id=\"username1\" type=\"text\"$st></label><br$st>\n";
	echo "<label>Password: <input name=\"password1\" id=\"password1\" type=\"password\"$st></label><br$st>\n";
	echo "<label><input type=\"checkbox\" name=\"remember\" id=\"remember\" value=\"true\"$st> Remember Me</label><br$st>\n";

	foreach($_POST as $key => $val)
	{
		if(is_array($_POST[$key]))
		{
			foreach($_POST[$key] as $key2 => $val2)
				echo "<input type=\"hidden\" name=\"".$key."[".$key2."]\" value=\"$val2\"$st>\n";
		}
		else
			echo "<input type=\"hidden\" name=\"$key\" value=\"$val\"$st>\n";
	}

	echo "<input type=\"submit\" name=\"login\" value=\"Login\"$st>\n";
?>
	</fieldset></form>
</body></html>
