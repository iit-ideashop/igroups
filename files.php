<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/folder.php" );
	include_once( "classes/file.php" );
	include_once( "classes/quota.php" );
		
	$currentQuota = new Quota( $currentGroup, $db );
	
	if ( isset( $_GET['selectFolder'] ) ) {
		unset( $_SESSION['selectedSpecial'] );
		$_SESSION['selectedFolder'] = $_GET['selectFolder'];
	}
	
	if ( isset( $_GET['selectSpecial'] ) ) {
		unset( $_SESSION['selectedFolder'] );
		$_SESSION['selectedSpecial'] = $_GET['selectSpecial'];
	}
	
	if ( isset( $_SESSION['selectedFolder'] ) && $_SESSION['selectedFolder'] != 0 ){
		$currentFolder = new Folder( $_SESSION['selectedFolder'], $db );
	}
	else
		$currentFolder = false;
	
	if ( !isset( $_SESSION['expandFolders'] ) )
		$_SESSION['expandFolders'] = array();
	
	if ( isset( $_GET['toggleExpand'] ) ) {
		if ( in_array( $_GET['toggleExpand'], $_SESSION['expandFolders'] ) ) {
			$temp = array( $_GET['toggleExpand'] );
			$_SESSION['expandFolders'] = array_diff( $_SESSION['expandFolders'], $temp );
		}
		else
			$_SESSION['expandFolders'][] = $_GET['toggleExpand'];	
	}
	
	//------Begin Special Display Functions--------------------------//
	
	function printFolder( $folder ) {
	// Prints tree structure of folders
		$subfolder = $folder->getFolders();
		if ( $_SESSION['selectedFolder'] == $folder->getID()) //This is the selected folder
			print "<li><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"=\" title=\"Open folder\" />&nbsp;<strong><a href=\"files.php?selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a></strong>\n";
		else if(in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs())) //The selected folder is a subfolder of this folder
			print "<li><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"=\" title=\"Open folder\" />&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a>\n";
		else if(in_array( $folder->getID(), $_SESSION['expandFolders'] )) //The user wants this folder expanded
			print "<li><a href=\"files.php?toggleExpand=".$folder->getID()."\"><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a>\n";
		else
			print "<li><a href=\"files.php?toggleExpand=".$folder->getID()."\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".htmlspecialchars($folder->getName())."</a>\n";
		if ( count($subfolder) > 0 && (in_array( $folder->getID(), $_SESSION['expandFolders'] ) || in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs()) || $_SESSION['selectedFolder'] == $folder->getID())) {
			print "<ul class=\"folderlist\">\n";
			foreach ( $subfolder as $key => $val ) {
				printFolder( $val );
			}
			print "</ul>\n";
		}
		print "</li>\n";
	}
	
	function printOptions( $group ) {
		$folders = $group->getGroupFolders();
		foreach ( $folders as $key => $subfolder ) {
			print "<option value=\"".$subfolder->getID()."\">+ ".$subfolder->getName()."</option>\n";
			printOptionsRecurse( $subfolder, "&nbsp;&nbsp;&nbsp;+ " );
		}
	}
	
	function printOptionsRecurse( $folder, $indent ) {
		$folders = $folder->getFolders();
		foreach ( $folders as $key => $subfolder ) {
			print "<option value=\"".$subfolder->getID()."\">".$indent.$subfolder->getName()."</option>\n";
			printOptionsRecurse( $subfolder, "&nbsp;&nbsp;&nbsp;".$indent );
		}
	}
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}
	
	function canViewFiles( $user, $folder ) {
		if ( !$folder )
			return true;
		if ( $folder->getGroupID() == 0 )
			return true;
		if ( !$folder->isWriteOnly() )
			return true;
		if ( $user->isGroupAdministrator( $folder->getGroup() ) )
			return true;
		return false;
	}
	
	//----End Display Functions-------------------------------------//
	
	if(isset($_GET['sort']) && is_numeric($_GET['sort']))
		$_SESSION['fileSort'] = $_GET['sort'];
	
	if(!isset($_SESSION['fileSort']))
		$_SESSION['fileSort'] = 1;
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Group Files</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/files.css\" type=\"text/css\" />\n";
echo "<link rel=\"stylesheet\" href=\"skins/$skin/dhtmlwindow.css\" type=\"text/css\" />\n";
?>
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
			for ( var i=0; i < inputs.length; i++ ) {
				if ( inputs[i].type == "checkbox" && inputs[i].checked ) {
					values = inputs[i].name.split( /\x5b|\x5d/ );
					if ( values[0] == "folder" )
						folders.push( values[1] );
					if ( values[0] == "file" )
						files.push( values[1] );
				}
			}
			var fileInputs = document.getElementsByName( "files" );
			for ( var i=0; i < fileInputs.length; i++ )
				fileInputs[i].value=files;
			var folderInputs = document.getElementsByName( "folders" );
			for ( var i=0; i < folderInputs.length; i++ )
				folderInputs[i].value=folders;
		}
	//]]>
	</script>
</head>
<body>
<?php
	//------Start of Code for Form Processing-------------------------//
	
	if ( isset( $_POST['create'] ) ) {
		if ( $currentFolder ) {
			if ( $currentFolder->getGroupID() == $currentGroup->getID() )
				$pfid = $currentFolder->getID();
			else 
				$pfid = 0;
		}
		else
			$pfid = 0;
		createFolder( $_POST['foldername'], $_POST['folderdescription'], $_POST['status'], $pfid, $currentGroup, $db );
		$message = "Folder successfully created";
	}
	
	if ( isset( $_POST['upload'] ) ) {
		// Get target folder ID
		if ( $currentFolder ) {
			if ( $currentFolder->getGroupID() == $currentGroup->getID() )
				$fid = $currentFolder->getID();
			else 
				$fid = 0;
		}
		else
			$fid = 0;
			
		// Load Quota information	
		if ( !$currentQuota ) {
			$currentQuota = createQuota( $currentGroup, $db );
		}
		
		if ( $_FILES['thefile']['error'] == UPLOAD_ERR_OK ) {
			if ( $currentQuota->checkSpace( filesize( $_FILES['thefile']['tmp_name'] ) ) ) {
				$currentQuota->increaseUsed( filesize( $_FILES['thefile']['tmp_name'] ) );
				$currentQuota->updateDB();
				if(isset($_POST['private']) && $_POST['private'])
				{
					$priv = 1;
					$fid = -1;
				}
				else
					$priv = 0;
				$file = createFile( $_POST['filename'], $_POST['filedescription'], $fid, $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $_FILES['thefile']['tmp_name'], $_FILES['thefile']['type'], $priv, $db );
				if(!$file)
					$message = "Error saving file. Please try again.";
				else
					$message = "File successfully uploaded";
			}
			else {
				$currentQuota->sendWarning(1);
				$message = "ERROR: Not enough space for file";
			}
		}
		else 
			$message = "Error occured during upload, please try again";
	}
	
	if ( isset( $_POST['fupdate'] ) ) {
		if ( ($_POST['files'] != "") && (count($_POST['files']) == 1))
			$update = true;
		else
			$update = false;

		if ($update) {
		$oldFile = new File($_POST['files'], $db);
		if ($oldFile->isIPROFile())
			$update = false;		
		}

		if ($update) {
		// Get target folder ID
		if ( $currentFolder ) {
			if ( $currentFolder->getGroupID() == $currentGroup->getID() )
				$fid = $currentFolder->getID();
			else
				$fid = 0;
		}
		else
			$fid = 0;
		// Load Quota information
		if ( !$currentQuota ) {
			$currentQuota = createQuota( $currentGroup, $db );
		}
		if ( $_FILES['thefile']['error'] == UPLOAD_ERR_OK ) {
			if ( $currentQuota->checkSpace( filesize( $_FILES['thefile']['tmp_name'] ) ) ) {
				$currentQuota->increaseUsed( filesize( $_FILES['thefile']['tmp_name'] ) );
				$currentQuota->updateDB();
				$oldFile = new File($_POST['files'], $db);
				$oldFile->makeObsolete();
				$oldFile->updateDB();
				$file = createFile( $oldFile->getNameNoVer(), $_POST['filedescription'], $fid, $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $_FILES['thefile']['tmp_name'], $_FILES['thefile']['type'], $oldFile->isPrivate(), $db );
				if(!$file)
					$message = "Error saving file. Please try again.";
				else
				{
					$file->setVersion($oldFile->getVersion() + 1);
					$file->updateDB();
					$message = "File successfully uploaded";
				}
			}
			else {
				$currentQuota->sendWarning(1);
				$message = "ERROR: Not enough space for file";
			}
		}
		else
			$message = "ERROR: Could not update file";
		}
		else
			$message = "ERROR: Could not update file. Make sure you selected a file to update.";
	}

	if(isset($_POST['editF']) && !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']!=0 && $currentUser->isGroupModerator($currentFolder->getGroup()))
	{
		if (isset($_POST['foldername']) && !$currentFolder->isIPROFolder())
			$currentFolder->setName( $_POST['foldername'] );
		if (isset($_POST['folderdesc']) && !$currentFolder->isIPROFolder())
			$currentFolder->setDesc( $_POST['folderdesc'] );
		$currentFolder->updateDB();
		$message = "Folder successfully edited";
	}
	else if(isset($_POST['deleteF']) && !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']!=0 && $currentUser->isGroupModerator($currentFolder->getGroup()))
	{
		$currentFolder->trash();
		$_SESSION['selectedFolder'] = 0;
		$currentFolder = false;
		$message = "Folder successfully deleted";
	}
	else if(isset($_POST['editF']) || isset($_POST['deleteF']))
		$message = "Error: Folder operation failed";
	if ( isset( $_POST['move'] ) ) {
		// Change form data into arrays instead of comma separated list
		if ( $_POST['files'] != "" )
			$_POST['files'] = explode( ",", $_POST['files'] );
		if ( $_POST['folders'] != "" )
			$_POST['folders'] = explode( ",", $_POST['folders'] );
		else
			$_POST['folders'] = array();
	
		// Move Files First
		if ( $_POST['files'] != "") {
		foreach( $_POST['files'] as $key => $val ) {
			$file = new File( $val, $db );
			if (!$file->isIPROFile()) {
			$file->setFolderID( $_POST['target'] );
			$file->takeFromTrash();
			$file->updateDB();
			}
		}
		}
		
		// Then check to see if moving any of the folders creates a cycle in the tree
		$doMove = true;
		$tmpFolder = new Folder( $_POST['target'], $db );
		while ( $tmpFolder ) {
			if ( in_array( $tmpFolder->getID(), $_POST['folders'] ) ) {
				$doMove = false;
			}
			$tmpFolder = $tmpFolder->getParentFolder();
		}
		
		// If not, move the folders
		if ( $doMove ) {
			foreach( $_POST['folders'] as $key => $val ) {
				$folder = new Folder( $val, $db );
				if (!$folder->isIPROFolder())
				$folder->setParentFolderID( $_POST['target'] );
				$folder->updateDB();
			}		
			$message = "Selected items successfully moved";
		}
		else
			$message = "Some elements could not be moved";
	}

	if ( isset( $_POST['rename'] ) && (isset($_POST['file']) XOR isset($_POST['folder'])) ) {	 
		if (isset($_POST['file'])) {
		 	$file = new File($_POST['file'], $db);
			if (isset($_POST['newname']) && !$file->isIPROFile())
				$file->setName( $_POST['newname'] );
			if (isset($_POST['newdesc']) && !$file->isIPROFile())
				$file->setDesc( $_POST['newdesc'] );
			$file->updateDB();
		}
		else {
			$folder = new Folder($_POST['folder'], $db);
			if (isset($_POST['newname']) && !$folder->isIPROFolder())
				$folder->setName( $_POST['newname'] );
			if (isset($_POST['newdesc']) && !$folder->isIPROFolder())
				$folder->setDesc( $_POST['newdesc'] );
			$folder->updateDB();
		}		 
		$message = "File or folder renamed";
	}
	else if( isset( $_POST['rename'] ))
		$message = "Unable to rename. Make sure one file or one folder is selected.";

	if ( isset( $_POST['delete'] ) ) {
		if ( isset( $_POST['folder'] )) {
		foreach( $_POST['folder'] as $folderid => $val ) {
			$folder = new Folder( $folderid, $db );
			if ( $currentUser->isGroupModerator( $folder->getGroup() ) ) {
				$folder->trash();
			}
		}
		}
		
		if ( isset( $_POST['file'] )) {
		foreach( $_POST['file'] as $fileid => $val ) {
			$file = new File( $fileid, $db );
			if ( $file->isInTrash() ) {
				if ( $currentUser->isGroupModerator( $file->getGroup() ) )
					$file->delete();
			}
			else {
				if ( $currentUser->isGroupModerator( $file->getGroup() ) || $currentUser->getID()==$file->getAuthorID() ) {
					$file->moveToTrash();
					$file->updateDB();
				}
			}
		}
		}	
		if ( isset( $_POST['folder'] ) || isset( $_POST['file'] ))
			$message = "Selected items successfully deleted";
		else
			$message = "Please select file(s) or folder(s) to delete first.";
	}
	
	//------End Form Processing Code---------------------------------//
	require("sidebar.php");
?>
	<div id="content"><div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
	<div id="container">
		<div id="folderbox">
			<div class="columnbanner">
				Your Folders:
			</div>
			<div class="menubar">
				<ul class="folderlist"> <?php if (!$currentUser->isGroupGuest($currentGroup) && !isset($_SESSION['selectedSpecial'])) { ?>
					<li><a href="#" onclick="newfolderwin=dhtmlwindow.open('newfolderbox', 'div', 'newfolder', 'Create Folder', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Create Folder</a></li>
					<?php
					if ( $currentUser->isGroupModerator($currentGroup) && !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']!=0  ) {
					?>
						<li><a href="#" onclick="editfolderwin=dhtmlwindow.open('editfolderbox', 'div', 'editfolder', 'Edit Folder', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Edit/Delete Folder</a></li>
					<?php } ?>
				</ul>
				<?php } ?>
			</div>
			<div id="folders">
				<ul id="top" class="folderlist">
<?php
					if ( !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']==0 )
						print '<li><img src="skins/'.$skin.'/img/folder-expanded.png" style="border-style: none" alt="=" title="Open folder" />&nbsp;<strong><a href="files.php?selectFolder=0">Your Files</a></strong>';
					else
						print '<li><img src="skins/'.$skin.'/img/folder-expanded.png" style="border-style: none" alt="=" title="Open folder" />&nbsp;<a href="files.php?selectFolder=0">Your Files</a>';
					
						$topFolders = $currentGroup->getGroupFolders();
						if(count($topFolders) > 0) {
							print "<ul class=\"folderlist\">";
							
							foreach ( $topFolders as $key => $val ) {
								printFolder( $val );
							}
						print "</ul>";
						}
					print "</li>";

					if ( $_SESSION['selectedSpecial'] == 'obsolete' )
							print '<li><a href="files.php?selectSpecial=obsolete"><img src="skins/'.$skin.'/img/folder-expanded.png" style="border-style: none" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectSpecial=obsolete">Past Versions</a></strong></li>';
						else
							print '<li><a href="files.php?selectSpecial=obsolete"><img src="skins/'.$skin.'/img/folder.png" style="border-style: none" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=obsolete">Past Versions</a></li>';
					if ( $_SESSION['selectedSpecial'] == 'trash' )
							print '<li><a href="files.php?selectSpecial=trash"><img src="skins/'.$skin.'/img/folder-expanded.png" style="border-style: none" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectSpecial=trash">Trash Bin</a></strong</li>';
						else
							print '<li><a href="files.php?selectSpecial=trash"><img src="skins/'.$skin.'/img/folder.png" style="border-style: none" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=trash">Trash Bin</a></li>';
					

					if ( $currentGroup->getType() == 0 ) {
						if ( $_SESSION['selectedSpecial'] == 'ipro' )
							print '<li><a href="files.php?toggleExpand=iprofiles"><img src="skins/'.$skin.'/img/folder-expanded.png" style="border-style: none" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectSpecial=ipro">IPRO Office Files</a></strong>';
						else
							print '<li><a href="files.php?toggleExpand=iprofiles"><img src="skins/'.$skin.'/img/folder.png" style="border-style: none" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=ipro">IPRO Office Files</a>';

						if ( in_array( "iprofiles", $_SESSION['expandFolders'] ) ) {
?>
							<ul class="folderlist">
<?php
								$topFolders = $currentGroup->getIPROOfficeFolders();
								foreach ( $topFolders as $key => $val ) {
									printFolder( $val );
								}
?>
							</ul>
<?php
						}
?>
					</li>
<?php
					}
?>
				<li><img src="skins/<?php echo $skin.; ?>img/folder.png" style="border-style: none" alt="+" title="Folder" />&nbsp;<a href="dropbox.php">Secure Dropbox</a></li>
				</ul>
			</div>
		</div>
		<div id="filebox">
			<div class="columnbanner">
<?php
				if ( $currentFolder ) {
					$folderList = $currentFolder->getFolders();
					$fileList = $currentFolder->getFilesSortedBy($_SESSION['fileSort']);
					print "<span id=\"boxtitle\">".htmlspecialchars($currentFolder->getName())."</span><br /><span id=\"boxdesc\">".htmlspecialchars($currentFolder->getDesc())."</span>";
				}
				else {
					if ( isset( $_SESSION['selectedSpecial'] ) ) {
						if ( $_SESSION['selectedSpecial'] == 'trash' ) {
							$fileList = $currentGroup->getGroupTrashBinSortedBy($_SESSION['fileSort']);
							print "<span id=\"boxtitle\">Trash</span><br /><span id=\"boxdesc\">Files that have been deleted</span>";
						}
						else if ($_SESSION['selectedSpecial'] == 'obsolete') {
							$fileList = $currentGroup->getGroupObsoleteSortedBy($_SESSION['fileSort']);
							print "<span id=\"boxtitle\">Past Versions</span><br /><span id=\"boxdesc\">Older versions of files</span>";
						}
						else if ( $_SESSION['selectedSpecial'] == 'ipro' ) {
							$folderList = $currentGroup->getIPROOfficeFolders();
							print "<span id=\"boxtitle\">IPRO Office Files</span><br /><span id=\"boxdesc\">Files from the IPRO Office</span>";
						}
					}
					else {
						$folderList = $currentGroup->getGroupFolders();
						$fileList = $currentGroup->getGroupFilesSortedBy($_SESSION['fileSort']);
						print "<span id=\"boxtitle\">Your Files</span><br /><span id=\"boxdesc\">Files uploaded by your group</span>";
					}
				}
?>			
			</div>
			<form method="post" action="files.php"><fieldset>
				<div class="menubar">
					<?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
					<ul class="folderlist">
						<?php if ($_SESSION['selectedSpecial'] != 'obsolete' && $_SESSION['selectedSpecial'] != 'ipro') { if ( $currentFolder == 0 || (is_object($currentFolder) && !$currentFolder->isIPROFolder())) { ?>
						<li><a href="#" onclick="uploadwin=dhtmlwindow.open('uploadbox', 'div', 'upload', 'Upload File', 'width=350px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); return false">Upload File</a></li>
<?php
					if(count($fileList) > 0) {
?>
						<li><a href="#" onclick="updatewin=dhtmlwindow.open('updatebox', 'div', 'update', 'Update File', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Update File</a></li>
<?php
					}
					if(count($fileList) > 0 && (($currentFolder == 0 && count($folderList) > 0) || (isset($_SESSION['selectedSpecial']) && $_SESSION['selectedSpecial'] == 'trash') || (!isset($_SESSION['selectedSpecial']) && $currentFolder != 0))) {
?>
						<li><a href="#" onclick="movewin=dhtmlwindow.open('movebox', 'div', 'move', 'Move', 'width=350px,height=100px,left=300px,top=100px,resize=0,scrolling=0'); return false">Move Selected</a></li>
						<?php } } } if ( count($fileList) > 0 && ($currentFolder == 0 && $_SESSION['selectedSpecial'] != 'ipro' || (is_object($currentFolder) && !$currentFolder->isIPROFolder()))) { ?>

						<li><a href="#" onclick="document.getElementById('delete').form.submit()">Delete Selected</a>
						<?php } ?>
						<input type="hidden" id="delete" name="delete" value="delete" /></li>
					</ul>
					<?php } ?>
				</div>
				<div id="files">
					<table width="100%">
<?php					if($_SESSION['selectedSpecial'] != 'ipro') {
						echo "<tr class=\"sortbar\"><td></td>\n";
						if($_SESSION['fileSort'] == 1)
							echo "<td><a href=\"files.php?sort=-1\" title=\"Sort this descendingly\">Filename <img src=\"skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
						else if($_SESSION['fileSort'] == -1)
							echo "<td><a href=\"files.php?sort=1\" title=\"Sort this ascendingly\">Filename <img src=\"skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
						else
							echo "<td><a href=\"files.php?sort=1\" title=\"Sort by filename\">Filename</a>";
						if($_SESSION['fileSort'] == 2)
							echo "<td><a href=\"files.php?sort=-2\" title=\"Sort this descendingly\">Description <img src=\"skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
						else if($_SESSION['fileSort'] == -2)
							echo "<td><a href=\"files.php?sort=2\" title=\"Sort this ascendingly\">Description <img src=\"skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
						else
							echo "<td><a href=\"files.php?sort=2\" title=\"Sort by description\">Description</a>";
						if($_SESSION['fileSort'] == 3)
							echo "<td><a href=\"files.php?sort=-3\" title=\"Sort this descendingly\">Author <img src=\"skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
						else if($_SESSION['fileSort'] == -3)
							echo "<td><a href=\"files.php?sort=3\" title=\"Sort this ascendingly\">Author <img src=\"skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
						else
							echo "<td><a href=\"files.php?sort=3\" title=\"Sort by author\">Author</a>";
						if($_SESSION['fileSort'] == 4)
							echo "<td><a href=\"files.php?sort=-4\" title=\"Sort this descendingly\">Date <img src=\"skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
						else if($_SESSION['fileSort'] == -4)
							echo "<td><a href=\"files.php?sort=4\" title=\"Sort this ascendingly\">Date <img src=\"skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
						else
							echo "<td><a href=\"files.php?sort=-4\" title=\"Sort by date\">Date</a>";
						echo "<td></td><td></td></tr>\n"; 
					}
					if ($currentFolder && !$currentFolder->isIPROFolder()) {
							printTR();
							print "<td style=\"width: 24px\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></td>";
							print "<td align=\"left\" colspan=\"5\"><a href=\"files.php?selectFolder=".$currentFolder->getParentFolderID()."\">..</a></td>";
							print "</tr>\n";
					}
					if ($folderList && ($_SESSION['selectedSpecial'] == 'ipro' || ($currentFolder && $currentFolder->isIPROFolder()))) {
						foreach ( $folderList as $key => $folder ) {
							printTR();
							print "<td><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></td>";
							print "<td><a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a></td>";
							print "<td colspan=\"3\">".$folder->getDesc()."</td>";
							print "</tr>\n";
						}
					}
					if($_SESSION['selectedSpecial'] == 'ipro')
					{
						printTR();
						echo "<td><img src=\"skins/$skin/img/globe.png\" style=\"border-style: none\" alt=\"Link\" title=\"External Link\" /></td><td><a href=\"http://ipro.iit.edu/home/index.php?id=163\" onclick=\"window.open(this.href); return false\" onkeypress=\"window.open(this.href); return false;\">IPRO Deliverables</a></td><td>Guidelines for the IPRO deliverables</td></tr>\n";
					}	
					if ( canViewFiles( $currentUser, $currentFolder ) ) {
					if ($fileList) //Prevents an error from PHP 4 to PHP 5 switch
						foreach ( $fileList as $key => $file ) {
							printTR();
							print "<td><img src=\"skins/$skin/img/file.png\" alt=\"File\" title=\"File\" /></td>";
							print "<td><a href=\"download.php?id=".$file->getID()."\">".htmlspecialchars($file->getName())."</a></td>";
							print "<td>".htmlspecialchars($file->getDesc())."</td>";
							$author = $file->getAuthor();
							if ( $author )
								print "<td>".$author->getFullName()."</td>";
							else
								print "<td></td>";
							print "<td>".$file->getDate()."</td>";
							print "<td><input type=\"checkbox\" name=\"file[".$file->getID()."]\" /></td>";
							print "<td><a href=\"#\" onclick=\"renamewin=dhtmlwindow.open('renamebox', 'ajax', 'renamefile.php?fileid=".$file->getID()."', 'Rename File', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Rename</a></td>";
							print "</tr>\n";
						}
							
						if ( count( $folderList ) + count( $fileList ) == 0 )
							print "<tr><td colspan=\"6\">There are no files or folders in the selected folder.</td></tr>\n";
						else if(count($fileList) == 0)
							print "<tr><td colspan=\"6\">There are no files in the selected folder.</td></tr>\n";
					}
					else
						print "<tr><td>You do not have access to view the files in this folder.</td></tr>\n";
?>
					</table>
				</div>
			</fieldset></form>
		</div>
	</div>
		<div class="window-content" id="upload" style="display: none">
			<form method="post" action="files.php" enctype="multipart/form-data"><fieldset>
				<label for="thefile1">File:</label><input type="file" name="thefile" id="thefile1" /><br />
				<label for="filename">File Name:</label><input type="text" name="filename" id="filename" /><br />
				<label for="filedescription1">Description:</label><input type="text" name="filedescription" id="filedescription1" /><br />
				This file will be placed in the
<?php
				if ( $currentFolder )
					print htmlspecialchars($currentFolder->getName());
				else
					print "Your Files"
?>
				folder.<br />- or -<br />
				<input type="checkbox" name="private" id="private" />&nbsp;<label for="private">Send to Dropbox (viewable only by instructor)</label><br />
				<input type="submit" name="upload" value="Upload File" />
			</fieldset></form>
		</div>
<?php
	if(count($fileList) > 0) {
?>
		<div class="window-content" id="update" style="display: none">
			<form method="post" action="files.php" enctype="multipart/form-data"><fieldset>
				<label for="thefile2">File:</label><input type="file" name="thefile" id="thefile2" /><br />
       				<label for="filedescription2">Description:</label><input type="text" name="filedescription" id="filedescription2" /><br />
				This file will be placed in the
<?php
				if ( $currentFolder )
					print htmlspecialchars($currentFolder->getName());
				else
					print "Your Files"
?>
				folder.<br />

				<input type="hidden" name="folders" />
				<input type="hidden" name="files" />
				<input type="hidden" name="fupdate" value="fupdate" />
				<input type="submit" value="Upload File" onclick="copyCheckBoxes();this.form.submit()" />
			</fieldset></form>
		</div>
<?php
		if(($currentFolder == 0 && count($folderList) > 0) || (isset($_SESSION['selectedSpecial']) && $_SESSION['selectedSpecial'] == 'trash') || (!isset($_SESSION['selectedSpecial']) && $currentFolder != 0)) {
?>
		<div class="window-content" id="move" style="display: none">
			<form method="post" action="files.php"><fieldset>
				<label for="target">Select Target Folder:</label>
				<select name="target" id="target">
					<option value="0">Your Files</option>
<?php
					printOptions( $currentGroup );
?>
				</select>
				<input type="hidden" name="folders" />
				<input type="hidden" name="files" />
				<input type="hidden" name="move" value="move" /><br />
				<input type="button" value="Move Files and Folders" onclick="copyCheckBoxes();this.form.submit()" />
			</fieldset></form>
		</div>
<?php
	} }
	if (!$currentUser->isGroupGuest($currentGroup) && !isset($_SESSION['selectedSpecial'])) {
?>
		<div class="window-content" id="newfolder" style="display: none">
			<form method="post" action="files.php"><fieldset>
				<label for="Nfoldername">Folder Name:</label><input type="text" name="foldername" id="Nfoldername" /><br />
				<label for="Nfolderdesc">Description:</label><input type="text" name="folderdescription" id="Nfolderdesc" /><br />
				This folder will be placed in the
<?php
				if ( $currentFolder )
					print htmlspecialchars($currentFolder->getName());
				else
					print "Your Files"
?>
				folder.<br />
<?php
				if ( $currentUser->isGroupAdministrator( $currentGroup ) ) {
?>
					<input type="radio" name="status" value="0" checked="checked" id="status0" /><label for="status0">Normal Folder</label>  <input type="radio" name="status" value="1" id="status1" /><label for="status1">Write Only</label><br />
<?php
				}
				else
					print "<input type=\"hidden\" name=\"status\" value=\"0\" />";
?>
				<input type="submit" name="create" value="Create Folder" />
			</fieldset></form>
		</div>
<?php
	}
	if ( $currentUser->isGroupModerator($currentGroup) && !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']!=0  ) {
?>
		<div class="window-content" id="editfolder" style="display: none">
			<form method="post" action="files.php"><fieldset>
<?php
				if ( $currentFolder ) {
					print "Current Folder Name: ".$currentFolder->getName()."<br />";
					print "<label for=\"Efoldername\">New Folder Name:</label><input type=\"text\" name=\"foldername\" id=\"Efoldername\" value=\"".$currentFolder->getName()."\" /><br />";
					print "<label for=\"Efolderdesc\">New Folder Description:</label><input type=\"text\" name=\"folderdesc\" id=\"Efolderdesc\" value=\"".$currentFolder->getDesc()."\" /><br />";
					print '<input type="submit" name="editF" value="Edit Folder" />';
					print '<input type="submit" name="deleteF" value="Delete Folder" />';
				}
				else {
					print "You cannot edit the current active category.";
				}
?>
			</fieldset></form>
	</div>
<?php
	}
?>
</div></body></html>
