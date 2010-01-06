<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/quota.php');
	include_once('../classes/semester.php');

	function groupSort($array)
	{
		$newArray = array();
		foreach($array as $group)
		{
			if($group)
				$newArray[$group->getName()] = $group;
		}
		ksort($newArray);
		return $newArray;
	}

	if(isset($_GET['selectSemester']))
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
	
	if(!isset($_SESSION['selectedIPROSemester']))
	{
		$semester = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($semester);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}
	
	if($_SESSION['selectedIPROSemester'] != 0)
		$currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db );
	else
		$currentSemester = 0;
	
	if(isset($_POST['updatelimit']))
	{
		if($currentSemester)
		{
			$semID = $currentSemester->getID();
			$gType = 0;
		}
		else
		{
			$semID = 0;
			$gType = 1;
		}
		
		foreach($_POST['quota'] as $key => $val)
		{
			if($val != '')
			{
				$group = new Group( $key, $gType, $semID, $db );
				$quota = new Quota( $group, $db );
				$quota->setLimit( $val );
				$quota->updateDB();
			}
		}
		$message = 'Quotas successfully updated';
	}
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/quota.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/quota.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - IPRO Quota Management</title>
</head>
<body>
<?php
		 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/

	echo "<div id=\"topbanner\">";
	if($currentSemester)
		echo $currentSemester->getName();
	else
		echo 'All iGROUPS';
?>
	</div>
	<div id="semesterSelect">
		<form method="get" action="quotas.php"><fieldset>
			<select name="semester">
<?php
	$semesters = $db->query('SELECT iID FROM Semesters ORDER BY iID DESC');
	while($row = mysql_fetch_row($semesters))
	{
		$semester = new Semester( $row[0], $db );
		if($currentSemester && $currentSemester->getID() == $semester->getID())
			echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>\n";
		else
			echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>\n";
	}
	if(!$currentSemester)
		echo "<option value=\"0\" selected=\"selected\">All iGROUPS</option>\n";
	else
		echo "<option value=\"0\">All iGROUPS</option>\n";
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
		if($currentSemester)
			$groups = $currentSemester->getGroups();
		else
		{
			$groupResults = $db->query( "SELECT iID FROM Groups" );
			$groups = array();
			while($row = mysql_fetch_row($groupResults))
				$groups[] = new Group($row[0], 1, 0, $db);
		}
		
		$groups = groupSort($groups);
		
		foreach($groups as $group)
		{
			$quota = new Quota($group, $db);
			if(!$quota)
				$quota = createQuota($group, $db);
			printTR();
			$pix = round($quota->getPercentUsed());
			echo "<td>{$group->getName()}</td>";
			echo "<td><div class=\"fullness-bar\" style=\"width:102px; height:15px;\"><div class=\"fullness-indicator\" style=\"height:13px;width:{$pix}px;\"></div></div></td>";
			echo "<td>".round($quota->getLimit()/1048576, 2)." MiB</td>";
			echo "<td><input type=\"text\" name='quota[".$group->getID()."]' style=\"height:16px;font-size:8pt;\" /> bytes</td>";
			echo "</tr>\n";
		}
?>
</table>
<input type="submit" value="Update Limits" name="updatelimit" />
</fieldset></form>
<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>
