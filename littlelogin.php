<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Login</title>
<link rel="stylesheet" href="default.css" type="text/css" />
</head><body>
<form method="post" action="<?php echo basename(); ?>"><fieldset><legend>Login</legend>
		<label for="username">User name:</label><input name="username1" id="username1" type="text" /><br />
		<label for="password">Password:</label><input name="password1" id="password1" type="password" /><br />
		<input type="checkbox" name="remember" id="remember" value="true" />&nbsp;<label for="remember">Remember Me</label><br />
<?php
	foreach($_POST as $key => $val)
		echo "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
?>
		<input type="submit" name="login" value="Login" />
	</fieldset></form>
</body></html>
