<?php
	include_once("globals.php");
	include_once("checklogingroupless.php");
	include_once("classes/nugget.php");
	include_once( "classes/group.php" );
	include_once( "classes/semester.php" );

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
<?php require("appearance.php"); ?>
<title><?php echo $appname;?> - Your Groups' Nuggets</title>
	<style type="text/css">
		#semesterSelect {
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
			print "All iGROUPS";
?>
	</div>
	<div id="semesterSelect">
		<form method="get" action="usernuggets.php"><fieldset>
			<select name="semester">
<?php
			$semesters = $db->igroupsQuery("select distinct iSemesterID from PeopleProjectMap where iPersonID=".$currentUser->getID()." order by iSemesterID desc");
			while($row = mysql_fetch_row($semesters))
			{
				$semester = new Semester( $row[0], $db );
				if ($currentSemester->getID() == $semester->getID())
					print "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
				else
					print "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
			}
?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
		</fieldset></form>
	</div>
	
	<p><strong>Note:</strong> This page links to nuggets' public interfaces. If you need to edit or upload a nugget, you should use the "iKnow Nuggets" link for your group.</p>
	<p style="text-align: center; font-style: italic">* Signifies a protected nugget</p>

	<table cellpadding="4" cellspacing="2" style="border: thin solid black">
		<thead>
			<tr><td>Group</td><td>Project Plan</td><td>Abstract</td><td>Ethics Statement</td><td>Midterm Report</td><td>Poster</td><td>Website (optional)</td><td>Final Presentation</td><td>Team Minutes (optional)</td><td>Final Report or Grant Proposal</td></tr>
		</thead>
	<tfoot>
	<tr><td>Group</td><td>Project Plan</td><td>Abstract</td><td>Ethics Statement</td><td>Midterm Report</td><td>Poster</td><td>Website (optional)</td><td>Final Presentation</td><td>Team Minutes (optional)</td><td>Final Report or Grant Proposal</td></tr></tfoot>
	<tbody>
	<?php
		$groups = $currentUser->getGroupsBySemester($currentSemester->getID());
		$groups = groupSort( $groups );
		
		foreach ( $groups as $group ) {
			print "<tr><td><a href=\"iknow/viewIproNuggets.php?id={$group->getID()}&amp;semester={$semester->getID()}\">{$group->getName()}</a></td>";
			if ($currentSemester->getID() >= 32) {
				$_DEFAULTNUGGETS = array('Project Plan', 'Abstract', 'Code of Ethics', 'Midterm Report', 'Poster', 'Website', 'Final Presentation', 'Team Minutes', 'Final Report');
				$nuggets = getNuggetStatus($group, $currentSemester->getID());
				if ($nuggets['Project Plan'] != 0) {
					$nug = new Nugget($nuggets['Project Plan'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Project Plan']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Abstract'] != 0) {
					$nug = new Nugget($nuggets['Abstract'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Abstract']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Code of Ethics'] != 0) {
					$nug = new Nugget($nuggets['Code of Ethics'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Code of Ethics']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Midterm Report'] != 0) {
					$nug = new Nugget($nuggets['Midterm Report'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Midterm Report']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Poster'] != 0) {
					$nug = new Nugget($nuggets['Poster'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Poster']}\">View$priv</a></td>";			
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Website'] != 0) {
					$nug = new Nugget($nuggets['Website'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Website']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Final Presentation'] != 0) {
					$nug = new Nugget($nuggets['Final Presentation'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Final Presentation']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Team Minutes'] != 0) {
					$nug = new Nugget($nuggets['Team Minutes'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Team Minutes']}\">View$priv</a></td>";
				}
				else
					print "<td align=\"center\"><b>N/A</b></td>";
				if ($nuggets['Final Report'] != 0) {
					$nug = new Nugget($nuggets['Final Report'], $db, 0);
					if ($nug->isPrivate())
						$priv = '*';
					else
						$priv = '';
					print "<td align=\"center\"><a href=\"iknow/viewNugget.php?nuggetID={$nuggets['Final Report']}\">View$priv</a></td>";
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
						$link = "<a href=\"iknow/viewNugget.php?nuggetID={$nug->getID()}&amp;isOld=1\">View$priv</a>&nbsp;";
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