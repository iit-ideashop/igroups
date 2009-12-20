<?php
	include_once('globals.php');
	include_once('checklogin.php');

	if(!$currentUser->isGroupModerator($currentGroup))
		errorPage('Group Credentials Required', 'You must be a group moderator to access this page', 403);

	function alpha($person1, $person2)
	{
		if($person1->getLastName() < $person2->getLastName())
			return -1;
		else if ($person2->getLastName() < $person1->getLastName())
			return 1;
		else
			return 0;
	}

	//------Start of Code for Form Processing-------------------------//

	if(isset($_POST['updategroup']))
	{
		$subgroups = $currentGroup->getSubGroups();
		foreach($subgroups as $subgroup)
		{
			$subgroup->clearMembers();
			if(isset($_POST["sub_{$subgroup->getID()}"]))
			{
				foreach($_POST["sub_{$subgroup->getID()}"] as $key => $val)
				{
					$person = new Person($val, $db);
					$subgroup->addMember($person);
				}
			}
		}

		if(isset($_POST['access']))
		{
			foreach($_POST['access'] as $key => $val)
			{
				$person = new Person($key, $db);
				$person->setGroupAccessLevel($val, $currentGroup);
			}
		}

		if(isset($_POST['delete']))
		{
			foreach($_POST['delete'] as $key => $val)
			{
				$person = new Person($key, $db);
				$person->removeFromGroup($currentGroup);
			}
		}
		$message = 'Group successfully updated';

	}
	//------End Form Processing Code--------------------------------//
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Group Management</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
</head><body>
<?php
	$go = true;
	if(isset($_POST['newuser']))
	{
		$email = $_POST['email'];
		$email = str_replace(' ', '', $email);
		$email = str_replace('>', '', $email);
		$email = str_replace('<', '', $email);
		$email = str_replace('"', '', $email);
		$email = str_replace("'", '', $email);
		$user = createPerson( $email, $_POST['fname'], $_POST['lname'], $db );
		$user->addToGroup( $currentGroup );
		$message = 'User was successfully created';
	}
	if(isset($_POST['retroadd']))
	{
		$addUsers = $_POST['addUsers'];
		$addGroups = $_POST['addGroups'];

		if($addUsers && $addGroups)
		{
			foreach($addUsers as $addUser)
			{
				$user = new Person($addUser, $db);
				foreach($addGroups as $addGroup)
				{
					$group = substr($addGroup, 0, strpos($addGroup, '-'));
					$sem = substr($addGroup, strpos($addGroup, '-')+1);
					$group = new Group($group, 0, $sem, $db);
					if(!$user->isGroupMember($group))
					{
						$user->addToGroupNoEmail($group);
						$user->setGroupAccessLevel(-1, $group);
					}
				}
			}
			$message = 'User(s) successfully added';
		}
		else
			$message = 'ERROR: Select at least one user and group';
	}
	if(isset($_POST['adduser']))
	{
		$email = $_POST['email'];
		$email = str_replace(' ', '', $email);
		$email = str_replace('>', '', $email);
		$email = str_replace('<', '', $email);
		$email = str_replace('"', '', $email);
		$email = str_replace("'", '', $email);
		$user = $db->query("SELECT iID FROM igroups.People WHERE sEmail='".$email."'");
		if($row = mysql_fetch_row($user))
		{
			$user = new Person($row[0], $db);
			$user->addToGroup($currentGroup);
			$message = 'User was successfully added';
		}
		else
		{
			$go = false;
			
      /****begin html head ****/
      require('htmlhead.php'); //starts main container
      /****end html head content ***/
?>
			<div id="newuser">
			No one with e-mail address <span style="font-weight: bold"><?php echo $email; ?></span> currently exists in our system.<br />
			Please enter additional data so that they may be added.<br />
			<form method="post" action="groupmanagement.php"><fieldset>
			<label for="fname">First Name:</label><input type="text" name="fname" id="fname" /><br />
			<label for="lname">Last Name:</label><input type="text" name="lname" id="lname" /><br />
<?php
			echo "<input type=\"hidden\" name=\"email\" value=\"".$_POST['email']."\" />";
?>
			<input type="submit" name="newuser" value="Create New User" />
			</fieldset></form></div>
<?php
		}
	}

	if(isset($_POST['createSubGroup']))
	{
		if($_POST['subGroupName'] != '')
		{
			$db->query("INSERT INTO SubGroups (iGroupID, sName) VALUES ({$currentGroup->getID()}, '{$_POST['subGroupName']}')");
			$message = 'Subgroup successfully created';
		}
	}

	if(isset($_POST['deleteSubGroup']))
	{
		if(isset($_POST['delete']))
		{
			$subgroup = new SubGroup($_POST['delete'], $db);
			$subgroup->delete();
			$message = 'Subgroup successfully deleted';
		}
	}
	if($go)
	{
		require('sidebar.php');
		echo "<div id=\"content\"><div id=\"topbanner\">{$currentGroup->getName()}</div>\n";
 	}
?>
<form method="post" action="groupmanagement.php"><fieldset><legend>Current Users</legend>
<table><thead>
<tr><th rowspan="2">User</th><th rowspan="2">Delete?</th><th rowspan="2">Hours Summary</th>
<?php
	$admin = $currentUser->isGroupAdministrator($currentGroup);
	$subgroups = $currentGroup->getSubGroups();
	$numSubgroups = count($subgroups);

	//if($admin)
	//	echo "<td colspan=\"4\">Access Level</td>";
	if($currentUser->isGroupModerator($currentGroup) && count($subgroups) > 0)
		echo "<th colspan=\"4\">Access Level</th><th colspan=\"$numSubgroups\">Subgroup Membership</th>";
	echo "</tr>\n<tr>";

	if($admin || $currentUser->isGroupModerator($currentGroup))
		echo "<th>Guest</th><th>User</th><th>Moderator</th><th>Administrator</th>";

	if($currentUser->isGroupModerator($currentGroup))
	{
		foreach($subgroups as $subgroup)
			echo "<th>{$subgroup->getName()}</th>";
	}
	echo "</tr></thead><tbody>\n";
			
	$members = $currentGroup->getAllGroupMembers();
	//$members = peopleSort( $members );
	usort($members, 'alpha');
	foreach($members as $person)
	{
		printTR();
		echo "<td>".$person->getCommaName()." &lt;".$person->getEmail()."&gt;</td>";
		echo "<td align=\"center\"><input type=\"checkbox\" name=\"delete[".$person->getID()."]\" /></td>";
		echo "<td align=\"center\"><a href=\"hourssummary.php?uid={$person->getID()}\">Summary</a></td>";
		if($person->isGroupAdministrator($currentGroup))
		{
			if($admin || $currentUser->isGroupModerator($currentGroup))
				echo "<td></td><td></td><td></td><td align=\"center\">*</td>";
			foreach ($subgroups as $subgroup)
			{
				if($subgroup->isSubGroupMember($person))
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" checked=\"checked\" /></td>";
				else
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" /></td>";
			}
		}
		else if($person->isGroupModerator($currentGroup))
		{
			if($currentUser->isGroupModerator($currentGroup) && !$admin)
				echo "<td></td><td></td><td align=\"center\">*</td><td></td>";
			if($admin)
			{
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" /></td>";
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" /></td>";
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" checked=\"checked\" /></td><td></td>";
			}
			foreach($subgroups as $subgroup)
			{
				if($subgroup->isSubGroupMember($person))
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" checked=\"checked\" /></td>";
				else
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" /></td>";
			}
		}
		else if(!$person->isGroupGuest($currentGroup))
		{
			if($admin || $currentUser->isGroupModerator($currentGroup))
			{
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" /></td>";
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" checked=\"checked\" /></td>";
				if($admin)
					echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" /></td><td></td>";
				else
					echo "<td></td><td></td>";
			}
			foreach($subgroups as $subgroup)
			{
				if($subgroup->isSubGroupMember($person))
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" checked=\"checked\" /></td>";
				else
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" /></td>";
			}
		}
		else
		{
			if($admin || $currentUser->isGroupModerator($currentGroup))
			{
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"-1\" checked=\"checked\" /></td>";
				echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"0\" /></td>";
				if($admin)
					echo "<td align=\"center\"><input type=\"radio\" name=\"access[".$person->getID()."]\" value=\"1\" /></td><td></td>";
				else
					echo "<td></td><td></td>";
			}
			foreach($subgroups as $subgroup)
			{
				if($subgroup->isSubGroupMember($person))
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" checked=\"checked\" /></td>";
				else
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"sub_{$subgroup->getID()}[]\" value=\"{$person->getID()}\" /></td>";
			}
		}
		
		echo "</tr>\n";
	}
?>
	</tbody></table>
	<input type="submit" value="Update" name="updategroup" />
	</fieldset></form>
	<br />
	<div id="adduser">
	<form method="post" action="groupmanagement.php"><fieldset>
	<legend>Add User to Group</legend>
	<label for="email">Email address:</label><input type="text" name="email" id="email" /><br />
	<input type="submit" value="Add User" name="adduser" />
	</fieldset></form>
	</div>
	<br />
	<div id="subgroups">
	<form method="post" action="groupmanagement.php"><fieldset><legend>Manage Subgroups</legend>
	<label for="subGroupName">Create New Subgroup:</label><input type="text" name="subGroupName" id="subGroupName" />&nbsp;<input type="submit" name="createSubGroup" value="Create Subgroup" /><br />
<?php
	$subgroups = $currentGroup->getSubGroups();
	$i = 1;
	foreach($subgroups as $subgroup)
	{
		echo "<input type=\"radio\" name=\"delete\" id=\"delete$i\" value=\"{$subgroup->getID()}\" />&nbsp;<label for=\"delete$i\">".htmlspecialchars($subgroup->getName())."</label><br />";
		$i++;
	}
	if(count($subgroups) > 0)
		echo "<input type=\"submit\" name=\"deleteSubGroup\" value=\"Delete Subgroup\" />";
?>
	<br /><br /></fieldset></form>
	</div>
<?php
	$pastGroups = $currentGroup->findPastIPROs();
	if($pastGroups)
	{
?>
		<div id="retroadd">
		<form method="post" action="groupmanagement.php"><fieldset>
		<legend>Make Users Guests in Past Groups</legend>
		<table width="100%">
		<tr><th>Choose Users</th><th>Choose Groups</th></tr>
		<tr><td valign="top">
<?php
		$i = 1;
		foreach($currentGroup->getAllGroupMembers() as $member)
		{
			echo "<input type=\"checkbox\" name=\"addUsers[]\" id=\"addUsers$i\" value=\"{$member->getID()}\" />&nbsp;<label for=\"addUsers$i\">{$member->getFullName()}</label><br />";
			$i++;
		}
?>
		</td><td valign="top">
<?php
		$i = 1;
		foreach($pastGroups as $group)
		{
			$query = $db->query("SELECT sSemester FROM Semesters where iID={$group->getSemester()}");
			$row = mysql_fetch_row($query);
			echo "<input type=\"checkbox\" name=\"addGroups[]\" id=\"addGroups$i\" value=\"{$group->getID()}-{$group->getSemester()}\" />&nbsp;<label for=\"addGroups$i\">{$row[0]} -&gt; ".$group->getName().": ".htmlspecialchars($group->getDesc())."</label><br />";
			$i++;
		}
?>
		<br /><input type="submit" name="retroadd" value="Make Guests" />
		</td></tr>
		</table>
		</fieldset></form>
	</div>
<?php
	}
?>

<?php
//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>
</body>
</html>
