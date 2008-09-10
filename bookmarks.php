<?php 
	session_start();
	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
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

	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
	
	if(isset($_POST['delete']) && ($currentUser->getID == $row['iAuthorID'] || $currentUser->isGroupModerator($currentGroup)))
	{
		$query = $db->igroupsQuery("select iID from Bookmarks where iGroupID=".$currentGroup->getID());
		while($row = mysql_fetch_row($query))
		{
			if(isset($_POST['del'.$row[0]]))
				$db->igroupsQuery("delete from Bookmarks where iID=".$row[0]);
		}
		$message = "The selected bookmarks have been deleted.";
	}
	else if(isset($_POST['add']))
	{
		$values = "( ".$currentGroup->getID().", ".$currentUser->getID().", '".mysql_real_escape_string($_POST['title'])."', '".mysql_real_escape_string($_POST['url'])."' )";
		$db->igroupsQuery("insert into Bookmarks (iGroupID, iAuthorID, sTitle, sURL) values $values");
		$message = "The bookmark has been added.";
	}
	else if(isset($_POST['editid']) && is_numeric($_POST['editid']) && ($currentUser->getID == $row['iAuthorID'] || $currentUser->isGroupModerator($currentGroup)))
	{
		$db->igroupsQuery("update Bookmarks set sTitle='".mysql_real_escape_string($_POST['title'])."', sURL='".mysql_real_escape_string($_POST['url'])."' where iID=".$_POST['editid']);
		$message = "The bookmark has been edited.";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Bookmarks</title>
<link rel="stylesheet" href="default.css" type="text/css" />
<style type="text/css">
#bookmarks td
{
	padding: 5px;
}
</style>
</head><body>
<?php require("sidebar.php"); ?>
<div id="content">
<div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<h1>Bookmarks</h1>
<p>Bookmarks in iGROUPS operate much like bookmarks in your web browser. Add URLs for other members in your group to be able to access at a click.</p>
<?php
if(isset($message))
	echo "<p style=\"font-weight: bold\">$message</p>";
$query = $db->igroupsQuery("select * from Bookmarks where iGroupID=".$currentGroup->getID());
if(isset($_GET['edit']) && is_numeric($_GET['edit']))
{
	$query = $db->igroupsQuery("select * from Bookmarks where iID=".$_GET['edit']." and iGroupID=".$currentGroup->getID()." order by dDate desc");
	if(mysql_num_rows($query) > 0)
	{
		$row = mysql_fetch_array($query);
		echo "<form method=\"post\" action=\"bookmarks.php\"><fieldset><legend>Edit Bookmark</legend>\n";
		echo "<label for=\"title\">Title</label><input type=\"text\" id=\"title\" name=\"title\" value=\"".htmlspecialchars($row['sTitle'])."\" /><br />\n";
		echo "<label for=\"url\">URL</label><input type=\"text\" id=\"url\" name=\"url\" value=\"".htmlspecialchars($row['sURL'])."\" /><br />\n";
		echo "<input type=\"hidden\" name=\"editid\" value=\"".$_GET['edit']."\" /><input type=\"submit\" value=\"Edit Bookmark\" /></fieldset></form>\n";
	}
	else
		die("That bookmark is not in your current group.");
}
else if(mysql_num_rows($query) > 0) { ?>
<div id="bookmarks"><form method="post" action="bookmarks.php"><fieldset><legend>Current Bookmarks</legend><table>
<tr><th>Bookmark</th><th>Submitted By</th><th>Date</th><th>Edit</th><th>Delete</th></tr>
<?php
	while($row = mysql_fetch_array($query))
	{
		$author = new Person($row['iAuthorID'], $db);
		echo "<tr><td><a href=\"".htmlspecialchars($row['sURL'])."\" title=\"".htmlspecialchars($row['sTitle'])."\" onclick=\"\">".htmlspecialchars($row['sTitle'])."</a></td><td>".$author->getCommaName()."</td><td>".$row['dDate']."</td>";
		if($currentUser->getID == $row['iAuthorID'] || $currentUser->isGroupModerator($currentGroup))
			echo "<td><a href=\"bookmarks.php?edit=".$row['iID']."\">Edit</a></td><td><input type=\"checkbox\" name=\"del".$row['iID']."\" /></td></tr>\n";
		else
			echo "</tr>\n";
	}
?>
</table><input type="submit" name="delete" id="delete" value="Delete Selected" /></fieldset></form></div>
<?php } else { echo "<p>Your group does not have any bookmarks.</p>\n"; } if(!isset($_GET['edit']) || !is_numeric($_GET['edit'])) { ?>
<form method="post" action="bookmarks.php"><fieldset><legend>Add Bookmark</legend>
<label for="title">Title</label><input type="text" id="title" name="title" /><br />
<label for="url">URL</label><input type="text" id="url" name="url" /><br />
<input type="submit" name="add" id="add" value="Add Bookmark" />
</fieldset></form><?php } ?>
</div></body></html>
