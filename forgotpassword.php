<?php
//Use unified password reset script

header('Location: https://igroups.iit.edu/userpassword/index.php?reset=1', true, 302);
exit;

	include_once('globals.php');
	
	if(isset($_POST['resetPW']))
	{
		setcookie('username', '', time()-60);
		setcookie('password', '', time()-60);
	}
	
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Forgotten Password</title>
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content"><h1>Password Reset Form</h1>
<?php
require_once("classes/db.php");
require_once("classes/person.php");

if(isset($_POST['resetPW']))
{
	$db = new dbConnection();
	$ur = $db->query("SELECT iID FROM People WHERE sEmail='".$_POST['email']."'");
	if($row = mysql_fetch_row($ur))
	{
		for($i = 0; $i < 8; $i++)
			$pw .= chr(rand(65, 90));
		$user = new Person($row[0], $db);
		$user->setPassword($pw);
		$user->updateDB();
		mail($_POST['email'], "Your $appname password has been reset", "Your password is:\n$pw\nPasswords are case-sensitive.\nYou should change your password (in My Profile) the next time you log in.", "From: $contactemail");
		echo "<p>Your password has been reset. An email has been sent to you containing your new password.</p>\n";
	}
	else
		echo "<p class=\"errorText\">Invalid email address</p>\n";
}
?>
<p>To reset your password, simply enter your email address in the form below. An email will be sent to you containing a new password.</p>
<form action="forgotpassword.php" method="post"><fieldset>
<label>Enter email address: <input type="text" name="email" /></label>
<input type="submit" name="resetPW" value="Reset Password" />
</fieldset></form></div></body></html>
