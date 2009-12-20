<?php
	include_once('globals.php');
	include_once('checklogin.php');
	
	//------Start of Code for Form Processing-----------------------//
	
	if(isset($_POST['delete']))
	{
		$query = $db->query("select * from Bookmarks where iGroupID=".$currentGroup->getID());
		while($row = mysql_fetch_array($query))
		{
			if(isset($_POST['del'.$row['iID']]) && ($currentUser->getID() == $row['iAuthorID'] || $currentUser->isGroupModerator($currentGroup)))
				$db->query("delete from Bookmarks where iID=".$row[0]);
		}
		$message = "The selected bookmarks have been deleted.";
	}
	else if(isset($_POST['add']))
	{
		if(!isset($_POST['title']) || $_POST['title'] == '' || !isset($_POST['url']) || $_POST['url'] == '' || $_POST['url'] == 'http://')
			$message = "Please fill both the title field and the URL field before adding a bookmark.";
		else
		{
			$values = "( ".$currentGroup->getID().", ".$currentUser->getID().", '".$_POST['title']."', '".$_POST['url']."', '".$_POST['desc']."', ".(is_numeric($_POST['folder']) && $_POST['folder'] > 1 ? $_POST['folder'] : 'null')." )";
			$db->query("insert into Bookmarks (iGroupID, iAuthorID, sTitle, sURL, sDesc, iFolder) values $values");
			$message = "The bookmark has been added.";
		}
	}
	else if(isset($_POST['addf']))
	{
		if(!isset($_POST['title']) || $_POST['title'] == '')
			$message = "Please fill the title field before adding a folder.";
		else
		{
			$values = "( ".$currentGroup->getID().", '".$_POST['title']."')";
			$db->query("insert into BookmarkFolders (iGroupID, sTitle) values $values");
			$message = "The folder has been added.";
		}
	}
	else if(isset($_POST['editid']) && is_numeric($_POST['editid']))
	{
		$okay = false;
		if(!$currentUser->isGroupModerator($currentGroup))
		{
			$row = mysql_fetch_row($db->query("select iAuthorID from Bookmarks where iID=".$_POST['editid']));
			$okay = ($row[0] == $currentUser->getID());
		}
		else
			$okay = true;

		if(!is_numeric($_POST['folder']) || $_POST['folder'] == 1)
			$okay = false;

		if($okay)
		{
			$db->query("update Bookmarks set sTitle='".$_POST['title']."', sURL='".$_POST['url']."', sDesc='".$_POST['desc']."', iFolder=".($_POST['folder'] ? $_POST['folder'] : 'null')." where iID=".$_POST['editid']);
			$message = "The bookmark has been edited.";
		}
		else
			$message = "You don't have permissions to edit that bookmark.";
	}
	
	if(isset($_GET['folder']) && is_numeric($_GET['folder']))
	{
		$row = mysql_fetch_row($db->query('select iGroupID, sTitle from BookmarkFolders where iID='.$_GET['folder']));
		if($_GET['folder'] > 1 && ($row && $row[0] == $currentGroup->getID()))
		{
			$BF = $_GET['folder'];
			$BFQ = '='.$_GET['folder'];
			$BFN = stripslashes($row[1]);
		}
		else if($_GET['folder'] == 1)
		{
			$BF = 1;
			$BFQ = '=1';
			$BFN = 'IPRO Office Bookmarks';
		}
		else
		{
			$BF = 0;
			$BFQ = ' is null';
			$BFN = 'Unfiled';
		}
	}
	else
	{
		$BF = 0;
		$BFQ = ' is null';
		$BFN = 'Unfiled';
	}
	
	//------End of Code for Form Processing-------------------------//
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require("appearance.php");
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Bookmarks</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
</head>
<body>

<?php
/**** begin html head *****/
   require('htmlhead.php'); //starts main container
/****end html head content ****/	
?>

<div id="topbanner"><?php echo $currentGroup->getName(); ?></div>
<h1>Bookmarks</h1>
<p>Bookmarks in <?php echo $appname; ?> operate much like bookmarks in your web browser. Add URLs for other members in your group to be able to access at a click.</p>
<?php
	$query = $db->query("select * from Bookmarks where iGroupID=".$currentGroup->getID()." and iFolder$BFQ order by sTitle");
	if(isset($_GET['edit']) && is_numeric($_GET['edit']))
	{
		$query = $db->query("select * from Bookmarks where iID=".$_GET['edit']." and iGroupID=".$currentGroup->getID());
		if(mysql_num_rows($query) > 0)
		{
			$row = mysql_fetch_array($query);
			if(($currentUser->isGroupModerator($currentGroup) || $row['iAuthorID'] == $currentUser->getID()) && $row['iFolder'] != 1)
			{
				echo "<form method=\"post\" action=\"bookmarks.php\"><fieldset><legend>Edit Bookmark</legend>\n";
				echo "<label for=\"title\">Title</label>&nbsp;<input type=\"text\" id=\"title\" name=\"title\" value=\"".htmlspecialchars($row['sTitle'])."\" /><br />\n";
				echo "<label for=\"url\">URL</label>&nbsp;<input type=\"text\" id=\"url\" name=\"url\" value=\"".htmlspecialchars($row['sURL'])."\" /><br />\n";
				echo "<label for=\"desc\">Description</label>&nbsp;<input type=\"text\" id=\"desc\" name=\"desc\" value=\"".htmlspecialchars($row['sDesc'])."\" /><br />\n";
				echo "<label for=\"folder\">Folder</label>&nbsp;<select id=\"folder\" name=\"folder\"><option value=\"0\">Unfiled</option>\n";
				$query2 = $db->query("select iID, sTitle from BookmarkFolders where iGroupID=".$currentGroup->getID());
				while($row2 = mysql_fetch_row($query2))
					echo '<option value="'.$row2[0].'"'.($row2[0] == $row['iFolder'] ? ' selected="selected"' : '').'>'.$row2[1]."</option>\n";
				echo "</select><br />\n";
				echo "<input type=\"hidden\" name=\"editid\" value=\"".$_GET['edit']."\" /><input type=\"submit\" value=\"Edit Bookmark\" /></fieldset></form>\n";
			}
			else
				die("You don't have permissions to edit that bookmark.");
		}
		else
			die("That bookmark is not in your current group.");
	}
	else if(mysql_num_rows($query) > 0 || $BF == 1)
	{
		if($BF == 1)
			$query = $db->query("select * from Bookmarks where iFolder=1 order by sTitle");
		else
			$quer = $db->query("select * from Bookmarks where iAuthorID=".$currentUser->getID()." and iFolder$BFQ and iGroupID=".$currentGroup->getID());
		if($BF != 1 && ($currentUser->isGroupModerator($currentGroup) || mysql_num_rows($quer) > 0))
			$hasDel = true;
		else
			$hasDel = false;
		echo "<div id=\"bookmarks\">";
		echo "<form method=\"get\"><fieldset><legend>Select Folder</legend>\n";
		echo "<select name=\"folder\"><option value=\"0\"".(0 == $BF ? ' selected="selected"' : '').">Unfiled</option>\n<option value=\"1\"".(1 == $BF ? ' selected="selected"' : '').">IPRO Office Bookmarks</option>\n";
		$query2 = $db->query("select iID, sTitle from BookmarkFolders where iGroupID=".$currentGroup->getID());
		while($row = mysql_fetch_row($query2))
			echo '<option value="'.$row[0].'"'.($row[0] == $BF ? ' selected="selected"' : '').'>'.$row[1]."</option>\n";
		echo "</select><br /><input type=\"submit\" value=\"Select Folder\" /></fieldset></form>\n";
		if($hasDel)
			echo "<form method=\"post\" action=\"bookmarks.php\"><fieldset><legend>Bookmarks in $BFN</legend>\n";
		else
			echo "<h1>Bookmarks in $BFN</h1>\n";
		if(mysql_num_rows($query) > 0)
		{
			echo "<table><tr><th>Bookmark</th><th style=\"max-width: 400px;\">Description</th><th>Submitted By</th>";
			if($hasDel)
				echo "<th>Edit</th><th>Delete</th>";
			echo "</tr>\n";
			while($row = mysql_fetch_array($query))
			{
				$author = new Person($row['iAuthorID'], $db);
				echo "<tr><td><a href=\"".htmlspecialchars($row['sURL'])."\" title=\"".htmlspecialchars($row['sTitle'])."\" onclick=\"window.open(this.href); return false;\" onkeypress=\"window.open(this.href); return false;\">".htmlspecialchars($row['sTitle'], ENT_NOQUOTES)."</a></td><td>".htmlspecialchars($row['sDesc'], ENT_NOQUOTES)."</td><td>".$author->getCommaName()."</td>";
				if($BF != 1 && ($currentUser->getID() == $row['iAuthorID'] || $currentUser->isGroupModerator($currentGroup)))
					echo "<td><a href=\"bookmarks.php?edit=".$row['iID']."\">Edit</a></td><td><input type=\"checkbox\" name=\"del".$row['iID']."\" /></td></tr>\n";
				else
					echo "</tr>\n";
			}
			echo "</table>";
			if($hasDel)
				echo "<input type=\"submit\" name=\"delete\" id=\"delete\" value=\"Delete Selected\" /></fieldset></form>";
		}
		else
			echo "<p>This folder does not have any bookmarks.</p>\n";
		echo "</div>";
	}
	else
	{
		echo "<form method=\"get\"><fieldset><legend>Select Folder</legend>\n";
		echo "<select name=\"folder\"><option value=\"0\"".(0 == $BF ? ' selected="selected"' : '').">Unfiled</option>\n<option value=\"1\"".(1 == $BF ? ' selected="selected"' : '').">IPRO Office Bookmarks</option>\n";
		$query2 = $db->query("select iID, sTitle from BookmarkFolders where iGroupID=".$currentGroup->getID());
		while($row = mysql_fetch_row($query2))
			echo '<option value="'.$row[0].'"'.($row[0] == $BF ? ' selected="selected"' : '').'>'.$row[1]."</option>\n";
		echo "</select><br /><input type=\"submit\" value=\"Select Folder\" /></fieldset></form>\n";
		echo "<p>This folder does not have any bookmarks.</p>\n";
	} 
	if(!isset($_GET['edit']) || !is_numeric($_GET['edit']))
	{
?>
		<form method="post" action="bookmarks.php"><fieldset><legend>Add Bookmark</legend>
		<label for="title">Title</label>&nbsp;<input type="text" id="title" name="title" /><br />
		<label for="url">URL</label>&nbsp;<input type="text" id="url" name="url" value="http://" /><br />
		<label for="desc">Description</label>&nbsp;<input type="text" id="desc" name="desc" /><br />
		<label for="folder">Folder</label>&nbsp;<select id="folder" name="folder"><option value="0">Unfiled</option>
<?php
		$query = $db->query("select iID, sTitle from BookmarkFolders where iGroupID=".$currentGroup->getID());
		while($row = mysql_fetch_row($query))
			echo '<option value="'.$row[0].'">'.$row[1]."</option>\n";
?>
		</select><br />
		<input type="submit" name="add" id="add" value="Add Bookmark" />
		</fieldset></form>
		<form method="post" action="bookmarks.php"><fieldset><legend>Add Folder</legend>
		<label for="title">Title</label>&nbsp;<input type="text" id="title" name="title" /><br />
		<input type="submit" name="addf" id="addf" value="Add Folder" />
		</fieldset></form>
<?php
	}
?>
</div></body></html>
