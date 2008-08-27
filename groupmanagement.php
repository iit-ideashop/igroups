<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );

	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
	
	if ( !$currentUser->isGroupModerator( $currentGroup ) )
		die("You must be a group moderator to access this page.");
	
	/*	
	function peopleSort( $array ) {
		$newArray = array();
		foreach ( $array as $person ) {
			$newArray[$person->getCommaName()] = $person;
		}
		ksort( $newArray );
		return $newArray;
	}*/
	
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
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}
	
	//------Start of Code for Form Processing-------------------------//
	
	if ( isset( $_POST['updategroup'] ) ) {
		$subgroups = $currentGroup->getSubGroups();
		foreach ($subgroups as $subgroup) {
			$subgroup->clearMembers();
			if (isset($_POST["sub_{$subgroup->getID()}"])) {
				foreach($_POST["sub_{$subgroup->getID()}"] as $key => $val) {
					$person = new Person($val, $db);
					$subgroup->addMember($person);
				}
			}
		}

		if ( isset( $_POST['access'] ) )
		foreach ( $_POST['access'] as $key => $val ) {
			$person = new Person( $key, $db );
			$person->setGroupAccessLevel( $val, $currentGroup );
		}

		if ( isset( $_POST['delete'] ) )
		foreach ( $_POST['delete'] as $key => $val ) {
			$person = new Person( $key, $db );
			$person->removeFromGroup( $currentGroup );
		}
		$message = "Group successfully updated";
	
	}
	
	//------End Form Processing Code---------------------------------//
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Management</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<script type="text/javascript">
	<!--
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
	//-->
	</script>
</head>
<body>
<?php
require("sidebar.php");
	if ( isset( $message ) )
		print "<script type='text/javascript'>showMessage(\"$message\");</script>";
?>	
	<div id="content"><div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
<?php
	if ( isset( $_POST['newuser'] ) ) {
		$email = $_POST['email'];
		$email = str_replace(' ', '', $email);
		$email = str_replace('>', '', $email);
		$email = str_replace('<', '', $email);
		$email = str_replace('"', '', $email);
		$email = str_replace("'", '', $email);
		$user = createPerson( $email, $_POST['fname'], $_POST['lname'], $db );
		$user->addToGroup( $currentGroup );
?>
		<script type="text/javascript">
			showMessage("User was successfully created");
		</script>
<?php
	}
	if (isset($_POST['retroadd'])) {
		$addUsers = $_POST['addUsers'];
		$addGroups = $_POST['addGroups'];

		if ($addUsers && $addGroups) {
			foreach($addUsers as $addUser) {
				$user = new Person($addUser, $db);
				foreach($addGroups as $addGroup) {
					$group = substr($addGroup, 0, strpos($addGroup, '-'));
					$sem = substr($addGroup, strpos($addGroup, '-')+1);
					$group = new Group($group, 0, $sem, $db);
					if (!$user->isGroupMember($group)) {
						$user->addToGroupNoEmail($group);
						$user->setGroupAccessLevel(-1, $group);
					}
				}
			}
?>
			<script type="text/javascript">
                        	showMessage("User(s) successfully added");
                	</script>
<?php
		}
		else {
?>
			<script type="text/javascript">
                        	showMessage("ERROR: Select at least one user and group");
                	</script>
<?php
		}
	}
	if ( isset( $_POST['adduser'] ) ) {
		$email = $_POST['email'];
                $email = str_replace(' ', '', $email);
                $email = str_replace('>', '', $email);
                $email = str_replace('<', '', $email);
                $email = str_replace('"', '', $email);
                $email = str_replace("'", '', $email);
		$user = $db->iknowQuery( "SELECT iID FROM igroups.People WHERE sEmail='".$email."'" );
		if ( $row = mysql_fetch_row( $user ) ) {
			$user = new Person( $row[0], $db );
			$user->addToGroup( $currentGroup );
?>
			<script type="text/javascript">
				showMessage("User was successfully added");
			</script>
<?php
		}
		else {
?>
			<div id="newuser">
				No one with e-mail address <b><?php print "$email"; ?></b> currently exists in our system.<br />
				Please enter additional data so that they may be added.<br />
				<form method="post" action="groupmanagement.php">
					First Name: <input type='text' name='fname' /><br />
					Last Name: <input type='text' name='lname' /><br />
<?php
					print "<input type='hidden' name='email' value='".$_POST['email']."' />";
?>
					<input type='submit' name='newuser' value="Create New User" />
				</form>
			</div>
<?php
		}	
	}

	if (isset($_POST['createSubGroup'])) {
		if ($_POST['subGroupName'] != '') {
			$db->igroupsQuery("INSERT INTO SubGroups (iGroupID, sName) VALUES ({$currentGroup->getID()}, '{$_POST['subGroupName']}')");
?>
		<script type="text/javascript">
                        showMessage("Subgroup successfully created");
                </script>
<?php
		}
	}

	if (isset($_POST['deleteSubGroup'])) {
		if (isset($_POST['delete'])) {
			$subgroup = new SubGroup($_POST['delete'], $db);
			$subgroup->delete();
?>
		<script type="text/javascript">
                        showMessage("Subgroup successfully deleted");
                </script>
<?php
		}
	}

?>
	<form method="post" action="groupmanagement.php">
		<h1>Current Users</h1>
		<table>
			<thead>
				<tr><td rowspan="2">User</td><td rowspan="2">Delete?</td>
<?php
			$admin = $currentUser->isGroupAdministrator( $currentGroup );
			$subgroups = $currentGroup->getSubGroups();
                        $numSubgroups = count($subgroups);

			//if ( $admin )
				//print "<td colspan='4'>Access Level</td>";
			if ( $currentUser->isGroupModerator($currentGroup) && count($subgroups) > 0)
                                print "<td colspan='4'>Access Level</td><td colspan='$numSubgroups'>Subgroup Membership</td>";
			print "</tr><tr>";

			if ($admin || $currentUser->isGroupModerator($currentGroup))
				print "<td>Guest</td><td>User</td><td>Moderator</td><td>Administrator</td>";

			if ( $currentUser->isGroupModerator($currentGroup) ) {
				foreach($subgroups as $subgroup)
					print "<td>{$subgroup->getName()}</td>";
			}
			print "</tr></thead>";
			
			$members = $currentGroup->getAllGroupMembers();
			//$members = peopleSort( $members );
			usort($members, "alpha");
			foreach ( $members as $person ) {
				printTR();
				print "<td>".$person->getCommaName()." &lt;".$person->getEmail()."&gt;</td>";
				print "<td align='center'><input type='checkbox' name='delete[".$person->getID()."]' /></td>";
					if ( $person->isGroupAdministrator( $currentGroup ) ) {
						if ($admin || $currentUser->isGroupModerator($currentGroup))
						print "<td></td><td></td><td></td><td align='center'>*</td>";
						foreach ($subgroups as $subgroup) {
                                                        if ($subgroup->isSubGroupMember($person))
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' checked='checked' /></td>";
                                                        else
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' /></td>";
                                                }
					}
					else if ( $person->isGroupModerator( $currentGroup ) ) {
						if ($currentUser->isGroupModerator($currentGroup) && !$admin)
							print "<td></td><td></td><td align='center'>*</td><td></td>";
						if ($admin) {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1' /></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='0' /></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='1' checked='checked' /></td><td></td>";
						}
						foreach ($subgroups as $subgroup) {
                                                        if ($subgroup->isSubGroupMember($person))
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' checked='checked' /></td>";
                                                        else
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' /></td>";
                                                }
					}
					else if (!$person->isGroupGuest($currentGroup)) {
						if ($admin || $currentUser->isGroupModerator($currentGroup)) {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1' /></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='0' checked='checked' /></td>";
						if ($admin)
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='1' /></td><td></td>";
						else
						print "<td></td><td></td>";
						}
						foreach ($subgroups as $subgroup) {
                                                        if ($subgroup->isSubGroupMember($person))
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' checked='checked' /></td>";
                                                        else
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' /></td>";
                                                }
					}
					else {
						if ($admin || $currentUser->isGroupModerator($currentGroup)) {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1' checked='checked' /></td>";
                                                print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='0' /></td>";
						if ($admin)
                                                print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='1' /></td><td></td>";
						else
						print "<td></td><td></td>";
						}
						foreach ($subgroups as $subgroup) {
                                                        if ($subgroup->isSubGroupMember($person))
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' checked='checked' /></td>";
                                                        else
                                                                print "<td align='center'><input type='checkbox' name='sub_{$subgroup->getID()}[]' value='{$person->getID()}' /></td>";
                                                }
					}
				
				print "</tr>";
			}
?>
		</table>
		<input type="submit" value="Update" name="updategroup" />
	</form>
	<br />
	<div id="adduser">
		<form method="post" action="groupmanagement.php">
			<h1>Add User to Group</h1>
			Email address:<input type="text" name="email" /><br />
			<input type="submit" value="Add User" name="adduser" />
		</form>
	</div>
	<br />
	<div id="subgroups">
		<h1>Manage Subgroups</h1>
		<form method='post' action='groupmanagement.php'>
		Create New Subgroup: <input type='text' name='subGroupName' />&nbsp;<input type='submit' name='createSubGroup' value='Create Subgroup' /><br />
<?php
	$subgroups = $currentGroup->getSubGroups();
	foreach ($subgroups as $subgroup) {
		print "<input type='radio' name='delete' value='{$subgroup->getID()}' />&nbsp;".str_replace("&", "&amp;", $subgroup->getName())."<br />";
	}
	if (count($subgroups) > 0)
		print "<input type='submit' name='deleteSubGroup' value='Delete Subgroup' />";
?>
	<br /><br /></form>
	</div>
<?php
$pastGroups = $currentGroup->findPastIPROs();
if ($pastGroups) {
?>
	<div id="retroadd">
		<form method="post" action='groupmanagement.php'>
			<h1>Make Users Guests in Past Groups</h1>
			<table width='100%'>
			<tr><td><b>Choose Users</b></td><td><b>Choose Groups</b></td></tr>
			<tr><td valign='top'>
<?php
	foreach($currentGroup->getAllGroupMembers() as $member) {
		print "<input type='checkbox' name='addUsers[]' value='{$member->getID()}' />&nbsp;{$member->getFullName()}<br />";
	}
?>
			</td><td valign='top'>
<?php
	foreach($pastGroups as $group) {
		$query = $db->iknowQuery("SELECT sSemester FROM Semesters where iID={$group->getSemester()}");
		$row = mysql_fetch_row($query);
		print "<input type='checkbox' name='addGroups[]' value='{$group->getID()}-{$group->getSemester()}' />&nbsp;{$row[0]} -> {$group->getName()}: ".str_replace("&", "&amp;", $group->getDesc())."<br />";
	}
?>
			<br /><input type='submit' name='retroadd' value='Make Guests' />
			</td></tr>
			</table>
		</form>
	</div>
<?php
}
?>
</div>
</body>
</html>
