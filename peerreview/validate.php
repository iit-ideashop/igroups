<?php
include_once('classes/db.php');
include_once('classes/person.php');

function getUserID($username, $db)
{
	if(strpos($username, '@') === false) 
		$username .= '@iit.edu';
	$username = mysql_real_escape_string(stripslashes($username));
	$query = $db->query("select id from People where email='$username'");
	if($result = mysql_fetch_row($query))
		return $result[0];
	else
		return false; 
}

function validate_user($username, $password, $db)
{
	if(!($userID = getUserID($username, $db)))
		return false;
	
	$query = $db->query("select * from People where id='$userID'");
	$result = mysql_fetch_row($query);
	$user = new Person ($userID, $db);
	if($user->getPassword() == null)
		return $user->getID();
	else
		return (md5($password) == $user->getPassword()) ? $user->getID() : false;
}

?>
