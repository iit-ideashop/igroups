<?php
	include_once("globals.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Login</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
</head><body>
<p>Your session has expired due to inactivity. Please log in to continue.</p>
<form method="post" action="<?php echo  $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>"><fieldset><legend>Login</legend>
		<label for="username">User name:</label><input name="username1" id="username1" type="text" /><br />
		<label for="password">Password:</label><input name="password1" id="password1" type="password" /><br />
		<input type="checkbox" name="remember" id="remember" value="true" />&nbsp;<label for="remember">Remember Me</label><br />
<?php
	foreach($_POST as $key => $val)
	{
		if(is_array($_POST[$key]))
		{
			foreach($_POST[$key] as $key2 => $val2)
				echo "<input type=\"hidden\" name=\"".$key."[".$key2."]\" value=\"$val2\" />\n";
		}
		else
			echo "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
	}
?>
		<input type="submit" name="login" value="Login" />
	</fieldset></form>
</body></html>
