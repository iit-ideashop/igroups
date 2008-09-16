<?php
	include_once("checklogin.php");
	include_once( "classes/folder.php" );
	include_once( "classes/file.php" );
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
