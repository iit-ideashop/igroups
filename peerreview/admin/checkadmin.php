<?php	
	session_start();
	include_once('../classes/db.php');
	include_once('../classes/person.php');
	$db = new dbConnection();
	
	if(isset($_SESSION['uID']))
		$currentUser = new Person($_SESSION['uID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']))
	{
		$userName = mysql_real_escape_string(stripslashes($_COOKIE['userID']));
		if(strpos($_COOKIE['userID'], '@') === FALSE)
			$userName .= '@iit.edu';
		$user = $db->query("SELECT id, password FROM People WHERE email='$userName'");
		if(($row = mysql_fetch_row($user)) && ($_COOKIE['password'] == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
		}
		else
			errorPage('Credentials Required', 'You are not logged in.', 403);
	}
	else
		errorPage('Credentials Required', 'You are not logged in.', 403);
	if($currentUser->isStudent())
		errorPage('Credentials Required', 'You must be an administrator to access this page.', 403);
?>
