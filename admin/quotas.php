<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/quota.php" );
	include_once( "../classes/semester.php" );
	
	$db = new dbConnection();
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], "@") === FALSE)
			$userName = $_COOKIE['userID']."@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
	}
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
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class=\"shade\">";
		else
			print "<tr>";
		$i=!$i;
	}

	if ( isset( $_GET['selectSemester'] ) ) {
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
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
	
	if ( isset( $_POST['updatelimit'] ) ) {
		if ( $currentSemester ) {
			$semID = $currentSemester->getID();
			$gType = 0;
		}
		else {
			$semID = 0;
			$gType = 1;
		}
		
		foreach ( $_POST['quota'] as $key => $val ) {
			if ( $val != '' ) {
				$group = new Group( $key, $gType, $semID, $db );
				$quota = new Quota( $group, $db );
				$quota->setLimit( $val );
				$quota->updateDB();
			}
		}
		$message = "Quotas successfully updated";
	}
?>		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - IPRO Quota Management</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
	<style type="text/css">
		.fullness-bar {
			text-align:left;
			width:500px;
			height:20px;
			border: 1px solid #666666;
		}
		
		.fullness-indicator {
			margin: 1px;
			height:18px;
			background-color:#999999;
		}
		
		#semesterSelect {
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
	require("sidebar.php");
	if ( isset( $message ) )
		print "<script type=\"text/javascript\">showMessage(\"$message\");</script>";
?>	
	<div id="content"><div id="topbanner">
<?php
		if ( $currentSemester )
			print $currentSemester->getName();
		else
			print "All iGROUPS";
?>
	</div>
	<div id="semesterSelect">
		<form method="get" action="quotas.php"><fieldset>
			<select name="semester">
<?php
			$semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
			while ( $row = mysql_fetch_row( $semesters ) ) {
				$semester = new Semester( $row[0], $db );
				if ($currentSemester && $currentSemester->getID() == $semester->getID())
					print "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
				else
					print "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
			}
			if (!$currentSemester)
				print "<option value=\"0\" selected=\"selected\">All iGROUPS</option>";
			else
				print "<option value=\"0\">All iGROUPS</option>";
		
?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
		</fieldset></form>
	</div>
	<form method="post" action="quotas.php"><fieldset>
	<table>
		<thead>
			<tr><td>Group</td><td>Space Used</td><td>Limit</td><td>New Limit</td></tr>
		</thead>
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
		
		$groups = groupSort( $groups );
		
		foreach ( $groups as $group ) {
			$quota = new Quota( $group, $db );
			if ( !$quota )
				$quota = createQuota( $group, $db );
			printTR();
			print "<td>".$group->getName()."</td>";
			print "<td><div class=\"fullness-bar\" style=\"width:102px; height:15px;\"><div class=\"fullness-indicator\" style=\"height:13px;width:".$quota->getPercentUsed()."px;\"></div></div></td>";
			print "<td>".round($quota->getLimit()/1048576, 2)." MiB</td>";
			print "<td><input type=\"text\" name='quota[".$group->getID()."]' style=\"height:16px;font-size:8pt;\" /> bytes</td>";
			print "</tr>";
		}
		
	?>
	</table>
	<input type="submit" value="Update Limits" name="updatelimit" />
	</fieldset></form></div>
</body>
</html>
