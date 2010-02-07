<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/folder.php');
	include_once('classes/file.php');
	include_once('classes/quota.php');
	
	//------Begin Special Display Functions-------------------------//
			
	function printFolder($folder)
	{ // Prints tree structure of folders
		$subfolder = $folder->getFolders();
		if($_SESSION['selectedFolder'] == $folder->getID()) //This is the selected folder
			echo "<li><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"=\" title=\"Open folder\" />&nbsp;<strong><a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a></strong>\n";
		else if(in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs())) //The selected folder is a subfolder of this folder
			echo "<li><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"=\" title=\"Open folder\" />&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
		else if(in_array( $folder->getID(), $_SESSION['expandFolders'] )) //The user wants this folder expanded
			echo "<li><a href=\"files.php?toggleExpand=".$folder->getID()."\"><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
		else
			echo "<li><a href=\"files.php?toggleExpand=".$folder->getID()."\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
		if(count($subfolder) > 0 && (in_array($folder->getID(), $_SESSION['expandFolders']) || in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs()) || $_SESSION['selectedFolder'] == $folder->getID()))
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
		$folders = $group->getGroupFolders();
		foreach($folders as $key => $subfolder)
		{
			echo "<option value=\"".$subfolder->getID()."\">+ ".$subfolder->getName()."</option>\n";
			printOptionsRecurse($subfolder, '&nbsp;&nbsp;&nbsp;+ ');
		}
	}
	
	function printOptionsRecurse($folder, $indent)
	{
		$folders = $folder->getFolders();
		foreach($folders as $key => $subfolder)
		{
			echo "<option value=\"".$subfolder->getID()."\">".$indent.$subfolder->getName()."</option>\n";
			printOptionsRecurse( $subfolder, "&nbsp;&nbsp;&nbsp;".$indent );
		}
	}
	
	function canViewFiles($user, $folder)
	{
		if(!$folder)
			return true;
		if($folder->getGroupID() == 0)
			return true;
		if(!$folder->isWriteOnly())
			return true;
		if($user->isGroupAdministrator($folder->getGroup()))
			return true;
		return false;
	}
	
	//----End Display Functions-------------------------------------//
	//------Start of Code for Form Processing-----------------------//

	if(isset($_POST['upload']))
	{
		if($_FILES['thefile']['error'] == UPLOAD_ERR_OK)
		{
			$file = createFile( $_POST['filename'], $_POST['filedescription'], $currentUser->getID(), $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $_FILES['thefile']['tmp_name'], $_FILES['thefile']['type'], 1, $db );
			if(is_object($file))
					$message = 'File successfully uploaded';
				else if($file == 1)
					$message = 'Error: The files directory is full. The IPRO Office has been notified of this problem.';
				else
					$message = 'Error saving file. Please try again.';
		}
		else
			$message = "Error occured during upload, please try again";
	}

	if(isset($_POST['delete']))
	{
		if(isset($_POST['file']))
		{
			foreach($_POST['file'] as $fileid => $val)
			{
				$file = new File( $fileid, $db );
				if($currentUser->getID() == $file->getAuthorID())
					$file->delete();
			}
		}
		$message = (isset($_POST['file']) ? 'Selected items successfully deleted' : 'Please select file(s) or folder(s) to delete first.');
	}
	
	//------End Form Processing Code--------------------------------//
	
	if(isset($_GET['sort']) && is_numeric($_GET['sort']))
		$_SESSION['fileSort'] = $_GET['sort'];
	
	if(!isset($_SESSION['fileSort']))
		$_SESSION['fileSort'] = 1;
	
	//------Start XHTML Output--------------------------------------//
		
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/files.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/files.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Group Files</title>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript">
//<![CDATA[
	function copyCheckBoxes() {
		var folders = new Array();
		var files = new Array();
		var inputs = document.getElementsByTagName('input');
		for(var i = 0; i < inputs.length; i++)
		{
			if(inputs[i].type == "checkbox" && inputs[i].checked)
			{
				values = inputs[i].name.split( /\x5b|\x5d/ );
				if(values[0] == "folder")
					folders.push(values[1]);
				if(values[0] == "file")
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
</script>
</head>
<body>
<?php
	require('htmlhead.php');
	echo "<div id=\"container\"><div id=\"folderbox\">\n";
	echo "<div class=\"columnbanner\">Your Folders:</div>\n";
	echo "<div id=\"folders\">\n";
	echo "<ul id=\"top\" class=\"folderlist\">\n";
	echo "\t<li><img src=\"skins/$skin/img/folder.png\" alt=\"=\" title=\"Folder\" />&nbsp;<a href=\"files.php?selectFolder=0\">Your Files</a></li>\n";
	echo "\t<li><a href=\"files.php?selectSpecial=obsolete\"><img src=\"skins/$skin/img/folder.png\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"files.php?selectSpecial=obsolete\">Past Versions</a></li>\n";
	echo "\t<li><a href=\"files.php?selectSpecial=trash\"><img src=\"skins/$skin/img/folder.png\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"files.php?selectSpecial=trash\">Trash Bin</a></li>\n";
	echo "\t<li><a href=\"files.php?toggleExpand=iprofiles\"><img src=\"skins/$skin/img/folder.png\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"files.php?selectSpecial=ipro\">IPRO Office Files</a></li>\n";
	echo "\t<li><img src=\"skins/$skin/img/folder-expanded.png\" alt=\"-\" title=\"Open folder\" />&nbsp;<strong><a href=\"dropbox.php\">Secure Dropbox</a></strong></li>\n</ul></div></div>\n";
	
	echo "<div id=\"filebox\"><div class=\"columnbanner\">\n";
	echo "<span id=\"boxtitle\">My Secure Dropbox</span><br /><span id=\"boxdesc\">Files in your dropbox can only be viewed by you and your instructor(s)</span>";
?>			
	</div>
	<form method="post" action="dropbox.php">
		<div id="menubar">
<?php
	if (!$currentUser->isGroupGuest($currentGroup))
	{
?>
		<ul class="folderlist">
		<li><a href="#" onclick="uploadwin=dhtmlwindow.open('uploadbox', 'div', 'upload', 'Upload File', 'width=350px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); return false">Add File</a></li>
		<li><a href="#" onclick="document.getElementById('delete').form.submit()">Delete File</a><input type="hidden" id="delete" name="delete" value="delete" /></li>
		</ul>
<?php
	}
	echo "</div>\n";
	if ($currentUser->isGroupAdministrator($currentGroup))
		$files = $currentGroup->getDropboxSortedBy($_SESSION['fileSort']);
	else
		$files = $currentGroup->getUserDropboxSortedBy($currentUser->getID(), $_SESSION['fileSort']);

	echo '<div id="files"><table width="100%">';
	echo "<tr class=\"sortbar\"><td></td>\n";
	if($_SESSION['fileSort'] == 1)
		echo "<td><a href=\"dropbox.php?sort=-1\" title=\"Sort this descendingly\">Filename &#x2193;</a></td>";
	else if($_SESSION['fileSort'] == -1)
		echo "<td><a href=\"dropbox.php?sort=1\" title=\"Sort this ascendingly\">Filename &#x2191;</a></td>";
	else
		echo "<td><a href=\"dropbox.php?sort=1\" title=\"Sort by filename\">Filename</a></td>";
	if($_SESSION['fileSort'] == 2)
		echo "<td><a href=\"dropbox.php?sort=-2\" title=\"Sort this descendingly\">Description &#x2193;</a></td>";
	else if($_SESSION['fileSort'] == -2)
		echo "<td><a href=\"dropbox.php?sort=2\" title=\"Sort this ascendingly\">Description &#x2191;</a></td>";
	else
		echo "<td><a href=\"dropbox.php?sort=2\" title=\"Sort by description\">Description</a></td>";
	if($_SESSION['fileSort'] == 3)
		echo "<td><a href=\"dropbox.php?sort=-3\" title=\"Sort this descendingly\">Author &#x2193;</a></td>";
	else if($_SESSION['fileSort'] == -3)
		echo "<td><a href=\"dropbox.php?sort=3\" title=\"Sort this ascendingly\">Author &#x2191;</a></td>";
	else
		echo "<td><a href=\"dropbox.php?sort=3\" title=\"Sort by author\">Author</a></td>";
	if($_SESSION['fileSort'] == 4)
		echo "<td><a href=\"dropbox.php?sort=-4\" title=\"Sort this descendingly\">Date &#x2193;</a></td>";
	else if($_SESSION['fileSort'] == -4)
		echo "<td><a href=\"dropbox.php?sort=4\" title=\"Sort this ascendingly\">Date &#x2191;</a></td>";
	else
		echo "<td><a href=\"dropbox.php?sort=-4\" title=\"Sort by date\">Date</a></td>";
	echo "<td></td><td></td></tr>\n";
	foreach ($files as $file)
	{
		printTR();
		echo "<td><img src=\"skins/$skin/img/file.png\" alt=\"File\" title=\"File\" /></td>";
		echo "<td><a href=\"download.php?id=".$file->getID()."\">".$file->getName()."</a></td>";
		echo "<td>".$file->getDesc()."</td>";
		$author = $file->getAuthor();
		if ( $author )
			echo "<td>".$author->getFullName()."</td>";
		else
			echo "<td></td>";
		echo "<td>".$file->getDateTime()."</td>";
		echo "<td align=\"right\"><input type=\"checkbox\" name=\"file[".$file->getID()."]\" /></td>";
		echo "</tr>\n";
	}
	if(count($files) == 0)
		echo "<tr><td colspan=\"6\">There are no files in your dropbox.</td></tr>\n";
	echo '</table></div>';
?>
			</form>
		</div>
	</div>
		<div class="window-content" id="upload" style="display:none">
			<form method="post" action="dropbox.php" enctype="multipart/form-data"><fieldset>
				<label for="thefile">File:</label><input type="file" name="thefile" id="thefile" /><br />
				<label for="filename">File Name:</label><input type="text" name="filename" id="filename" /><br />
				<label for="filedescription">Description:</label><input type="text" name="filedescription" id="filedescription" /><br />
				<input type="submit" name="upload" value="Upload File" />
			</fieldset></form>
		</div>
	
 <?php
  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>
</body></html>
