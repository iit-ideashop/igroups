<?php
	session_start();
	
	include_once( "classes/db.php" );

        $db = new dbConnection();

        //-----Process Login------------------------//

        if ( (isset($_COOKIE['userID']) && isset($_COOKIE['password'])) && !$_GET['logout'] ) {
               	header("Location: menu.php");
        }

	if (isset($_SESSION['userID']) && !$_GET['logout']) {
                header("Location: menu.php");
        }

ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Login</title>
	<style type="text/css">
		@import url("default.css");
		
		body {
			margin:0;
		}
		
		#sidebar {
			font-family: Arial, Helvetica, sans-serif;
			font-size:10pt;
			width:200px;
			background-color:#EEEEEE;
			background-image: url('img/menu-right-border.gif');
			background-repeat: repeat-y;
			background-position: right;
			padding-top:5px;
			padding-left:5px;
		}
		
		#sidebar-bottom {
			height:33px;
			background-image: url('img/menu-bottom-border.gif');
			background-repeat: no-repeat;
			background-position: right;
		}
		
		#loginform {
			margin-top:10px;
		}
	</style>
</head>

<body>
<div id="sidebar">
	<div id="iprologo">
		<a href="http://ipro.iit.edu/home/main.php" target="_parent"><img width=180 border="0" padding="0" margin="0" src="img/iprologo.jpg"></a>
	</div>
	<div id="igroupslogo">
		<img src="img/igroupslogo.jpg">
	</div>
	
	<div id="loginform">
<?php
		if ( isset( $_GET['logout'] ) ) {
			session_destroy();
			setcookie('username', '', time()-60);
			setcookie('password', '', time()-60);
?>
			<script type="text/javascript">
					parent.mainFrame.location.href="main.php";
			</script>
<?php
ob_end_flush();
		}
	
		if ( isset ( $_SESSION['loginError'] ) )
			print "<strong>Invalid username or password.</strong><br>";
		unset( $_SESSION['loginError'] );
?>
		<form method="post" action="menu.php">
			User name: <input name="username" type="text" /><br />
			Password: <input name="password" type="password" /><br />
			<input type='checkbox' name='remember' value='true'>&nbsp;Remember Me<br />
			<input type="submit" name="login" value="Login" />
		</form>
	</div>
	<p>
	<a href='forgotpassword.php' target='mainFrame'>Forgot password?</a><br />
	<a href='UM_iGROUPS.pdf' target='_top'>iGROUPS User Manual</a><br />
	<a href='http://iknow.iit.edu' target='_top'>Visit iKNOW</a><br />
	<a href='needhelp.php' target='mainFrame'>Need help?</a>
	</p>
	<div id="sidebar-bottom"></div>
</div>
</body>
</html>
