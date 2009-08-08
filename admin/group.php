<?php
	include_once("../globals.php");
	include_once( "checkadmin.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/quota.php" );
	include_once( "../classes/semester.php" );

	function groupSort( $array ) {
		$newArray = array();
		foreach ( $array as $group ) {
			if ( $group )
				$newArray[$group->getName()] = $group;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function alpha($person1, $person2) {
		if ($person1->getLastName() < $person2->getLastName())
			return -1;
		else if ($person2->getLastName() < $person1->getLastName())
			return 1;
		else
			return 0;
	}

	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class=\"shade\">";
		else
			print "<tr>";
		$i=!$i;
	}
	
	if(is_numeric($_GET['del']) && is_numeric($_GET['type']) && is_numeric($_GET['sem']))
	{
		$group = new Group($_GET['del'], $_GET['type'], $_GET['sem'], $db);
		$files = $group->getGroupFiles();
		$emails = $group->getGroupEmails();
		$members = $currentGroup->getAllGroupMembers();
		if(!count($files) && !count($emails) && !count($members))
		{
			$group->delete();
			$message = 'Group deleted';
		}
		else
			$message = 'Could not delete group.'
	}

	if ( isset( $_GET['selectSemester'] ) ) {
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
		unset( $_SESSION['selectedIPROGroup'] );
	}
	
	if ( isset( $_GET['selectAdminGroup'] ) && $_GET['selectAdminGroup'] != '') {
		$_SESSION['selectedIPROGroup'] = $_GET['group'];
	}

	if ( isset($_POST['createIPRO'])) {
		if (isset($_POST['sIITID'])) {
			$db->iknowQuery("INSERT INTO Projects VALUES (NULL, '{$_POST['sName']}', NULL, '{$_POST['sIITID']}')");
			$id = $db->iknowInsertID();
			$db->iknowQuery("INSERT INTO ProjectSemesterMap VALUES ($id, {$_POST['semester']}, NULL)");
			$message = 'Group Created';
		}
		else
			$message = 'Could not create group: no ID given';
	}	

	if ( isset( $_POST['createGroup'] ) ) {
		$db->igroupsQuery("INSERT INTO Groups (sName) VALUES (\"{$_POST['newGroup']}\")");
		$message = 'Group successfully created.';
	}
	
	if ( !isset( $_SESSION['selectedIPROSemester'] ) ) {
		$semester = $db->iknowQuery( "SELECT iID FROM Semesters WHERE bActiveFlag=1" );
		$row = mysql_fetch_row( $semester );
		$_SESSION['selectedIPROSemester'] = $row[0];
	}
	
	if ($_SESSION['selectedIPROSemester'] != 0)
		$currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db );
	else
		$currentSemester = 0;

	if ( isset( $_SESSION['selectedIPROGroup'] ) && $_SESSION['selectedIPROGroup'] != '' ) {
		if ( $currentSemester )
			$currentGroup = new Group( $_SESSION['selectedIPROGroup'], 0, $currentSemester->getID(), $db );
		else
			$currentGroup = new Group( $_SESSION['selectedIPROGroup'], 1, 0, $db );
	}
	else
		$currentGroup = false;
		
	if ( isset( $_POST['updategroup'] ) ) {
		foreach ( $_POST['access'] as $key => $val ) {
			$person = new Person( $key, $db );
			$person->setGroupAccessLevel( $val, $currentGroup );
		}
		
		if ( isset( $_POST['delete'] ) )
		foreach ( $_POST['delete'] as $key => $val ) {
			$person = new Person( $key, $db );
			$person->removeFromGroup( $currentGroup );
		}
		$message = "Group successfully updated.";
	}

?>		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php
require("../iknow/appearance.php");
echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - IPRO Group Management</title>
	<style type="text/css">
		#groupSelect {
			margin-bottom:10px;
		}
	</style>
</head>
<body>
<?php
	require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		if ( $currentSemester )
			print $currentSemester->getName();
		else
			print "All groups";
		
		if ( $currentGroup )
			print " - ".$currentGroup->getName();
?>
	</div>
	<table width="85%">
	<tr>
	<td style="width:40%">
	<div id="semesterSelect">
	<h1>Create New IPRO</h1>
		<form method="post" action="group.php"><fieldset>
		<b>Semester</b><br />
		<select name="semester">
<?php
		$semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
			while ( $row = mysql_fetch_row( $semesters ) ) {
				$semester = new Semester( $row[0], $db );
				print "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";

			}
?>
		</select><br /><br />
		<?php
		$last = mysql_fetch_row($db->igroupsQuery('select sIITID from Projects group by iID desc limit 1'));
		echo "<b>ID: </b>(last: {$last[0]})<br />";
		?>
		<input type="text" name="sIITID" size="10" /><br /><br />
		<b>Name: </b>(e.g. Developing New Products)<br />
		<input type="text" name="sName" size="25" />&nbsp;&nbsp;
		<input type="submit" name="createIPRO" value="Create IPRO" /><br />
		</fieldset></form>
		<br />
		<h1>Manage Groups</h1>
		<form method="get" action="group.php"><fieldset>
			<select name="semester">
<?php
			$semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
			while ( $row = mysql_fetch_row( $semesters ) ) {
				$semester = new Semester( $row[0], $db );
				if (isset($_SESSION['selectedIPROSemester']) && ($_SESSION['selectedIPROSemester'] == $semester->getID()))
					print "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
				else
					print "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";

			}
			if (!$currentSemester)
				print "<option value=\"0\" selected=\"selected\">All iGROUPS</option>";
			else 
				print "<option value=\"0\">All groups</option>";

?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
		</fieldset></form>
	</div>

<?php
	if ( $currentSemester ) {
		$groups = $currentSemester->getGroups();
	}
	else {
		$groupResults = $db->igroupsQuery( "SELECT iID FROM Groups" );
		$groups = array();
		while ( $row = mysql_fetch_row( $groupResults ) ) {
			$groups[] = new Group( $row[0], 1, 0, $db );
		}
	}
?>
	<div id="groupSelect" style="margin-bottom:10px;">
		<form method="get" action="group.php"><fieldset>
			<select name="group">
			<option value=''>Select a Group</option>
<?php
			$groups = groupSort( $groups );
			foreach ( $groups as $group ) {
				if ($currentGroup && $group->getID() == $currentGroup->getID())
					print "<option value=\"".$group->getID()."\" selected=\"selected\">".$group->getName()."</option>";
				else
					print "<option value=\"".$group->getID()."\">".$group->getName()."</option>";
			}
?>
			</select>
			<input type="submit" name="selectAdminGroup" value="Select Group" />
		</fieldset></form>
	</div>
	</td>
	<td style="width:60%">
	<div id="createCroup"> 
		<h1>Create non-IPRO Group</h1>
		<form method="post" action="group.php"><fieldset>
			<label for="newGroup">Name:</label><input type="text" name="newGroup" id="newGroup" />
			<input type="submit" name="createGroup" value="Create Group" />
		</fieldset></form>
	</div>
	</td>
	</tr>
	</table>
<?php
	if ( $currentGroup ) {	
		if ( isset( $_POST['newuser'] ) ) {
			$email = $_POST['email'];
			$email = str_replace(' ', '', $email);
			$email = str_replace('>', '', $email);
			$email = str_replace('<', '', $email);
			$email = str_replace('"', '', $email);
			$email = str_replace("'", '', $email);
			$user = createPerson( $email, $_POST['fname'], $_POST['lname'], $db );
			$user->addToGroup( $currentGroup );
			$user->updateDB();
?>
			<script type="text/javascript">
			<!--
				showMessage("User was successfully created");
			//-->
			</script>
<?php
		}
		
		if ( isset( $_POST['adduser'] ) ) {
			$email = $_POST['email'];
			$email = str_replace(' ', '', $email);
			$email = str_replace('>', '', $email);
			$email = str_replace('<', '', $email);
			$email = str_replace('"', '', $email);
			$email = str_replace("'", '', $email);
			$user = $db->iknowQuery( "SELECT iID FROM People WHERE sEmail='".$email."'" );
			if ( $row = mysql_fetch_row( $user ) ) {
				$user = new Person( $row[0], $db );
				$user->addToGroup( $currentGroup );
?>
				<script type="text/javascript">
				<!--
					showMessage("User successfully added");
				//-->
				</script>
<?php
			}
			else {
?>
				<div id="newuser">
					No one with e-mail address <b><?php print "$email"; ?></b> currently exists in our system.<br />
					Please enter additional data so that they may be added.<br />
					<form method="post" action="group.php"><fieldset>
						<label for="fname">First Name:</label><input type="text" name="fname" /><br />
						<label for="lname">Last Name:</label><input type="text" name="lname" /><br />
						<input type="hidden" name="email" value="<?php print($_POST['email']); ?>" />
						<input type="submit" name="newuser" value="Create New User" />
					</fieldset></form>
				</div>
<?php
			}	
		}

		$members = $currentGroup->getAllGroupMembers();
		if(count($members) > 0) {
?>
			<form method="post" action="group.php"><fieldset>
				<table>
					<thead>
						<tr>
							<td rowspan="2">User</td>
							<td rowspan="2">Phone</td>
							<td rowspan="2">Delete?</td>
							<td colspan="4">Access Level</td>
						</tr>
						<tr>
							<td>Guest</td>
							<td>User</td>
							<td>Moderator</td>
							<td>Administrator</td>
						</tr></thead>
<?php
				usort($members, "alpha");
				foreach ( $members as $person ) {
					
					printTR();
					print "<td>".$person->getCommaName()." &lt;".$person->getEmail()."&gt;</td>";
					print "<td>{$person->getPhone()}</td>";
					print "<td align=\"center\"><input type=\"checkbox\" name=\"delete[".$person->getID()."]\" /></td>";
					if ( $person->isGroupAdministrator( $currentGroup ) ) {
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"2\" checked=\"checked\" /></td>";
					}
					else
					if ( $person->isGroupModerator( $currentGroup ) ) {
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" checked=\"checked\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"2\" /></td>";
					}
					else if (!$person->isGroupGuest($currentGroup)) {
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" checked=\"checked\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"2\" /></td>";
					}
					else {
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" checked=\"checked\"></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" /></td>";
						print "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"2\" /></td>";					
					}
					print "</tr>";
				}
?>
			</table>
			<input type="submit" value="Update" name="updategroup" />&nbsp;
		</fieldset></form>
<?php
		}
		else
		{
			$files = $currentGroup->getGroupFiles();
			$emails = $currentGroup->getGroupEmails();
			if(!count($files) && !count($emails))
				echo "<p><b>{$currentGroup->getName()}</b> has no data associated with it. You may <a href=\"group.php?del={$currentGroup->getID()}&amp;type={$currentGroup->getType()}&amp;sem={$currentGroup->getSemester()}\">delete this group</a>.</p>\n";
		}
?>
		<div id="adduser">
			<form method="post" action="group.php"><fieldset><legend>Add User</legend>
				<label for="addEmail">Email address:</label><input type="text" name="email" id="addEmail" /><br />
				<input type="submit" value="Add User" name="adduser" />
			</fieldset></form>
		</div>
<?php
	}
?>
</div></body>
</html>
