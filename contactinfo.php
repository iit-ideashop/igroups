<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) {
		$currentUser = new Person( $_SESSION['userID'], $db );
		$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");
		if ($result = mysql_fetch_array($query))
			$profile = $result;
		else {
			$db->igroupsQuery("INSERT INTO Profiles (iPersonID) VALUES({$currentUser->getID()})");
			$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$db->igroupsInsertID()}");
			$profile = mysql_fetch_array($query);
		}
	}
	else
		die("You are not logged in.");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Contact Info</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<script type="text/javascript">
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
	</script>
</head>
<body>
<?php
require("sidebar.php");
	if ( isset( $_POST['update'] ) ) {
		$query = $db->igroupsQuery("SELECT * FROM Profiles WHERE iPersonID={$currentUser->getID()}");                                             $profile = mysql_fetch_array($query);

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
		if (isset($_FILES['picture'])) {
			if ( $_FILES['picture']['error'] == UPLOAD_ERR_OK && @getimagesize($_FILES['picture']['tmp_name']) && @is_uploaded_file($_FILES['picture']['tmp_name']) && ($_FILES['picture']['type'] == 'image/gif' || $_FILES['picture']['type'] == 'image/jpeg' || $_FILES['picture']['type'] == 'image/bmp' || $_FILES['picture']['type'] == 'image/x-windows-bmp' || $_FILES['picture']['type'] == 'image/png')) {
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

		if (!$error) {
?>
		<script type="text/javascript">
			showMessage("Your profile was successfully updated");
		</script>
<?php
		}
		else {
?>
		<script type="text/javascript">
                        showMessage("<?php print "$error"; ?>");
                </script>
<?php
		}
	}
?>
	<div id="content"><h1>Update My Profile</h1>
	<p>If you do not want to update or provide a piece of information, simply leave it blank.</p>
	<form method="post" action="contactinfo.php" enctype="multipart/form-data">
<?php
		print "<h4>Contact Information</h4>";
		print "<table cellspacing='5'>";
		print "<tr><td>Primary E-mail: </td><td>{$currentUser->getEmail()}</td></tr>";
		print "<tr><td>Alternate E-mail: </td><td><input type='text' name='altEmail' value='".$profile['sAltEmail']."' /></td></tr>";
		print "<tr><td>Primary Phone #: </td><td><input type='text' name='phone' value='".$profile['sPhone']."' /></td></tr>";
		print "<tr><td>Home/Other Phone #: </td><td><input type='text' name='phone2' value='".$profile['sPhone2']."' /></td></tr>";
		print "<tr><td>AIM Screen Name: </td><td><input type='text' name='im' value='{$profile['sIM']}' /></td></tr>";
		print "</table>";

		print "<h4>About Me</h4>";
		print "<table cellspacing='5'>";
		print "<tr><td>Nickname: </td><td><input type='text' name='nickname' value='{$profile['sNickname']}' /></td></tr>";
		print "<tr><td>Major: </td><td><input type='text' name='major' value='{$profile['sMajor']}' /></td></tr>";
		print "<tr><td>Year: </td><td><select name='year'>";
		if ($profile['sYear'] == 'Freshman')
			print "<option value='Freshman' selected=\"selected\">Freshman</option>";
		else
			print "<option value='Freshman'>Freshman</option>";
		if ($profile['sYear'] == 'Sophomore')
			print "<option value='Sophomore' selected=\"selected\">Sophomore</option>";
		else
			print "<option value='Sophomore'>Sophomore</option>";
		if ($profile['sYear'] == 'Junior')
			print "<option value='Junior' selected=\"selected\">Junior</option>";
		else
			print "<option value='Junior'>Junior</option>";
		if ($profile['sYear'] == 'Senior')
			print "<option value='Senior' selected=\"selected\">Senior</option>";
		else
			print "<option value='Senior'>Senior</option>";
		if ($profile['sYear'] == 'Graduate')
			print "<option value='Graduate' selected=\"selected\">Graduate</option>";
		else
			print "<option value='Graduate'>Graduate</option>";
		print "</select></td></tr>";
		print "<tr><td>Hometown: </td><td><input type='text' name='hometown' value='{$profile['sHometown']}' /></td></tr>";
		if ($profile['isResident'] == 1)
			print "<tr><td>Live on Campus? </td><td><input type='radio' name='isResident' value='1' checked=\"checked\" />&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type='radio' name='isResident' value='0' />&nbsp;No</td></tr>";
		else
			print "<tr><td>Live on Campus? </td><td><input type='radio' name='isResident' value='1' />&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type='radio' name='isResident' value='0' checked=\"checked\" />&nbsp;No</td></tr>";
		print "<tr><td valign='top'>Biography: </td><td><textarea name='bio' cols='65' rows='6'>{$profile['sBio']}</textarea></td></tr>";
		print "<tr><td valign='top'>Skills: </td><td><textarea name='skills' cols='65' rows='6'>{$profile['sSkills']}</textarea></td></tr>";
		if ($error)
			print "<tr><td>$error</td></tr>";
		if ($profile['sPicture'])
			print "<tr><td>Profile Picture Uploaded<br />Delete?&nbsp;<input type='checkbox' name='delPicture' value='true' /></td></tr>";
		else
			print "<tr><td>Profile Picture: </td><td><input type='file' name='picture' /></td></tr>";
		print "</table>";
		print "<input type='submit' name='update' value='Update Profile' />";

		print "<h1>Change Password</h1>";
		print "New password: <input type='password' name='pw1' /><br />";
		print "Confirm password: <input type='password' name='pw2' /><br />";
?>
		<input type='submit' name='update' value='Change Password' />
	</form></div>
</body>
</html>
