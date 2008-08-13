<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/quota.php" );
	include_once( "../classes/semester.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( !$currentUser->isAdministrator() )
		die("You must be an administrator to access this page.");

	function groupSort( $array ) {
		$newArray = array();
		foreach ( $array as $group ) {
			if ( $group )
				$newArray[$group->getName()] = $group;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function peopleSort( $array ) {
		$newArray = array();
		foreach ( $array as $person ) {
			$newArray[$person->getCommaName()] = $person;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}

	if ( isset( $_GET['selectSemester'] ) ) {
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
		unset( $_SESSION['selectedIPROGroup'] );
	}
	
	if ( isset( $_GET['selectGroup'] ) && $_GET['selectGroup'] != '') {
		$_SESSION['selectedIPROGroup'] = $_GET['group'];
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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - IPRO Group Management</title>
	<style type="text/css">
		@import url("../default.css");
		
		#groupSelect {
			margin-bottom:10px;
		}
	</style>
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
	if ( isset( $message ) )
		print "<script type='text/javascript'>showMessage(\"$message\");</script>";
?>
	<div id="topbanner">
<?php
		if ( $currentSemester )
			print $currentSemester->getName();
		else
			print "All iGROUPS";
		
		if ( $currentGroup )
			print " - ".$currentGroup->getName();
?>
	</div>
	<table width="100%">
	<tr>
	<td width="40%" valign="top">
	<div id="semesterSelect">
		<h1>Manage Groups</h1>
		<form method="get" action="group.php" valign="top">
			<select name="semester">
<?php
			$semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
			while ( $row = mysql_fetch_row( $semesters ) ) {
				$semester = new Semester( $row[0], $db );
				if (isset($_SESSION['selectedIPROSemester']) && ($_SESSION['selectedIPROSemester'] == $semester->getID()))
					print "<option value=".$semester->getID()." selected>".$semester->getName()."</option>";
				else
					print "<option value=".$semester->getID().">".$semester->getName()."</option>";

			}
			if (!$currentSemester)
				print "<option value=0 selected>All iGROUPS</option>";
			else 
				print "<option value=0>All iGROUPS</option>";

?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester">
		</form>
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
		<form method="get" action="group.php">
			<select name="group">
			<option value=''>Select a Group</option>
<?php
			$groups = groupSort( $groups );
			foreach ( $groups as $group ) {
				if ($currentGroup && $group->getID() == $currentGroup->getID())
					print "<option value=".$group->getID()." selected>".$group->getName()."</option>";
				else
					print "<option value=".$group->getID().">".$group->getName()."</option>";
			}
?>
			</select>
			<input type="submit" name="selectGroup" value="Select Group">
		</form>
	</div>
	</td>
	<td width="60%" valign="top">
	<div id="createCroup" valign="top"> 
		<h1>Create non-IPRO Group</h1>
		<form method="post" action="group.php">
			Name: <input type="text" name="newGroup">
			<input type="submit" name="createGroup" value="Create Group">
		</form>
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
				showMessage("User was successfully created");
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
					showMessage("User successfully added");
				</script>
<?php
			}
			else {
?>
				<div id="newuser">
					No one with e-mail address <b><?php print "$email"; ?></b> currently exists in our system.<br>
					Please enter additional data so that they may be added.<br>
					<form method="post" action="group.php">
						First Name: <input type='text' name='fname'><br>
						Last Name: <input type='text' name='lname'><br>
						<input type='hidden' name='email' value='<?php print "{$_POST['email']}"; ?>'>
						<input type='submit' name='newuser' value="Create New User">
					</form>
				</div>
<?php
			}	
		}
?>
		<form method="post" action="group.php">
			<table>
				<thead>
					<tr>
						<td rowspan=2>User</td>
						<td rowspan=2>Phone</td>
						<td rowspan=2>Delete?</td>
						<td colspan=4>Access Level</td>
					</tr>
					<tr>
						<td>Guest</td>
						<td>User</td>
						<td>Moderator</td>
						<td>Administrator</td>
					</tr></thead>
<?php
	
				$members = $currentGroup->getAllGroupMembers();
				$members = peopleSort( $members );
				foreach ( $members as $person ) {
					
					printTR();
					print "<td>".$person->getCommaName()." &lt;".$person->getEmail()."&gt;</td>";
					print "<td>{$person->getPhone()}</td>";
					print "<td align='center'><input type='checkbox' name='delete[".$person->getID()."]'></td>";
					if ( $person->isGroupAdministrator( $currentGroup ) ) {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1'></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=0></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=1></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=2 checked></td>";
					}
					else
					if ( $person->isGroupModerator( $currentGroup ) ) {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1'></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=0></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=1 checked></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=2></td>";
					}
					else if (!$person->isGroupGuest($currentGroup)) {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1'></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=0 checked></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=1></td>";
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=2></td>";
					}
					else {
						print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value='-1' checked></td>";
                                                print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=0></td>";
                                                print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=1></td>";
                                                print "<td align='center'><input type='radio' name='access[".$person->getID()."]' value=2></td>";					
					}
					print "</tr>";
				}
?>
			</table>
			<input type="submit" value="Update" name="updategroup">
		</form>
		<div id="adduser">
			<form method="post" action="group.php">
				<h1>Add User</h1>
				Email address:<input type="text" name="email"><br>
				<input type="submit" value="Add User" name="adduser">
			</form>
		</div>
<?php
	}
?>
</body>
</html>
