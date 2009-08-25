<?php
	session_start();
	include_once('globals.php');
	include_once('classes/db.php');
	include_once('classes/person.php');
	include_once('classes/group.php');
	include_once('classes/email.php');

	$db = new dbConnection();
	
	$loggedIn = true;
	//Check to see if the user has a session active
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup'])) //If not, look for a remember me cookie
	{
		$userName = mysql_real_escape_string(stripslashes($_COOKIE['userID']));
		if(strpos($_POST['username1'], '@') === FALSE)
			$userName .= '@iit.edu';
		$user = $db->query("SELECT iID,sPassword FROM People WHERE sEmail='$userName'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
		else
			$loggedIn = false;
	}
	else
		$loggedIn = false;
	
	if(isset($_POST['help']))
	{
		mail($_POST['email'], "Your $appname Help Request", "We have received your inquiry and will respond to it as soon as possible.\n\nThank you for contacting us.\n\n-The $appname Team", "From:$contactemail");
		$user = $db->query("SELECT iID FROM People WHERE sEmail='".$_POST['email']."'");
		$id = (($row = mysql_fetch_row($user)) ? $row[0] : 753);
		$_POST['problem'] .= "\n\nUser Agent: {$_SERVER['HTTP_USER_AGENT']}";
		$help = createEmail('', 'Web based help request', $_POST['problem'], $id, 0, 14, 1, 0, 0, $db);
		$iid = $help->getID();
		mail($contactemail, "$appname Help Request [ID:$iid]", stripslashes($_POST['problem']), "From:".$_POST['email']);
		$db->query("UPDATE Emails SET sSubject='$appname Help Request [ID:$iid]' WHERE iID=$iid");
	}
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Help</title>
</head>
<body>
<?php
require('sidebar.php');
?>
<div id="content"><h1>Need help?</h1>
<?php
if(isset($_POST['help']))
	echo "<p>Your request for help has been sent and we will respond to it as soon as possible.</p>\n";
?>
<p>If you are <b>having trouble logging in</b>, try <a href="http://sloth.iit.edu/~iproadmin/userpassword.php?reset=1">resetting your password</a>.</p>
<p>If you are experiencing a <b>display bug</b>, your browser may be using a stale stylesheet. Try clearing your cache. If you don't know how to do this, Wikipedia has <a href="http://en.wikipedia.org/wiki/Wikipedia:Bypass_your_cache">instructions for commonly used browsers</a>.</p>
<p>You may also be interested in the <a href="reqs.php">list of supported browsers</a>.</p>
<p>If the above instructions fail to correct your problem, complete the form below. Please try to be as specific as you can. We will get back to you as soon as possible.</p>
<form method="post" action="needhelp.php"><fieldset><legend>Help Request</legend>
<?php
	if($loggedIn)
	{
		$email = htmlspecialchars(stripslashes($currentUser->getEmail()));
		echo "<input type=\"hidden\" name=\"email\" value=\"$email\" />\n";
	}
	else
		echo "<label for=\"email\">Email address:</label><input type=\"text\" name=\"email\" id=\"email\" /><br />\n";
?>
<label for="problem">Please describe the problem you are having in as much detail as possible:</label><br />
<textarea name="problem" id="problem" rows="10" cols="50"></textarea><br /><br />
<input type="submit" name="help" value="Report Problem" />
</fieldset></form></div>
</body>
</html>
