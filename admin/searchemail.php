<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/groupemail.php');
	
	//---------Begin XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Email Search</title>
</head>
<body>
<?php
		 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
	echo "<div id=\"topbanner\">Group Emails</div>\n";
?>
	<table width="100%">
		<tr>
			<td>
				<form method="get" action="searchemail.php"><fieldset><legend>Search Text</legend>
				<label>Keyword: <input type="text" name="keyword" /></label>
				<input type="submit" name="kwsearch" value="Search" />
				</fieldset></form>
			</td>
		</tr>
	</table>
<?php
	
	if(isset($_GET['kwsearch']))
	{
		$query = $db->query("SELECT iID FROM GroupEmails WHERE sBody LIKE '%{$_GET['keyword']}%'"); 
		$emails = array();
		while($row = mysql_fetch_row($query))
			$emails[] = new GroupEmail($row[0], $db);
	}
	
	if(isset($emails))
	{
		echo "<table>\n";
		foreach($emails as $email)
		{
			$author = $email->getSender();
			echo "<tr><td></td><td><a href=\"displayemail.php?id=".$email->getID()."\">".$email->getShortSubject()."</a></td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td></tr>\n";
		}
		echo "</table>\n";
	}
?>
<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>
