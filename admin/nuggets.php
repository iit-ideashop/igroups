<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once("../classes/nugget.php");
	include_once( "../classes/group.php" );
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
	
	function groupSort( $array ) {
                $newArray = array();
                foreach ( $array as $group ) {
                        if ( $group )
                                $newArray[$group->getName()] = $group;
                }
                ksort( $newArray );
                return $newArray;
        }

?>		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - IPRO Team Nuggets</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
	<style type="text/css">
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
		<form method="get" action="nuggets.php"><fieldset>
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
	
	<p style="text-align: center; font-style: italic">* Signifies a protected nugget</p>

	<table cellpadding="4" cellspacing="0" style="border: thin solid black">
		<thead>
			<tr><td>Group</td><td>Project Plan</td><td>Abstract</td><td>Code of Ethics</td><td>Midterm Report</td><td>Poster</td><td>Website</td><td>Final Presentation</td><td>Meeting Minutes</td><td>Final Report</td></tr>
		</thead>
	<tfoot>
	<tr><td>Group</td><td>Project Plan</td><td>Abstract</td><td>Code of Ethics</td><td>Midterm Report</td><td>Poster</td><td>Website</td><td>Final Presentation</td><td>Meeting Minutes</td><td>Final Report</td></tr></tfoot>
	<tbody>
	<?php
		$groups = $currentSemester->getGroups();
		$groups = groupSort( $groups );
		
		foreach ( $groups as $group ) {
			print "<tr><td><a href=\"viewIproNuggets.php?id={$group->getID()}\">{$group->getName()}</a></td>";
			if ($currentSemester->getID() >= 32) {
				$_DEFAULTNUGGETS = array('Project Plan', 'Abstract', 'Code of Ethics', 'Midterm Report', 'Poster', 'Website', 'Final Presentation', 'Team Minutes', 'Final Report');
				$nuggets = getNuggetStatus($group, $currentSemester->getID());
				if ($nuggets['Project Plan'] != 0) {
					$nug = new Nugget($nuggets['Project Plan'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Project Plan']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Abstract'] != 0) {
					$nug = new Nugget($nuggets['Abstract'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Abstract']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Code of Ethics'] != 0) {
					$nug = new Nugget($nuggets['Code of Ethics'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Code of Ethics']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Midterm Report'] != 0) {
					$nug = new Nugget($nuggets['Midterm Report'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Midterm Report']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Poster'] != 0) {
					$nug = new Nugget($nuggets['Poster'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Poster']}&amp;groupID={$group->getID()}\">View$priv</a></td>";			
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Website'] != 0) {
					$nug = new Nugget($nuggets['Website'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Website']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Final Presentation'] != 0) {
					$nug = new Nugget($nuggets['Final Presentation'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Final Presentation']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Team Minutes'] != 0) {
					$nug = new Nugget($nuggets['Team Minutes'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Team Minutes']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Final Report'] != 0) {
					$nug = new Nugget($nuggets['Final Report'], $db, 0);
                                        if ($nug->isPrivate())
                                                $priv = '*';
                                        else
                                                $priv = '';
                                        print "<td align=\"center\"><a href=\"viewNugget.php?nuggetID={$nuggets['Final Report']}&amp;groupID={$group->getID()}\">View$priv</a></td>";
				}
                                else
                                        print "<td align=\"center\"><b>N/A</b></td>";
			}
			else {
				$_DEFAULTNUGGETS = array('Project Plan', 'Abstract', 'Code of Ethics', 'Midterm Report', 'Poster', 'Web Site', 'Final Presentation', 'Team Minutes', 'Final Report');
				$nuggets = getOldNuggetsByGroupAndSemester($group, $currentSemester->getID());
				foreach ($_DEFAULTNUGGETS as $def) {
				$link = null;
				foreach($nuggets as $nug) {
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					if (strstr($nug->getType(), $def))
						$link = "<a href=\"viewNugget.php?nuggetID={$nug->getID()}&amp;groupID={$group->getID()}&amp;isOld=1\">View$priv</a>&nbsp;";
				}	
				if (!$link)
					$link = "<b>N/A</b>";
				print "<td align=\"center\">$link</td>";
				}
			}
			print "</tr>";
		}
		
	?>
	</tbody></table></div>
</body>
</html>
