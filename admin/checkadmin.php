<?php	
	session_start();
	include_once('../classes/db.php');
	include_once('../classes/person.php');
	$db = new dbConnection();
	
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], '@') === FALSE)
			$userName = $_COOKIE['userID'].'@iit.edu';
		else
			$userName = $_COOKIE['userID'];
		$user = $db->query("SELECT iID,sPassword FROM People WHERE sEmail=\"$userName\"");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(',', $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
		else
			errorPage('Credentials Required', 'You are not logged in.', 403);
	}
	else
		errorPage('Credentials Required', 'You are not logged in.', 403);
	if(!$currentUser->isAdministrator())
		errorPage('Credentials Required', 'You must be an administrator to access this page.', 403);
?>
