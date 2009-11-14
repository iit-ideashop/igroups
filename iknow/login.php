<?php
	include_once('../globals.php');
	include_once('../classes/db.php');

	$db = new dbConnection();

	//-----Process Login------------------------//

	ob_start();
	echo '<div id="loginform">';
	if(isset($_GET['logout']))
	{
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

	if(isset($_SESSION['loginError']))
		echo "<strong>Invalid username or password.</strong><br />";
	unset($_SESSION['loginError']);
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
<a href="../help/index.php" title="Help Center">Help Center</a><br />
<a href="../needhelp.php" title="Contact Us">Contact Us</a>
</p>
<hr />
<p>Return to <a href="http://sloth.iit.edu/~iproadmin/peerreview/">Peer Review</a> &#183; <a href="http://ipro.iit.edu">IPRO Website</a></p>