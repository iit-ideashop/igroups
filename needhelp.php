<?php
	session_start();
	include_once("globals.php");
	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/email.php" );

	$db = new dbConnection();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Help</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
</head>
<body>
<?php
require("sidebar.php");
if ( isset( $_POST['help'] ) ) {
	mail( $_POST['email'], "Your $appname Help Request", "We have received your inquiry and will respond to it as soon as possible.\n\nThank you for contacting us.\n\n-The $appname Team", "From:$contactemail" );
	$user = $db->iknowQuery( "SELECT iID FROM People WHERE sEmail='".$_POST['email']."'" );
	if ( $row = mysql_fetch_row( $user ) ) {
		$id = $row[0];
	}
	else
		$id = 753;
	$help = createEmail( '', 'Web based help request', $_POST['problem'], $id, 0, 14, 1, 0, 0, $db );
	$iid = $help->getID();
	mail( "$contactemail", "$appname Help Request [ID:$iid]", $_POST["problem"], "From:".$_POST['email'] );
	$db->igroupsQuery( "UPDATE Emails SET sSubject='$appname Help Request [ID:$iid]' WHERE iID=$iid" );
}

if ( isset( $_SESSION['iUserID'] ) ) {
	$query = "SELECT sEmail FROM People WHERE iID=".$_SESSION['iUserID'];
	$result = query_db( $query, $iknowDB );
	if ( $row = mysql_fetch_row( $result ) ) {
		$email = $row[0];
	}
}
?>
<div id="content"><h1>Need help?</h1>
<?php
if ( isset( $_POST['help'] ) ) {
	print("Your request for help has been sent and we will respond to it as soon as possible.<br>");
}
?>
<p>If you are having trouble logging in, try <a href="<?php echo $appurl; ?>/forgotpassword.php">resetting your password</a>.</p>
<p>If this fails to correct your problem, complete the form below including your login e-mail address and your IPRO number.</p>
<form method="post" action="needhelp.php"><fieldset>
<?php
	if ( isset( $email ) ) {
		print("<input type=\"hidden\" name=\"email\" value=\"$email\" />");
	}
	else {
		print("<label for=\"email\">Email address:</label><input type=\"text\" name=\"email\" id=\"email\" />");
	}
?>
<label for="problem">Please describe the problem you are having in as much detail as possible:</label><br />
<textarea name="problem" id="problem" rows="10" cols="50"></textarea><br /><br />
<input type="submit" name="help" value="Report Problem" />
</fieldset></form></div>
</body>
</html>
