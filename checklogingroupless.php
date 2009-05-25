<?php
	session_start();
	include_once("classes/db.php");
	include_once("classes/person.php");
	$db = new dbConnection();
	
	//If we have an "on-the-fly" login
	if(isset($_POST['username1']) && isset($_POST['password1']))
	{
		$userName = mysql_real_escape_string(stripslashes($_POST['username1']));
		if(strpos($_POST['username1'], '@') === FALSE)
			$userName .= '@iit.edu';
		$user = $db->igroupsQuery("SELECT iID,sPassword FROM People WHERE sEmail='$userName'");
		if(($row = mysql_fetch_row($user)) && (md5($_POST['password1']) == $row[1])) //Success! Set session variables.
		{
			$_SESSION['userID'] = $row[0];
			if(isset($_POST['remember']))
			{
				setcookie('userID', $_POST['username1'], time()+60*60*24*7);
				setcookie('password', $_POST['password1'], time()+60*60*24*7);
			}
			if(isset($_COOKIE['selectedGroup']))
			{
				$group = explode(",", $_COOKIE['selectedGroup']);
				$_SESSION['selectedGroup'] = $group[0];
				$_SESSION['selectedGroupType'] = $group[1];
				$_SESSION['selectedSemester'] = $group[2];
			}
		}
		else
		{
			include_once("littlelogin.php");
			die();
		}
	}
	
	//Check to see if the user has a session active
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup'])) //If not, look for a remember me cookie
	{
		$userName = mysql_real_escape_string(stripslashes($_COOKIE['userID']));
		if(strpos($_POST['username1'], '@') === FALSE)
			$userName .= '@iit.edu';
		$user = $db->igroupsQuery("SELECT iID,sPassword FROM People WHERE sEmail='$userName'");
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
		{
			include_once("littlelogin.php");
			die();
		}
	}
	else
	{
		include_once("littlelogin.php");
		die();
	}
?>
