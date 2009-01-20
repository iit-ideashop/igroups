<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
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
<title><?php echo $appname;?> - Group Semester Move</title>
</head>
<body>
<?php
	require("sidebar.php");
	print "<div id=\"content\">";
	if(is_numeric($_GET['gid']))
	{
		echo "<h1>Group Semester Mover Thingy Step 2</h1>\n";
		$group = mysql_fetch_array($db->igroupsQuery('select * from Projects where iID='.$_GET['gid']));
		echo "<ul><li><b>Group:</b> {$group['sName']}</li>\n";
		$sem = mysql_fetch_array($db->igroupsQuery('select * from Semesters where iID in (select iSemesterID from ProjectSemesterMap where iProjectID='.$_GET['gid'].')'));
		echo "<li><b>Semester:</b> {$sem['sSemester']}</li></ul>\n";
		echo "<p>What semester do you want to move this group into? Note: Submitting this form WILL move the group!</p>\n";
		echo "<form method=\"post\"><fieldset><legend>Move Group</legend>\n";
		echo "<label>Semester:<br /><select name=\"semester\"><option value=\"bananapudding\" selected=\"selected\">Select a semester</option>\n";
		$sems = $db->igroupsQuery("select * from Semesters where iID<>{$sem['iID']}");
		while($row = mysql_fetch_array($sems))
			echo "<option value=\"{$row['iID']}\">{$row['sSemester']}</option>\n";
		echo "</select></label><input type=\"hidden\" name=\"gid\" value=\"{$_GET['gid']}\" /><input type=\"submit\" /></fieldset></form>\n";
	}
	else if(is_numeric($_POST['gid']) && is_numeric($_POST['semester']))
	{
		echo "<h1>Group Semester Mover Thingy is go!</h1>\n<ul>";
		$group = $_POST['gid'];
		$sem = $_POST['semester'];
		$g = 'iGroupID';
		$p = 'iProjectID';
		$tables = array( //Since some work-creating people put redundancies in the database, there are iSemesterIDs all over the place. This array holds all the relevant ones.
			'Announcements' => $g,
			'BudgetEmails' => $p,
			'Budgets' => $p,
			'BudgetsHistory' => $p,
			'Categories' => $g,
			'Emails' => $g,
			'Events' => $g,
			'FileQuota' => $g,
			'Files' => $g,
			'Folders' => $g,
			'GroupAccessMap' => $g
			'GroupListMap' => $g,
			'iGroupsNuggets' => $g,
			'PeopleProjectMap' => $p,
			'Pictures' => $g,
			'ProjectNuggetMap' => $p,
			'ProjectSemesterMap' => $p,
			'Timesheets' => $g,
			'TodoList' => $g
		);
		$fail = false;
		foreach($tables as $table => $nm)
		{
			echo "<li>Updating table <b>$table</b> (which stores the group ID in <b>$nm</b>)...";
			$query = $db->igroupsQuery("update $table set iSemesterID=$sem where $nm=$group");
			echo ($query ? '<b>OK</b>' : '<b style="color: red">FAIL</b>'.mysql_error());
			if(!$query)
				$fail = true;
			echo "</li>\n";
		}
		echo "</ul>\n";
		if($fail)
			echo "<p>Uh oh, one of the queries barfed. You might need to perform some manual cleanup in the database.</p>\n";
	}
	else
	{
		echo "<h1>Group Semester Mover Thingy</h1>\n";
		echo "<p>This form will automagically move a group from one semester to another. You must know the group's ID to continue. (You can find that if you select your group on <a href=\"group.php\">group.php</a> and look at the query string.) Submitting this form will not yet make any changes.</p>\n";
		echo "<form method=\"get\"><fieldset><legend>Select Group</legend>\n";
		echo "<label>Group ID: <input type=\"text\" name=\"gid\" /></label>\n";
		echo "<input type=\"submit\" /></fieldset></form>\n";
	}
?>
</div></body>
</html>
