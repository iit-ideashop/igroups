<?php
	include_once("../globals.php");
	include_once( "checkadmin.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/groupemail.php" );
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
<title><?php echo $appname;?> - Email Search</title>
</head>
<body>
<?php
require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		print "Group Emails";
?>
	</div>
	<table width="100%">
		<tr>
			<td>
				<h1>Search Text</h1>
				<form method="get" action="searchemail.php"><fieldset>
				<label for="keyword">Keyword:</label><input type="text" name="keyword" id="keyword" />
				<input type="submit" name="kwsearch" value="Search" />
				</fieldset></form>
			</td>
		</tr>
	</table>
<?php
	
	if ( isset( $_GET['kwsearch'] ) ) {
		$query = $db->igroupsQuery("SELECT iID FROM GroupEmails WHERE sBody LIKE '%{$_GET['keyword']}%'"); 
		$emails = array();
		while ($row = mysql_fetch_row($query))
			$emails[] = new GroupEmail($row[0], $db);
	}
		
	
	if ( isset( $emails ) ) {
		print "<table>";
		foreach ( $emails as $email ) {
			$author = $email->getSender();
			print "<tr><td></td><td><a href=\"displayemail.php?id=".$email->getID()."\">".$email->getShortSubject()."</a></td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td></tr>";
		}
		print "</table>";
	}
?>
</div></body>
</html>
