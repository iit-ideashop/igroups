<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/folder.php" );
	include_once( "classes/file.php" );
	
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
?>
<div class="window-content" id="rename">
<?php
	if(isset($_GET['fileid']) || isset($_GET['folderid']))
	{
		if(isset($_GET['fileid']))
			$curr = new File($_GET['fileid'], $db);
		else
			$curr = new Folder($_GET['folderid'], $db);
			print "<form method=\"post\" action=\"files.php\"><fieldset>";
				print "<label for=\"newname\">Enter new name:</label>";
				print "<input type=\"text\" name=\"newname\" id=\"newname\" size=\"30\" value=\"".htmlspecialchars($curr->getNameNoVer())."\" /><br />";
				print "<label for=\"newdesc\">Enter new description:</label>";
				print "<input type=\"text\" name=\"newdesc\" id=\"newdesc\" size=\"30\" value=\"".htmlspecialchars($curr->getDesc())."\" />";

				print "<input type=\"hidden\" name=\"rename\" value=\"rename\" />";
				if(isset($_GET['fileid']))
					print "<input type=\"hidden\" name=\"file\" value=\"".$_GET['fileid']."\" />";
				else
					print "<input type=\"hidden\" name=\"folder\" value=\"".$_GET['folderid']."\" />";
?>
				<br />
				<input type="submit" value="Rename" />
			</fieldset></form>
<?php
	}
	else
		print "<p>No file or folder selected.</p>";
?>
		</div>
