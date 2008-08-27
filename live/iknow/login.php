<?php
	
	include_once( "../classes/db.php" );

        $db = new dbConnection();

        //-----Process Login------------------------//

ob_start();
?>

<div id="sidebar">
	<div id="iprologo">
		<a href="http://ipro.iit.edu/home/index.php" title="IPRO Home"><img src="../img/iprologo.png" alt="IPRO" title="IPRO" /></a>
	</div>
	<div id="igroupslogo">
		<img src="../img/iGroupslogo.png" alt="iGroups" title="iGroups" />
	</div>
	
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
		<a href="../index.php">iGROUPS Home</a><br /><br />
		<a href="main.php">iKnow/iGroups Guest Access</a><br /><br />
		<form method="post" action="menu.php?loggingin=true">
			User name: <input name="username" type="text" /><br />
			Password: <input name="password" type="password" /><br />
			<input type="checkbox" name="remember" value="true" />&nbsp;Remember Me<br />
			<input type="submit" name="login" value="Login" />
		</form>
	</div>
	<p>
	<a href="../forgotpassword.php" title="Forgotten password">Forgot password?</a><br />
	<a href="../UM_iGROUPS.pdf" title="User manual">iGROUPS User Manual</a><br />
	<a href="../needhelp.php" title="Help">Need help?</a>
	</p>
	<div id="sidebar-bottom"></div>
</div>
