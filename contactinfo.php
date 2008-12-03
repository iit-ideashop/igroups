<?php
	include_once("globals.php");
	include_once( "checklogingroupless.php" );
	$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");
	if ($result = mysql_fetch_array($query))
		$profile = $result;
	else {
		$db->igroupsQuery("INSERT INTO Profiles (iPersonID) VALUES({$currentUser->getID()})");
		$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$db->igroupsInsertID()}");
		$profile = mysql_fetch_array($query);
	}
	if ( isset( $_POST['update'] ) ) {
		$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");					     $profile = mysql_fetch_array($query);

		if ( $_POST['pw1'] == $_POST['pw2'] )
			$currentUser->setPassword( $_POST['pw1'] );
		$currentUser->updateDB();
		if (isset($_POST['altEmail']))
			$db->igroupsQuery("UPDATE Profiles SET sAltEmail='{$_POST['altEmail']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['phone']))
			$db->igroupsQuery("UPDATE Profiles SET sPhone='{$_POST['phone']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['phone2']))
			$db->igroupsQuery("UPDATE Profiles SET sPhone2='{$_POST['phone2']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['im']))
			$db->igroupsQuery("UPDATE Profiles SET sIM='{$_POST['im']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['nickname']))
			$db->igroupsQuery("UPDATE Profiles SET sNickname='{$_POST['nickname']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['major']))
			$db->igroupsQuery("UPDATE Profiles SET sMajor='{$_POST['major']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['year']))
			$db->igroupsQuery("UPDATE Profiles SET sYear='{$_POST['year']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['hometown']))
			$db->igroupsQuery("UPDATE Profiles SET sHometown='{$_POST['hometown']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['isResident']))
			$db->igroupsQuery("UPDATE Profiles SET isResident={$_POST['isResident']} WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['bio']))
			$db->igroupsQuery("UPDATE Profiles SET sBio='{$_POST['bio']}' WHERE iPersonID={$currentUser->getID()}");
		if (isset($_POST['skills']))
			$db->igroupsQuery("UPDATE Profiles SET sSkills='{$_POST['skills']}' WHERE iPersonID={$currentUser->getID()}");
		if(is_numeric($_POST['skin']) && ($_POST['skin'] == 0 || mysql_num_rows($db->igroupsQuery('select * from Skins where iID='.$_POST['skin'].' and bPublic=1'))))
			$db->igroupsQuery('update People set iSkin='.$_POST['skin'].' where iID='.$currentUser->getID());
		if (isset($_FILES['picture'])) {
			if ( $_FILES['picture']['error'] == UPLOAD_ERR_OK && @getimagesize($_FILES['picture']['tmp_name']) && @is_uploaded_file($_FILES['picture']['tmp_name']) && ($_FILES['picture']['type'] == 'image/gif' || $_FILES['picture']['type'] == 'image/jpeg' || $_FILES['picture']['type'] == 'image/bmp' || $_FILES['picture']['type'] == 'image/x-windows-bmp' || $_FILES['picture']['type'] == 'image/png' || $_FILES['picture']['type'] == 'image/pjpeg')) {
			    $newName = $currentUser->getID() . substr($_FILES['picture']['name'],strlen($_FILES['picture']['name'])-4);
			    move_uploaded_file($_FILES['picture']['tmp_name'], "profile-pics/$newName");
			    $db->igroupsQuery("UPDATE Profiles SET sPicture='$newName' WHERE iPersonID={$currentUser->getID()}");
			}
		}
		if (isset($_POST['delPicture'])) {
			$db->igroupsQuery("UPDATE Profiles SET sPicture=NULL WHERE iPersonID={$currentUser->getID()}");
			unlink("profile-pics/{$profile['sPicture']}");
		}

		$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");
		$profile = mysql_fetch_array($query);

		if (!$error)
			$message = "Your profile was successfully updated";
		else
			$message = $error;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Contact Info</title>
<?php require("appearance.php"); ?>
</head>
<body>
<?php
	require("sidebar.php");
?>
	<div id="content"><h1>Update My Profile</h1>
	<p>If you do not want to update or provide a piece of information, simply leave it blank.</p>
	<form method="post" action="contactinfo.php" enctype="multipart/form-data">
<?php
		print "<fieldset><legend>Contact Information</legend>\n";
		print "<table cellspacing=\"5\">\n";
		print "<tr><td>Primary E-mail: </td><td>{$currentUser->getEmail()}</td></tr>\n";
		print "<tr><td><label for=\"altEmail\">Alternate E-mail:</label></td><td><input type=\"text\" name=\"altEmail\" id=\"altEmail\" value=\"".$profile['sAltEmail']."\" /></td></tr>\n";
		print "<tr><td><label for=\"phone\">Primary Phone #:</label> </td><td><input type=\"text\" name=\"phone\" id=\"phone\" value=\"".$profile['sPhone']."\" /></td></tr>\n";
		print "<tr><td><label for=\"phone2\">Home/Other Phone #:</label> </td><td><input type=\"text\" name=\"phone2\" id=\"phone2\" value=\"".$profile['sPhone2']."\" /></td></tr>\n";
		print "<tr><td><label for=\"im\">AIM Screen Name:</label> </td><td><input type=\"text\" name=\"im\" id=\"im\" value=\"{$profile['sIM']}\" /></td></tr>\n";
		print "</table></fieldset>\n";

		print "<fieldset><legend>About Me</legend>\n";
		print "<table cellspacing=\"5\">\n";
		print "<tr><td><label for=\"nickname\">Nickname:</label> </td><td><input type=\"text\" name=\"nickname\" id=\"nickname\" value=\"{$profile['sNickname']}\" /></td></tr>\n";
		print "<tr><td><label for=\"major\">Major:</label> </td><td><input type=\"text\" name=\"major\" id=\"major\" value=\"{$profile['sMajor']}\" /></td></tr>\n";
		print "<tr><td><label for=\"year\">Year:</label> </td><td><select name=\"year\" id=\"year\">\n";
		if ($profile['sYear'] == 'Freshman')
			print "<option value=\"Freshman\" selected=\"selected\">Freshman</option>\n";
		else
			print "<option value=\"Freshman\">Freshman</option>\n";
		if ($profile['sYear'] == 'Sophomore')
			print "<option value=\"Sophomore\" selected=\"selected\">Sophomore</option>\n";
		else
			print "<option value=\"Sophomore\">Sophomore</option>\n";
		if ($profile['sYear'] == 'Junior')
			print "<option value=\"Junior\" selected=\"selected\">Junior</option>\n";
		else
			print "<option value=\"Junior\">Junior</option>\n";
		if ($profile['sYear'] == 'Senior')
			print "<option value=\"Senior\" selected=\"selected\">Senior</option>\n";
		else
			print "<option value=\"Senior\">Senior</option>\n";
		if ($profile['sYear'] == 'Graduate')
			print "<option value=\"Graduate\" selected=\"selected\">Graduate</option>\n";
		else
			print "<option value=\"Graduate\">Graduate</option>\n";
		print "</select></td></tr>\n";
		print "<tr><td><label for=\"hometown\">Hometown:</label> </td><td><input type=\"text\" name=\"hometown\" id=\"hometown\" value=\"{$profile['sHometown']}\" /></td></tr>\n";
		if ($profile['isResident'] == 1)
			print "<tr><td>Live on Campus? </td><td><input type=\"radio\" name=\"isResident\" id=\"isRes1\" value=\"1\" checked=\"checked\" />&nbsp;<label for=\"isRes1\">Yes</label>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"isResident\" id=\"isRes2\" value=\"0\" />&nbsp;<label for=\"isRes2\">No</label></td></tr>\n";
		else
			print "<tr><td>Live on Campus? </td><td><input type=\"radio\" name=\"isResident\" id=\"isRes1\" value=\"1\" />&nbsp;<label for=\"isRes1\">Yes</label>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"isResident\" value=\"0\" id=\"isRes2\" checked=\"checked\" />&nbsp;<label for=\"isRes2\">No</label></td></tr>\n";
		print "<tr><td valign=\"top\"><label for=\"bio\">Biography:</label> </td><td><textarea name=\"bio\" id=\"bio\" cols=\"65\" rows=\"6\">{$profile['sBio']}</textarea></td></tr>\n";
		print "<tr><td valign=\"top\"><label for=\"skills\">Skills:</label> </td><td><textarea name=\"skills\" id=\"skills\" cols=\"65\" rows=\"6\">{$profile['sSkills']}</textarea></td></tr>\n";
		if ($error)
			print "<tr><td>$error</td></tr>\n";
		if ($profile['sPicture'])
			print "<tr><td>Profile Picture Uploaded<br /><label for=\"delPicture\">Delete?</label>&nbsp;<input type=\"checkbox\" name=\"delPicture\" id=\"delPicture\" value=\"true\" /></td></tr>\n";
		else
			print "<tr><td><label for=\"picture\">Profile Picture:</label></td><td><input type=\"file\" name=\"picture\" id=\"picture\" /></td></tr>";
		$query = $db->igroupsQuery('select * from Skins');
		$userskin = mysql_fetch_row($db->igroupsQuery('select iSkin from People where bPublic=1 and iID='.$currentUser->getID()));
		$skins = "<select name=\"skin\" id=\"skin\">\n";
		$skins .= "<option value=\"0\"".($userskin[0] == 0 ? ' selected="selected"' : '').">Use default</option>\n";
		while($row = mysql_fetch_array($query))
		{
			$skins .= "<option value=\"{$row['iID']}\"".($userskin[0] == $row['iID'] ? ' selected="selected"' : '').">".str_replace('&', '&amp;', stripslashes($row['sName']))."</option>\n";
		}
		$skins .= "</select>\n";
		print "<tr><td><label for=\"skin\">Skin:</label></td><td>$skins</td></tr>\n";
		print "</table>\n";
		print "<input type=\"submit\" name=\"update\" value=\"Update Profile\" /></fieldset>\n";

/*		print "<fieldset><legend>Change Password</legend>\n";
		print "<label for=\"pw1\">New password:</label><input type=\"password\" name=\"pw1\" id=\"pw1\" /><br />\n";
		print "<label for=\"pw2\">Confirm password:</label><input type=\"password\" name=\"pw2\" id=\"pw2\" /><br />";*/
?>
		<!--input type="submit" name="update" value="Change Password" /></fieldset-->
	</form>
<a href="http://sloth.iit.edu/~iproadmin/userpassword.php">Change my password</a>
</div>
</body>
</html>
