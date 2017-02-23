<?php
	include_once('globals.php');
	include_once('checklogingroupless.php');
	
	$query = $db->query("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");
	if ($result = mysql_fetch_array($query))
		$profile = $result;
	else 
	{
		$db->query("INSERT INTO Profiles (iPersonID) VALUES({$currentUser->getID()})");
		$query = $db->query("SELECT * FROM Profiles WHERE iPersonID={$db->insertID()}");
		$profile = mysql_fetch_array($query);
	}
	
	//------Start of Code for Form Processing-----------------------//
	if(isset($_POST['update'])) 
	{
		$clean = array();
		foreach($_POST as $key => $val)
			$clean[$key] = mysql_real_escape_string(stripslashes($val));
		$query = $db->query("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");
		$profile = mysql_fetch_array($query);

		if($_POST['pw1'] == $_POST['pw2'])
			$currentUser->setPassword($_POST['pw1']);
		$currentUser->updateDB();
		if(isset($_POST['altEmail']))
			$db->query("UPDATE Profiles SET sAltEmail='{$clean['altEmail']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['phone']))
			$db->query("UPDATE Profiles SET sPhone='{$clean['phone']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['phone2']))
			$db->query("UPDATE Profiles SET sPhone2='{$clean['phone2']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['im']))
			$db->query("UPDATE Profiles SET sIM='{$clean['im']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['nickname']))
			$db->query("UPDATE Profiles SET sNickname='{$clean['nickname']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['major']))
			$db->query("UPDATE Profiles SET sMajor='{$clean['major']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['year']))
			$db->query("UPDATE Profiles SET sYear='{$clean['year']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['hometown']))
			$db->query("UPDATE Profiles SET sHometown='{$clean['hometown']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['isResident']))
			$db->query("UPDATE Profiles SET isResident={$_POST['isResident']} WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['bio']))
			$db->query("UPDATE Profiles SET sBio='{$clean['bio']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['skills']))
			$db->query("UPDATE Profiles SET sSkills='{$clean['skills']}' WHERE iPersonID={$currentUser->getID()}");
		if(isset($_POST['sig']))
			$db->query("UPDATE Profiles SET sSig='{$clean['sig']}' WHERE iPersonID={$currentUser->getID()}");
		if(is_numeric($_POST['skin']) && ($_POST['skin'] == 0 || mysql_num_rows($db->query('select * from Skins where iID='.$_POST['skin'].' and bPublic=1'))))
			$db->query('update People set iSkin='.$_POST['skin'].' where iID='.$currentUser->getID());
		$b = ($_POST['receive'] ? 1 : 0);
		$db->query("update People set bReceiveNotifications=$b where iID={$currentUser->getID()}");
		if(isset($_FILES['picture']))
		{
			if($_FILES['picture']['error'] == UPLOAD_ERR_OK && @getimagesize($_FILES['picture']['tmp_name']) && @is_uploaded_file($_FILES['picture']['tmp_name']) && ($_FILES['picture']['type'] == 'image/gif' || $_FILES['picture']['type'] == 'image/jpeg' || $_FILES['picture']['type'] == 'image/bmp' || $_FILES['picture']['type'] == 'image/x-windows-bmp' || $_FILES['picture']['type'] == 'image/png' || $_FILES['picture']['type'] == 'image/pjpeg'))
			{
				$newName = $currentUser->getID().substr($_FILES['picture']['name'], strlen($_FILES['picture']['name']) - 4);
				move_uploaded_file($_FILES['picture']['tmp_name'], "profile-pics/$newName");
				$db->query("UPDATE Profiles SET sPicture='$newName' WHERE iPersonID={$currentUser->getID()}");
			}
		}
		if(isset($_POST['delPicture']))
		{
			$db->query("UPDATE Profiles SET sPicture=NULL WHERE iPersonID={$currentUser->getID()}");
			unlink("profile-pics/{$profile['sPicture']}");
		}

		$query = $db->query("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");
		$profile = mysql_fetch_array($query);

		if(!$error)
			$message = "Your profile was successfully updated";
		else
			$message = $error;
	}
	
	//------End of Code for Form Processing-------------------------//
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Contact Info</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
</head>
<body>
<?php
	/**** begin html head *****/
   require('htmlhead.php'); //starts main container
/****end html head content ****/	
?>
	<h1>Update My Profile</h1>
	<p>If you do not want to update or provide a piece of information, simply leave it blank.</p>
	<form method="post" action="contactinfo.php" enctype="multipart/form-data">
<?php
	echo "<fieldset><legend>Contact Information</legend>\n";
	echo "<table cellspacing=\"5\">\n";
	echo "<tr><td>Primary E-mail: </td><td>{$currentUser->getEmail()}</td></tr>\n";
	echo "<tr><td><label for=\"altEmail\">Alternate E-mail:</label></td><td><input type=\"text\" name=\"altEmail\" id=\"altEmail\" value=\"".$profile['sAltEmail']."\" /></td></tr>\n";
	echo "<tr><td><label for=\"phone\">Primary Phone #:</label> </td><td><input type=\"text\" name=\"phone\" id=\"phone\" value=\"".$profile['sPhone']."\" /></td></tr>\n";
	echo "<tr><td><label for=\"phone2\">Home/Other Phone #:</label> </td><td><input type=\"text\" name=\"phone2\" id=\"phone2\" value=\"".$profile['sPhone2']."\" /></td></tr>\n";
	echo "<tr><td><label for=\"im\">AIM Screen Name:</label> </td><td><input type=\"text\" name=\"im\" id=\"im\" value=\"{$profile['sIM']}\" /></td></tr>\n";
	echo "</table></fieldset>\n";

	echo "<fieldset><legend>About Me</legend>\n";
	echo "<table cellspacing=\"5\">\n";
	echo "<tr><td><label for=\"nickname\">Nickname:</label> </td><td><input type=\"text\" name=\"nickname\" id=\"nickname\" value=\"{$profile['sNickname']}\" /></td></tr>\n";
	echo "<tr><td><label for=\"major\">Major:</label> </td><td><input type=\"text\" name=\"major\" id=\"major\" value=\"{$profile['sMajor']}\" /></td></tr>\n";
	
	echo "<tr><td><label for=\"year\">Year:</label> </td><td><select name=\"year\" id=\"year\">\n";
	$years = array('Freshman' => '', 'Sophomore' => '', 'Junior' => '', 'Senior' => '', 'Graduate' => '');
	if(array_key_exists($profile['sYear'], $years))
		$years[$profile['sYear']] = ' selected="selected"';
	foreach($years as $year => $sel)
		echo "<option value=\"$year\"$sel>$year</option>\n";
	echo "</select></td></tr>\n";
	
	echo "<tr><td><label for=\"hometown\">Hometown:</label> </td><td><input type=\"text\" name=\"hometown\" id=\"hometown\" value=\"{$profile['sHometown']}\" /></td></tr>\n";
	if($profile['isResident'] == 1)
		echo "<tr><td>Live on Campus? </td><td><input type=\"radio\" name=\"isResident\" id=\"isRes1\" value=\"1\" checked=\"checked\" />&nbsp;<label for=\"isRes1\">Yes</label>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"isResident\" id=\"isRes2\" value=\"0\" />&nbsp;<label for=\"isRes2\">No</label></td></tr>\n";
	else
		echo "<tr><td>Live on Campus? </td><td><input type=\"radio\" name=\"isResident\" id=\"isRes1\" value=\"1\" />&nbsp;<label for=\"isRes1\">Yes</label>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"isResident\" value=\"0\" id=\"isRes2\" checked=\"checked\" />&nbsp;<label for=\"isRes2\">No</label></td></tr>\n";
	echo "<tr><td valign=\"top\"><label for=\"bio\">Biography:</label> </td><td><textarea name=\"bio\" id=\"bio\" cols=\"65\" rows=\"6\">{$profile['sBio']}</textarea></td></tr>\n";
	echo "<tr><td valign=\"top\"><label for=\"skills\">Skills:</label> </td><td><textarea name=\"skills\" id=\"skills\" cols=\"65\" rows=\"6\">{$profile['sSkills']}</textarea></td></tr>\n";
	echo "<tr><td valign=\"top\"><label for=\"sig\">Email Signature:</label></td><td><textarea name=\"sig\" id=\"sig\" cols=\"65\" rows=\"6\">{$profile['sSig']}</textarea></td></tr>\n";
	if($error)
		echo "<tr><td>$error</td></tr>\n";
	if($profile['sPicture'])
		echo "<tr><td>Profile Picture Uploaded<br /><label for=\"delPicture\">Delete?</label>&nbsp;<input type=\"checkbox\" name=\"delPicture\" id=\"delPicture\" value=\"true\" /></td></tr>\n";
	else
		echo "<tr><td><label for=\"picture\">Profile Picture:</label></td><td><input type=\"file\" name=\"picture\" id=\"picture\" /></td></tr>";
	$query = $db->query('select * from Skins where bPublic=1');
	$userskin = mysql_fetch_row($db->query('select iSkin from People where iID='.$currentUser->getID()));
	$skins = "<select name=\"skin\" id=\"skin\">\n";
	$skins .= "<option value=\"0\"".($userskin[0] == 0 ? ' selected="selected"' : '').">Use default</option>\n";
	while($row = mysql_fetch_array($query))
	{
		$skins .= "<option value=\"{$row['iID']}\"".($userskin[0] == $row['iID'] ? ' selected="selected"' : '').">".str_replace('&', '&amp;', stripslashes($row['sName']))."</option>\n";
	}
	$skins .= "</select>\n";
	echo "<tr><td><label for=\"skin\">Skin:</label></td><td>$skins</td></tr>\n";
	$ischecked = $currentUser->receivesNotifications() ? ' checked="checked"' : '';
	echo "<tr><td><label for=\"receive\">Receive task notifications by email:</label></td><td><input type=\"checkbox\" name=\"receive\"$ischecked /></td></tr>\n";
	echo "</table>\n";
	echo "<input type=\"submit\" name=\"update\" value=\"Update Profile\" /></fieldset>\n";

//Password change form now at http://sloth.iit.edu/~iproadmin/userpassword.php
	echo "<fieldset><legend>Change Password</legend>\n";
	echo "<label for=\"pw1\">New password:</label><input type=\"password\" name=\"pw1\" id=\"pw1\" /><br />\n";
	echo "<label for=\"pw2\">Confirm password:</label><input type=\"password\" name=\"pw2\" id=\"pw2\" /><br />";
	echo "<input type=\"submit\" name=\"update\" value=\"Change Password\" /></fieldset>\n";
?>
	</form>


<?php
//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>	
</body></html>
