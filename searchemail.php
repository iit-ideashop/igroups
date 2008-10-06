<?php
	include_once("checklogin.php");
	include_once( "classes/email.php" );
	include_once( "classes/category.php" );
	
	$searchParams = "";
	if(isset($_POST['senderSearch']) && is_numeric($_POST['senderSearch']) && $_POST['senderSearch'] > -1)
		$searchParams .= "and iSenderID=".$_POST['senderSearch']." ";
	if(isset($_POST['categorySearch']) && is_numeric($_POST['categorySearch']) && $_POST['categorySearch'] > -1)
		$searchParams .= "and iCategoryID=".$_POST['categorySearch']." ";
	if(isset($_POST['subjectSearch']) && trim($_POST['subjectSearch']) != "")
		$searchParams .= "and match(sSubject) against('".mysql_real_escape_string(stripslashes($_POST['subjectSearch']))."' in boolean mode) ";
	if(isset($_POST['bodySearch']) && trim($_POST['bodySearch']) != "")
		$searchParams .= "and match(sBody) against('".mysql_real_escape_string(stripslashes($_POST['bodySearch']))."' in boolean mode) ";
	
	$query = $db->igroupsQuery("select iID from Emails where iGroupID=".$currentGroup->getID()." and iGroupType=".$currentGroup->getType()." and iSemesterID=".$currentGroup->getSemester()."  $searchParams order by iID desc");
	$emails = array();
	while($row = mysql_fetch_row($query))
		$emails[] = new Email($row[0], $db);
	
	function printTR()
	{
		static $i=0;
		if ( $i )
			print "<tr class=\"shade\">";
		else
			print "<tr>";
		$i=!$i;
	}
?>
		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Email Search Results</title>
<link rel="stylesheet" href="default.css" type="text/css" />
<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
</head>
<body>
<?php
require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		echo $currentGroup->getName();
?>
	</div>
<form method="post" action="searchemail.php"><fieldset><table>
	<tr><td><label for="subjectSearch">Subject:</label></td><td><input type="text" name="subjectSearch" id="subjectSearch" value="<?php echo htmlspecialchars(stripslashes($_POST['subjectSearch'])); ?>" /></td><td><label for="senderSearch">Sender:</label></td><td>
	<select name="senderSearch" id="senderSearch"><option value="-1">Any</option>
<?php
	$people = $currentGroup->getGroupMembers();
		$iid = "";
		$i = 0;
		foreach($people as $person)
		{
			if($i)
				$iid .= ",";
			$i++;
			$iid .= $person->getID();
		}
		$query = $db->igroupsQuery("select iID, sLName, sFName from People where iID in ($iid) order by sLName, sFName");
		while($row = mysql_fetch_row($query))
		{
			if($_POST['senderSearch'] == $row[0])
				echo "<option value=\"".$row[0]."\" selected=\"selected\">".$row[1].", ".$row[2]."</option>\n";
			else
				echo "<option value=\"".$row[0]."\">".$row[1].", ".$row[2]."</option>\n";
		}
?>			
	</select></td></tr>
	<tr><td><label for="bodySearch">Body:</label></td><td><input type="text" name="bodySearch" id="bodySearch" value="<?php echo htmlspecialchars(stripslashes($_POST['bodySearch'])); ?>" /></td><td><label for="categorySearch">Category:</label></td><td><select name="categorySearch" id="categorySearch"><option value="-1">Any</option><option value="0"<?php if($_POST['categorySearch'] == 0) echo ' selected="selected"'; ?>>Uncategorized</option>
<?php
	$categories = $currentGroup->getGroupCategories();
	foreach($categories as $category)
	{
		if($_POST['categorySearch'] == $category->getID())
			echo "<option value=\"".$category->getID()."\" selected=\"selected\">".$category->getName()."</option>\n";
		else
			echo "<option value=\"".$category->getID()."\">".$category->getName()."</option>\n";
	}
?>
	</select></td></tr>
	<tr><td><input type="submit" name="search" /></td><td colspan="3" style="font-size: smaller"><strong>Note:</strong> Subject and body search terms use <a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" onclick="window.open(this.href); return false;">implied boolean logic</a>.</td></tr></table></fieldset></form>
<div id="emails">
	<table>
<?php				
	if(count($emails) > 0)
	{
		echo "<tr><th>Subject</th><th>Sender</th><th>Date</th><th>Category</th></tr>\n";
		foreach ($emails as $email)
		{
			$author = $email->getSender();
			$cat = $email->getCategory();
			printTR();
			if($email->hasAttachments()) 
				$img = '&nbsp;<img src="img/attach.png" alt="(Attachments)" style="border-style: none" title="Paper clip" />';
			else
				$img = '';
			echo "<td><a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->getID()."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">".htmlspecialchars($email->getShortSubject())."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td><td>".$cat->getName()."</td>";
			echo "</tr>\n";
		}
	}
	else
		echo "<tr><td>Your search returned no results.</td></tr>\n";
?>
	</table>
</div></body>
</html>
