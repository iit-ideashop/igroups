<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/semester.php');
	
	if(isset($_POST['setActive']))
	{
		$id = substr($_POST['active'], 1);
		if(!is_numeric($id))
			errorPage('Bad Input', '$id is not numeric', 400);
		$sem = new Semester($id, $db);
		if($sem->setActive())
			$message = 'Active semester updated';
		else
			$message = 'ERROR: Active semester was not updated';
	}
	else if(isset($_POST['new']))
	{
		if(createSemester($_POST['newname'], isset($_POST['newActive']) ? 1 : 0, $db))
			$message = 'New semester created';
		else
			$message = 'ERROR: New semester was not created';
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
<title><?php echo $appname;?> - Manage Semesters</title>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\"><div id=\"topbanner\">Manage Semesters</div>\n";
	echo "<form action=\"semesters.php\" method=\"post\"><fieldset><legend>Semesters</legend><table>\n";
	echo "<thead><tr><th>Semester</th><th>Teams</th><th>Make Active</th></tr></thead><tbody>\n";
	$query = $db->igroupsQuery("select * from Semesters order by iID desc");
	while($row = mysql_fetch_array($query))
	{
		$semester = new Semester($row['iID'], $db);
		$id = $semester->getID();
		$name = str_replace('&', '&amp;', $semester->getName());
		$numteams = $semester->getCount();
		echo "<tr><td><label for=\"S$id\">$name</label></td><td>$numteams</td>";
	
		if($semester->isActive())
			echo "<td><input type=\"radio\" name=\"active\" id=\"S$id\" value=\"S$id\" checked=\"checked\" /></td></tr>\n";
		else
			echo "<td><input type=\"radio\" name=\"active\" id=\"S$id\" value=\"S$id\" /></td></tr>\n";
	}
	echo "</tbody></table><input type=\"submit\" name=\"setActive\" value=\"Set Active Semester\" /></fieldset>\n";
	echo "<fieldset><legend>New Empty Semester</legend>\n";
	echo "<label>Name: <input type=\"text\" name=\"newname\" /></label>\n";
	echo "<label><input type=\"checkbox\" name=\"newActive\" /> Make Active</label>\n";
	echo "<input type=\"submit\" name=\"new\" value=\"Create Semester\" /></fieldset></form>\n";
?>
</div></body></html>
