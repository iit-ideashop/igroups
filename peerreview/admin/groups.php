<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/rating.php');
include_once('../classes/criterion.php');
include_once('../classes/criteriaList.php');

include_once('checkadmin.php');
	
if(isset($_SESSION['group']) && !is_numeric($_SESSION['group']))
	unset($_SESSION['group']);
if(!is_numeric($_GET['group']))
{
	unset($_GET['group']);
	$get = '';	
}
else
	$get = "?group={$_GET['group']}";

if(isset($_POST['createAdmin']) && isset($_POST['firstName']) && isset($_POST['lastName']) && isset($_POST['email']) && $currentUser->isAdministrator())
{
	createPerson($_POST['email'], $_POST['firstName'], $_POST['lastName'], 2, $db);
}

if(isset($_POST['makeFaculty']) && isset($_POST['userID']))
{
	$user = new Person($_POST['userID'], $db);
	if(!$user->isAdministrator())
		$user->makeFaculty();
}

if(isset($_POST['removeUser']) && isset($_POST['userID']))
{
	$user = new Person($_POST['userID'], $db);
	$user->removeFromGroup($_SESSION['group']);
}

//create group
if(isset($_POST['createGroup']) && is_numeric($_POST['criteriaList']))
{
	$group_name = trim($_POST['groupName']);
	if($group_name == '')
		$message = 'Group name cannot be blank.';
	else
	{
		$query = $db->query('select id from Groups where name="'.mysql_real_escape_string(stripslashes($group_name)).'"');
		if(mysql_num_rows($query))
			$message = 'A group with that name already exists.';
		else
		{
			$group = createGroup($group_name, $_POST['criteriaList'], $db);
			$_SESSION['group'] = $group->getID();
			$message = 'Group successfuly created.';
		}
	}
}	

//delete group
if(is_numeric($_GET['deleteGroup']) && $currentUser->isAdministrator())
{
	$delGroup = new Group($_GET['deleteGroup'], $db);
	$delGroup->delete();
	unset($_SESSION['group']);
	
	$message = 'Group successfuly deleted.';
}

if(isset($_POST['importGroup']) && is_numeric($_POST['igroupsGroup']) && $_POST['igroupsGroup'] > 0 && is_numeric($_POST['criteriaList']) && $currentUser->isAdministrator())
{
	$igroupsID = $_POST['igroupsGroup'];
	// getting group info from iGroups
	$query = $db->igroupsQuery("SELECT * FROM Projects WHERE iID=$igroupsID");
	if(mysql_num_rows($query) != 1)
		errorPage('Internal error', 'Trying to import multiple groups!', 500);
	$result = mysql_fetch_array($query);
	// insert into database
	$name = mysql_real_escape_string(stripslashes($result['sIITID']));
	$checkName = $db->query("select * from Groups where name=\"$name\"");
	if(mysql_num_rows($checkName))
		errorPage('Duplicate Group', 'A group with that name already exists.', 400);
	$newGroup = createGroup($name, $_POST['criteriaList'], $db);
	if(!$newGroup)
		errorPage('Error', 'Error importing group', 500);

	// get current semester
	$query = $db->igroupsQuery("SELECT iID from Semesters where bActiveFlag=1");
	$result = mysql_fetch_row($query);
	$currSemester = $result[0];

	// get group members
	$query = $db->igroupsQuery("SELECT m.iPersonID, p.sEmail, p.sFName, p.sLName, p.sPassword from PeopleProjectMap m, People p where m.iPersonID = p.iID AND m.iProjectID=$igroupsID and m.iSemesterID=$currSemester");
	$members = array();
	$i = 0;
	if(!mysql_num_rows($query))
		$message = 'Group created; no users found';
	else while($row = mysql_fetch_array($query))
	{
		$query2 = $db->igroupsQuery("select iAccessLevel from GroupAccessMap where iPersonID={$row['iPersonID']} AND iGroupID=$igroupsID AND iGroupType=0");
		$fac = false;
		if($row2 = mysql_fetch_row($query2))
		{
			if($row2[0] == 0) //Guest
				continue;
			else if($row2[0] == 2) //Administrator
				$fac = true;
		}
		$members[$i] = array();
		$members[$i]['fName'] = $row['sFName'];
		$members[$i]['lName'] = $row['sLName'];
		$members[$i]['email'] = $row['sEmail'];
		$members[$i]['password'] = $row['sPassword'];
		$members[$i]['type'] = ($fac ? 1 : 0);
		$i++;
	}

	// insert into groups
	foreach($members as $member)
	{
		$query = $db->query("SELECT id FROM People WHERE email='{$member['email']}'");
		if(!mysql_num_rows($query))
		{
			$person = createPerson($member['email'], $member['fName'], $member['lName'], $member['type'], $db);
			$success = $newGroup->addUser($person);
		}
		else
		{
			$result = mysql_fetch_row($query);
			$guy = new Person($result[0], $db);
			if($guy->isAdministrator())
				continue;
			$success = $newGroup->addUser($guy);
			if($member['type'] == 1 && !$guy->isFaculty())
				$guy->makeFaculty();
		}
		if(!$success)
			errorPage('Error Adding Users', 'There was an error adding user '.$member['email'].' to the new group. Please contact the administrator for assistance.', 500);
	}
	$message = 'Group imported successfully';
}

if(isset($_GET['selectGroup']) && $_GET['group'])
{
	$_SESSION['group'] = $_GET['group'];
}
else if(isset($_GET['selectGroup']))
{
	unset($_SESSION['group']);
	$currentGroup = 0;
}

if(isset($_SESSION['group']) && $_GET['group'] != 0)
	$currentGroup = new Group($_SESSION['group'], $db);
else if($_GET['group'] == 0)
	$currentGroup = 0;

$groups = array();

if($currentUser->isAdministrator())
{
	$query = $db->query('SELECT id FROM Groups ORDER BY name');
	
	while($row = mysql_fetch_row($query))
		$groups[] = new Group($row[0], $db);
}
else
	$groups = $currentUser->getGroups();

//creating user
if(isset($_POST['createUser']))
{
	$email = $_POST['email'];
	$first_name = $_POST['firstName'];
	$last_name = $_POST['lastName'];
	$type = $_POST['userType'];
	$group = $_POST['group'];
	$groupObj = new Group($_POST['group'], $db);
	
	$query = $db->query("SELECT * FROM People WHERE email='$email'");
	
	if(mysql_num_rows($query) > 0)
	{
		$row = mysql_fetch_row($query);
		$user = new Person($row[0], $db);
		$groupObj->addUser($user);
		$message = 'User successfuly added to group.';
	} 	
	else { 
		//create new user and add to group
		$user = createPerson($email, $first_name, $last_name, $type, $db);
		$groupObj->addUser($user);
		$message = 'User successfuly added to group.';
	}
}
include_once('../includes/header.php');
if(isset($message))
	echo "<script type=\"text/javascript\">showMessage(\"$message\");</script>\n";
?>
<script type="text/javascript">
//<![CDATA[
	function confirmation()
	{
		var answer = confirm("All data associated with this group will be deleted. Are you sure you want to do this?")
		if(answer)
			window.location = "groups.php<?php if (isset($currentGroup) && is_object($currentGroup)) echo "?deleteGroup={$currentGroup->getID()}";?>";
	}
	
	function confirmReset()
	{
		var answer = confirm("All data associated with this group will become archival and will no longer be able to be edited or viewed. A clean running of the peer review process in this semester will be set up. Are you sure you want to do this?")
		if(answer)
			window.location = "adminsurvey.php<?php if (isset($currentGroup) && is_object($currentGroup)) echo "?resetGroup={$currentGroup->getID()}";?>";
	}
	
	function checkInputForm()
	{
		var select = document.forms['import'].elements['igroupsGroup'].value;
		document.forms['import'].elements['importGroup'].disabled = (select == 0);
	}
//]]>
</script>
<title><?php echo $GLOBALS['systemname']; ?> - Manage Groups</title>
<?php
$onload = 'checkInputForm()';
include_once('../includes/sidebar.php');
?>
<div id="content">
<h2>Manage Groups</h2>
<p>Here you can create new groups and associate them with a survey list. Make sure you have the survey list created before you make a new group. Once the group has been created you can add users to it by first selecting the group from the dropdown menu under 'Edit a Group'. You may remove users from the group by clicking on the radio button in front of the user's name and clicking on 'Remove User from Group'. You may also designate faculty which is important if they are not meant to take the survey.</p>
<?php
if($currentUser->isAdministrator())
{
	$query = $db->igroupsQuery("SELECT p.iID, p.sIITID FROM Projects p, Semesters s, ProjectSemesterMap m WHERE p.sIITID NOT IN (SELECT name FROM prv2.Groups) AND s.iID = m.iSemesterID AND m.iProjectID = p.iID AND s.bActiveFlag=1 ORDER BY p.sIITID");
	echo "<hr$st><form action=\"groups.php\" method=\"post\" id=\"import\"><fieldset><legend>Import a Group From iGroups</legend>\n";
	echo "<select name=\"igroupsGroup\" onchange=\"checkInputForm()\">\n\t<option value=\"0\" $selected>Select Group</option>\n";

	while($row = mysql_fetch_array($query))
		echo "\t<option value=\"{$row['iID']}\">".htmlspecialchars($row['sIITID'])."</option>\n";

	echo "</select><br$st>\n";
	echo "<label>Use Survey: <select name=\"criteriaList\">\n";
	$criteria = getCriteriaLists($db);
	foreach($criteria as $criterialist)
		echo "\t<option value=\"{$criterialist->getID()}\">".htmlspecialchars($criterialist->getName())."</option>\n";
	echo "</select></label><br$st>\n";
	echo "<input type=\"submit\" name=\"importGroup\" value=\"Import Group\"$st>";
	echo "</fieldset></form>\n";

	//==================CREATE NEW GROUP=======================
	
	echo "<form action=\"groups.php$get\" method=\"post\"><fieldset><legend>Create a Group</legend>\n";
	echo "<label>Group Name: <input type=\"text\" name=\"groupName\"$st></label><br$st>\n";
	echo "<label>Use Survey: <select name=\"criteriaList\">\n";

	foreach($criteria as $criterialist)
		echo "\t<option value=\"{$criterialist->getID()}\">".htmlspecialchars($criterialist->getName())."</option>\n";
	echo "</select></label><br$st>\n";
	echo "<input type=\"submit\" name=\"createGroup\" value=\"Create Group\"$st>\n";
	echo "</fieldset></form>\n";
}

//=================EDIT A GROUP (Add/Remove Users)==================
echo "<form action=\"groups.php$get\" method=\"get\"><fieldset><legend>Edit a Group</legend>\n";
echo "<select name=\"group\">\n";

foreach($groups as $group)
{
	if(!isset($currentGroup))
		$currentGroup = new Group($group->getID(), $db);
	$nm = htmlspecialchars($group->getName());
	if($currentGroup && $currentGroup->getID() == $group->getID())
		echo "\t<option value=\"{$group->getID()}\" $selected>$nm</option>\n";
	else
		echo "\t<option value=\"{$group->getID()}\">$nm</option>\n";
}

if($currentUser->isAdministrator())
{
	$s = ($_GET['group'] == 0) ? " $selected" : '';
	echo "\t<option value=\"0\"$s>Admins</option>\n";
}
echo "</select>\n";
echo "<input type=\"submit\" name=\"selectGroup\" value=\"Select Group\"$st>\n";
echo "</fieldset></form><br$st>\n";

if(isset($_SESSION['group']) && $_GET['group'] != 0)
{
	$criterialist = new CriteriaList($currentGroup->getListID(), $db);
	$members = $currentGroup->getGroupStudents();
	$faculty = $currentGroup->getGroupFaculty();

	if(count($members) || count($faculty))
		echo "<form action=\"groups.php$get\" method=\"post\"><fieldset><legend></legend>\n";
	echo "<p><a href=\"adminsurvey.php?resetGroup={$currentGroup->getID()}\" onclick=\"confirmReset(); return false\">[Save &amp; Reset Data]</a>".($currentUser->isAdministrator() ? " <a href=\"groups.php?deleteGroup={$currentGroup->getID()}\" onclick=\"confirmation(); return false\">[Remove Group]</a>" : '')."</p>\n";
	echo "<table width=\"80%\">\n";
	echo "<tr><th colspan=\"2\">".htmlspecialchars($currentGroup->getName())."<br$st><small><i>(This group is set up to use the survey '".htmlspecialchars($criterialist->getName())."')</i></small></th></tr>\n";

	echo "<tr><td><h4>Faculty:</h4>\n";
	if(count($faculty))
	{
		echo "<ul class=\"nobullets\">\n";
		foreach($faculty as $user)
			echo "\t<li><label><input type=\"radio\" name=\"userID\" value=\"{$user->getID()}\"$st> ".htmlspecialchars($user->getFullName())." (".htmlspecialchars($user->getEmail()).")</label></li>\n";
		echo "</ul>\n";
	}
	else
		echo "This group has no faculty.\n";

	echo "</td><td><h4>Students:</h4>\n";
	if(count($members))
	{
		echo "<ul class=\"nobullets\">\n";
		foreach($members as $user)
			echo "\t<li><label><input type=\"radio\" name=\"userID\" value=\"{$user->getID()}\"$st> ".htmlspecialchars($user->getFullName())." (".htmlspecialchars($user->getEmail()).")</label></li>\n";
		echo "</ul>\n";
	}
	else
		echo 'This group has no students.';
	
	if(count($members) || count($faculty))
	{
		echo '</td></tr><tr><th colspan="2">';
		if(count($members))
			echo "<input type=\"submit\" name=\"makeFaculty\" value=\"Designate Faculty\"$st>\n";
		echo "<input type=\"submit\" name=\"removeUser\" value=\"Remove User from Group\"$st></th></tr></table>\n</fieldset></form>\n";
	}
	else
		echo '</td></tr></table>';

	//Create New User
	?>
<form action="groups.php<?php echo $get; ?>" method="post"><fieldset><legend>Add a New User to This Group</legend>
<table class="centered">
<tr><td><label for="firstName">First Name:</label></td>
<td><input type="text" name="firstName" id="firstName"<?php echo $st; ?>></td></tr>
<tr><td><label for="lastName">Last Name:</label></td>
<td><input type="text" name="lastName" id="lastName"<?php echo $st; ?>></td></tr>
<tr><td><label for="email">Email:</label></td>
<td><input type="text" name="email" id="email"<?php echo $st; ?>></td></tr>
<tr><td><label for="userType">User Type:</label></td>
<td>
<select name="userType" id="userType">
<option value="0">Student</option>
<option value="1">Faculty</option>
</select>
</td></tr>
<tr><td colspan="2"><input type="submit" name="createUser" value="Create User"<?php echo $st; ?>></td></tr></table>
<input type="hidden" name="group" value="<?php echo $_SESSION['group'];?>"<?php echo $st; ?>>
</fieldset></form>
	<?php
}
else if($currentUser->isAdministrator() && !$currentGroup)
{
     	echo "<h4>Administrators</h4>\n";

	$query = $db->query('SELECT * FROM People where userType=2');
	$members = array();
	while($row = mysql_fetch_array($query))
		$members[] = new Person($row['id'], $db);
	
	echo "<ul>\n";
	foreach($members as $user)
		echo "\t<li>{$user->getFullName()} ({$user->getEmail()})</li>\n";
	echo "</ul>\n";

	echo "<form method=\"post\" action=\"groups.php$get\"><fieldset><legend>Create New Administrator</legend>\n";
	echo "<table width=\"50%\"><tr><td><strong><label>First Name: <input type=\"text\" name=\"firstName\"$st></label><br$st>\n";
	echo "<label>Last Name: <input type=\"text\" name=\"lastName\"$st></label><br$st>\n";
	echo "<label>Email: <input type=\"text\" name=\"email\"$st></label></strong><br$st>\n";
	echo "<input type=\"submit\" name=\"createAdmin\" value=\"Create Admin\"$st>\n";
	echo "</td></tr></table></fieldset></form>\n";
}
?>
</div></body></html>
