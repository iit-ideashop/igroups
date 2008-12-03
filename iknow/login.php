<?php
	include_once("../globals.php");
	include_once( "../classes/db.php" );

	$db = new dbConnection();

	//-----Process Login------------------------//

ob_start();
?>
<div id="loginform">
<?php
	if ( isset( $_GET['logout'] ) ) {
		session_destroy();
		setcookie('username', '', time()-60);
		setcookie('password', '', time()-60);
?>
		<script type="text/javascript">
		<!--
			window.location.href="../index.php";
		//-->
		</script>
<?php
ob_end_flush();
	}

	if ( isset ( $_SESSION['loginError'] ) )
		print "<strong>Invalid username or password.</strong><br />";
	unset( $_SESSION['loginError'] );
?>
	<a href="../index.php"><?php echo $appname; ?> Home</a><br /><br />
	<a href="../iknow/main.php">iKnow/iGroups Guest Access</a><br /><br />
	<form method="post" action="../menu.php?loggingin=true"><fieldset>
		<label for="username">User name:</label><input name="username" id="username" type="text" /><br />
		<label for="password">Password:</label><input name="password" id="password" type="password" /><br />
		<input type="hidden" name="logform" value="true" />
		<input type="checkbox" name="remember" id="remember" value="true" />&nbsp;<label for="remember">Remember Me</label><br />
		<input type="submit" name="login" value="Login" />
	</fieldset></form>
</div>
<p>
<a href="http://sloth.iit.edu/~iproadmin/userpassword.php?reset=1" title="Forgotten password">Forgot password?</a><br />
<a href="../UM_iGROUPS.pdf" title="User manual"><?php echo $appname; ?> User Manual</a><br />
<a href="../needhelp.php" title="Help">Need help?</a>
</p>
