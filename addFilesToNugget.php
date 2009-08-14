<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/folder.php');
	include_once('classes/file.php');
	include_once('classes/quota.php');
	include_once('classes/nugget.php');
	include_once('nuggetTypes.php');

	global $_DEFAULTNUGGETS;
	
	//------Start of Code for Form Processing-----------------------//

	if(isset($_GET['selectFolder']))
	{
		unset($_SESSION['selectedSpecial']);
		$_SESSION['selectedFolder'] = $_GET['selectFolder'];
	}

	if(isset($_GET['selectSpecial']))
	{ 
		unset($_SESSION['selectedFolder']);
		$_SESSION['selectedSpecial'] = $_GET['selectSpecial'];
	}

	if(isset($_SESSION['selectedFolder']) && $_SESSION['selectedFolder'] != 0 && $_SESSION['selectedFolder'] != 1)
		$currentFolder = new Folder($_SESSION['selectedFolder'], $db);
	else
		$currentFolder = false;
	
	if(!isset($_SESSION['expandFolders']))
		$_SESSION['expandFolders'] = array();

	if(isset($_GET['toggleExpand']))
	{
		if(in_array($_GET['toggleExpand'], $_SESSION['expandFolders']))
		{
			$temp = array($_GET['toggleExpand']);
			$_SESSION['expandFolders'] = array_diff($_SESSION['expandFolders'], $temp);
		}
		else
			$_SESSION['expandFolders'][] = $_GET['toggleExpand'];
	}
	
	if(isset($_POST['nugget']))
	{
		$id = $_POST['nugget'];
		$nugget = new Nugget($id, $db, 0);
		$files = $_POST['files'];
		$fileList = explode(',', $files);
		foreach($fileList as $file)
			if(is_numeric($file))
				$nugget->addFile($file);
		$folderList = $_POST['folders'];
		$folders = explode(',', $folderList);
		foreach($folders as $folder)
		{
			if(is_numeric($folder))
			{
				$folder = new Folder($folder, $db);
				$folderFiles = $folder->getFiles();
				foreach($folderFiles as $file)
					$nugget->addFile($file->getID());
			}
		}
		$header = "Location:editNugget.php?nug=$id";
		header($header);
	}
	
	//------End Form Processing-------------------------------------//
	//------Start Helper Functions----------------------------------//

	function printFolder($folder)
	{	// Prints tree structure of folders
		$subfolder = $folder->getFolders();
		if($_SESSION['selectedFolder'] == $folder->getID()) //This is the selected folder
			echo "<li><img src=\"skins/$skin/img/folder-expanded.png\" alt=\"=\" title=\"Open folder\" style=\"border-style: none\" />&nbsp;<strong><a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a></strong>\n";
		else if(in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs())) //The selected folder is a subfolder of this folder
			echo "<li><img src=\"skins/$skin/img/folder-expanded.png\" alt=\"=\" title=\"Open folder\" style=\"border-style: none\" />&nbsp;<a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a>\n";
		else if(in_array($folder->getID(), $_SESSION['expandFolders'])) //The user wants this folder expanded
			echo "<li><a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."\"><img src=\"skins/$skin/img/folder-expanded.png\" alt=\"-\" title=\"Open folder\" style=\"border-style: none\" /></a>&nbsp;<a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a>\n";
		else
			echo "<li><a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."\"><img src=\"skins/$skin/img/folder.png\" alt=\"+\" title=\"Folder\" style=\"border-style: none\" /></a>&nbsp;<a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a>\n";
		if(count($subfolder) > 0 && (in_array( $folder->getID(), $_SESSION['expandFolders'] ) || in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs()) || $_SESSION['selectedFolder'] == $folder->getID()))
		{
			echo "<ul class=\"folderlist\">\n";
			foreach($subfolder as $key => $val)
				printFolder($val);
			echo "</ul>\n";
		}
		echo "</li>\n";
	}

	function printOptions($group)
	{
		$folder = $group->getGroupFolders();
		foreach($folders as $key => $subfolder)
		{
			echo "<option value=".$subfolder->getID().">+ ".$subfolder->getName()."</option>\n";
			printOptionsRecurse($subfolder, '&nbsp;&nbsp;&nbsp;+ ');
		}
	}

	function printOptionsRecurse($folder, $indent)
	{
		$folders = $folder->getFolders();
		foreach($folders as $key => $subfolder)
		{
			echo "<option value =".$subfolder->getID().">".$indent.$subfolder->getName()."</option>\n";
			printOptionsRecurse($subfolder, '&nbsp;&nbsp;&nbsp;', $indent);
		}
	}

	function canViewFiles($user, $folder)
	{
		 return (!$folder || $folder->getGroupID() == 0 || !$folder->isWriteOnly() || $user->isGroupAdministrator($folder->getGroup()));
	}
	
	//------End Helper Functions------------------------------------//
	//------Start XHTML output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/files.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/files.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Nugget File Transfer</title>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type= "text/javascript">
//<![CDATA[
	function copyCheckBoxes(){
		var folders = new Array();
		var files = new Array();
		var inputs = document.getElementsByTagName('input');
		for(var i = 0; i < inputs.length; i++){
			if(inputs[i].type == "checkbox" && inputs[i].checked){
				values = inputs[i].name.split(/\x5b|\x5d/);
				if(values[0] == 'folder')
					folders.push(values[1]);
				if(values[0] == 'file')
					files.push(values[1]);
			}
		}
		var fileInputs = document.getElementsByName("files");
		for(var i = 0; i < fileInputs.length; i++)
			fileInputs[i].value=files;
		var folderInputs = document.getElementsByName("folders");
		for(var i = 0; i < folderInputs.length; i++)
			folderInputs[i].value=folders;
	}
//]]>
</script></head><body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\"><div id=\"topbanner\">{$currentGroup->getName()}</div>\n";
?>
	<div id="container">
	<div id="folderbox">
	<div class="columnbanner">Your Folders:</div>
	<div id="folders"><ul id="top" class="folderlist">
<?php
	if(!isset($_SESSION['selectedSpecial']) && $_SESSION['selectedFolder'] == 0)
	{
		echo "<li><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"=\" />&nbsp;<strong><a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=0\">Your Files</a></strong>";
		$topFolders = $currentGroup->getGroupFolders();
		if(count($topFolders) > 0)
		{
			echo "<ul class=\"folderlist\">";
			foreach($topFolders as $key=> $val)
				printFolder($val);
			echo "</ul>";
		}
		echo "</li>\n";
	}
	else
		echo "<li><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"=\" /></a>&nbsp;<a href=\"addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=0\">Your Files</a></li>\n";
?>
	</ul>
	</div>
	</div>
	<div id="filebox">
	<div class="columnbanner">
<?php
	if($currentFolder)
	{
		$folderList = $currentFolder->getFolders();
		$fileList = $currentFolder->getFiles();
		echo "<span id=\"boxtitle\">".htmlspecialchars($currentFolder->getName())."</span><br /><span id=\"boxdesc\">".htmlspecialchars($currentFolder->getDesc())."</span>";
	}
	else
	{
		if(isset($_SESSION['selectedSpecial']))
		{
			if($_SESSION['selectedSpecial'] == 'ipro')
			{
				$folderList = $currentGroup->getIPROOfficeFolders();
				echo "<span id=\"boxtitle\">IPRO Office Files</span><br /><span id=\"boxdesc\">Files from the IPRO Office</span>";
			}
		}
		else if($_SESSION['selectedFolder'] == 0)
		{
			$folderList = $currentGroup->getGroupFolders();
			$fileList = $currentGroup->getGroupFiles();
			echo "<span id=\"boxtitle\">Your Files</span><br /><span id=\"boxdesc\">Files uploaded by your group</span>";
		}
		else if($_SESSION['selectedFolder'] == 1)
		{
			$fileList = $currentGroup->getNuggetFiles();
			echo "<span id=\"boxtitle\"Your Nugget Files</span><br /><span id=\"boxdesc\">Files in your group's nuggets</span>";
		}
	}
?>
	</div>
	<form method="post" action = "addFilesToNugget.php"><div id="menubar">
<?php
	if (!$currentUser->isGroupGuest($currentGroup))
	{
?>
		<ul class="folderlist">
		<li><a href="#" onclick="addwin=dhtmlwindow.open('addbox', 'div', 'update', 'Add Files to Nugget', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1')">Add to Nugget</a></li>
<?php
	}
?>
	</ul>
	</div>
	<div id="files">
	<fieldset><table width="100%">
<?php
	if(canViewFiles($currentUser, $currentFolder)
	{
		if($fileList)
		{
			foreach($fileList as $key => $file)
			{
				printTR();
				echo "<td><img src=\"skins/$skin/img/file.png\" alt=\"\" /></td>";
				echo "<td><a href=\"download.php?id=".$file->getID()."\">".$file->getName()."</a></td>";
				if($_SESSION['selectedFolder'] == 1)
				{
					$nuggetName=$file->getNugget()->getType();
					echo "<td>$nuggetName nugget file</td>";
				}
				echo "<td>".$file->getDesc()."</td>";
				$author = $file->getAuthor();
				if($author)
					echo "<td>".$author->getFullName()."</td>";
				else
					echo "<td></td>";
				echo "<td>".$file->getDate()."</td>";
				echo "<td><input type=\"checkbox\" name=\"file[".$file->getID()."]\" /></td>";
				echo "</tr>\n";
			}
		}
		if(count($folderList) + count($fileList) == 0)
			echo "<tr><td>There are no files or folders in the selected folder</td></tr>\n";
	}
	else
		echo "<tr><td>You do not have access to view the files in this folder</td></tr>\n";
?>
	</table></fieldset>
	</div>
	</form>
	</div>
	</div>
	<div class="window-content" id="update" style="display: none">
	<form method="post" action="addFilesToNugget.php" id="moveFile"><fieldset>
	Are you sure you wish to add the selected files to the nugget?
	<input type="hidden" name="folders" /><input type="hidden" name="files" />
<?php
	$nugget = $_GET['nugget'];
	echo "<input type =\"hidden\" name=\"nugget\" value=\"$nugget\" />";
?>
<input type="submit" value="Yes" onclick="copyCheckBoxes();this.form.submit()" />
</fieldset></form></div></div></body></html>
