<?php
	include_once("../globals.php");
	include_once( "checkadmin.php" );
	
	if(isset($_POST['delete']))
	{
		$query = $db->igroupsQuery('select * from Bookmarks where iFolder=1');
		while($row = mysql_fetch_array($query))
		{
			if(isset($_POST['del'.$row['iID']]))
				$db->igroupsQuery("delete from Bookmarks where iID=".$row[0]);
		}
		$message = "The selected bookmarks have been deleted.";
	}
	else if(isset($_POST['add']))
	{
		if(!isset($_POST['title']) || $_POST['title'] == '' || !isset($_POST['url']) || $_POST['url'] == '' || $_POST['url'] == 'http://')
			$message = "Please fill both the title field and the URL field before adding a bookmark.";
		else
		{
			$values = "(0, 1, ".$currentUser->getID().", '".$_POST['title']."', '".$_POST['url']."', '".$_POST['desc']."' )";
			$db->igroupsQuery("insert into Bookmarks (iGroupID, iFolder, iAuthorID, sTitle, sURL, sDesc) values $values");
			$message = "The bookmark has been added.";
		}
	}
	else if(isset($_POST['editid']) && is_numeric($_POST['editid']))
	{
		$db->igroupsQuery("update Bookmarks set sTitle='".$_POST['title']."', sURL='".$_POST['url']."', sDesc='".$_POST['desc']."' where iID=".$_POST['editid']);
		$message = "The bookmark has been edited.";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Global Bookmarks</title>
<?php
require("../iknow/appearance.php");
echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<style type="text/css">
#bookmarks td
{
	padding: 5px;
}
</style>
</head><body>
<?php require("sidebar.php"); ?>
<div id="content">
<div id="topbanner">Global Bookmarks</div>
<?php
$query = $db->igroupsQuery("select * from Bookmarks where iFolder=1 order by sTitle");
if(isset($_GET['edit']) && is_numeric($_GET['edit']))
{
	$query = $db->igroupsQuery("select * from Bookmarks where iID=".$_GET['edit']);
	if(mysql_num_rows($query) > 0)
	{
		$row = mysql_fetch_array($query);
		echo "<form method=\"post\" action=\"bookmarks.php\"><fieldset><legend>Edit Bookmark</legend>\n";
		echo "<label for=\"title\">Title</label>&nbsp;<input type=\"text\" id=\"title\" name=\"title\" value=\"".htmlspecialchars($row['sTitle'])."\" /><br />\n";
		echo "<label for=\"url\">URL</label>&nbsp;<input type=\"text\" id=\"url\" name=\"url\" value=\"".htmlspecialchars($row['sURL'])."\" /><br />\n";
		echo "<label for=\"desc\">Description</label>&nbsp;<input type=\"text\" id=\"desc\" name=\"desc\" value=\"".htmlspecialchars($row['sDesc'])."\" /><br />\n";
		echo "<input type=\"hidden\" name=\"editid\" value=\"".$_GET['edit']."\" /><input type=\"submit\" value=\"Edit Bookmark\" /></fieldset></form>\n";
	}
	else
		die("That bookmark does not exist.");
}
else if(mysql_num_rows($query) > 0) {
	echo "<div id=\"bookmarks\">";
	echo "<form method=\"post\" action=\"bookmarks.php\"><fieldset><legend>Current Bookmarks</legend>\n";
	echo "<table><tr><th>Bookmark</th><th style=\"max-width: 400px;\">Description</th><th>Submitted By</th><th>Edit</th><th>Delete</th></tr>\n";
	while($row = mysql_fetch_array($query))
	{
		$author = new Person($row['iAuthorID'], $db);
		echo "<tr><td><a href=\"".htmlspecialchars($row['sURL'])."\" title=\"".htmlspecialchars($row['sTitle'])."\" onclick=\"window.open(this.href); return false;\" onkeypress=\"window.open(this.href); return false;\">".htmlspecialchars($row['sTitle'], ENT_NOQUOTES)."</a></td><td>".htmlspecialchars($row['sDesc'], ENT_NOQUOTES)."</td><td>".$author->getCommaName()."</td>";
		echo "<td><a href=\"bookmarks.php?edit=".$row['iID']."\">Edit</a></td><td><input type=\"checkbox\" name=\"del".$row['iID']."\" /></td></tr>\n";
	}
	echo "</table>";
	echo "<input type=\"submit\" name=\"delete\" id=\"delete\" value=\"Delete Selected\" /></fieldset></form>";
	echo "</div>";
} else { echo "<p>Your group does not have any bookmarks.</p>\n"; } if(!isset($_GET['edit']) || !is_numeric($_GET['edit'])) { ?>
<form method="post" action="bookmarks.php"><fieldset><legend>Add Bookmark</legend>
<label for="title">Title</label>&nbsp;<input type="text" id="title" name="title" /><br />
<label for="url">URL</label>&nbsp;<input type="text" id="url" name="url" value="http://" /><br />
<label for="desc">Description</label>&nbsp;<input type="text" id="desc" name="desc" /><br />
<input type="submit" name="add" id="add" value="Add Bookmark" />
</fieldset></form><?php } ?>
</div></body></html>
