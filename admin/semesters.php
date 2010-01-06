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
	
	//---------Start XHTML Output-----------------------------------//
	require('../doctype.php');
	require('../iknow/appearance.php');
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Manage Semesters</title>
</head>
<body>
<?php
 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/

	echo "<div id=\"topbanner\">Manage Semesters</div>\n";
	echo "<form action=\"semesters.php\" method=\"post\"><fieldset><legend>Semesters</legend><table>\n";
	echo "<thead><tr><th>Semester</th><th>Teams</th><th>Make Active</th></tr></thead><tbody>\n";
	$query = $db->query("select * from Semesters order by iID desc");
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

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>
