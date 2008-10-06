<?php
	include_once("checklogin.php");
	include_once( "classes/email.php" );
	include_once( "classes/category.php" );
	
	if(isset($_POST['senderSearch']) && is_numeric($_POST['senderSearch']) && $_POST['senderSearch'] > -1)
		$sndr = "and iSenderID=".$_POST['senderSearch'];
	else
		$sndr = "";
	if(isset($_POST['categorySearch']) && is_numeric($_POST['categorySearch']) && $_POST['categorySearch'] > -1)
		$ctgy = "and iCategoryID=".$_POST['categorySearch'];
	else
		$ctgy = "";
	
	$query = $db->igroupsQuery("select iID from Emails where iGroupID=".$currentGroup->getID()." and iGroupType=".$currentGroup->getType()." and iSemesterID=".$currentGroup->getSemester()." $sndr $ctgy and match(sSubject) against('".mysql_real_escape_string($_POST['subjectSearch'])."' in boolean mode) and match(sBody) against('".mysql_real_escape_string($_POST['bodySearch'])."' in boolean mode)");
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
<form method="post" action="searchemail.php"><fieldset><table width="100%">
	<tr><td><label for="subjectSearch">Subject:</label></td><td><input type="text" name="subjectSearch" id="subjectSearch" value="<?php echo htmlspecialchars($_POST['subjectSearch']); ?>" /></td><td><label for="senderSearch">Sender:</label></td><td>
	<select name="senderSearch" id="senderSearch"><option value="-1">Any</option>
<?php
	$people = $currentGroup->getGroupUsers();
	foreach($people as $person)
	{
		if($_POST['senderSearch'] == $person->getID())
			echo "<option value=\"".$person->getID()."\" selected=\"selected\">".$person->getCommaName()."</option>\n";
		else
			echo "<option value=\"".$person->getID()."\">".$person->getCommaName()."</option>\n";
	}
?>			
	</select></td></tr>
	<tr><td><label for="bodySearch">Body:</label></td><td><input type="text" name="bodySearch" id="bodySearch" value="<?php echo htmlspecialchars($_POST['bodySearch']); ?>" /></td><td><label for="categorySearch">Category:</label></td><td><select name="categorySearch" id="categorySearch"><option value="-1">Any</option><option value="0">Uncategorized</option>
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
	<table width="100%">
<?php				
	if(count($emails) > 0)
	{
		foreach ($emails as $email)
		{
			$author = $email->getSender();
			printTR();
			if($email->hasAttachments()) 
				$img = '&nbsp;<img src="img/attach.png" alt="(Attachments)" style="border-style: none" title="Paper clip" />';
			else
				$img = '';
			echo "<td colspan=\"2\"><a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->getID()."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">".htmlspecialchars($email->getShortSubject())."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td>";
			echo "</tr>";
		}
	}
	else
		echo "<tr><td>Your search returned no results.</td></tr>";
?>
	</table>
</div></body>
</html>
