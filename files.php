<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/folder.php" );
	include_once( "classes/file.php" );
	include_once( "classes/quota.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		 die("You are not logged in.");
	
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
		
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
			print "<li><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"-\" title=\"Open folder\" />&nbsp;<strong><a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a></strong>\n";
		else if(in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs())) //The selected folder is a subfolder of this folder
			print "<li><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"-\" title=\"Open folder\" />&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
		else if(in_array( $folder->getID(), $_SESSION['expandFolders'] )) //The user wants this folder expanded
			print "<li><a href=\"files.php?toggleExpand=".$folder->getID()."\"><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
		else
			print "<li><a href=\"files.php?toggleExpand=".$folder->getID()."\"><img src=\"img/folder.png\" border=\"0\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
		if ( count($subfolder) > 0 && (in_array( $folder->getID(), $_SESSION['expandFolders'] ) || in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs()) || $_SESSION['selectedFolder'] == $folder->getID())) {
			print "<ul class=\"filesul\">\n";
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
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Files</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		#container {
			margin:auto;
			padding:0;
		}
		
		#folderbox {
			float:left;
			width:30%;
			margin:5px;
			padding:2px;
			border:1px solid #000;
		}
		
		#folders {
			width:100%;
			text-align:left;
			background-color: #fff;
			padding-top:5px;
		}
		
		#filebox {
			float:left;
			margin:5px;
			padding:2px;
			width:65%;
			border:1px solid #000;
		}
		
		#files {
			width:100%;
			text-align:left;
			background-color:#fff;
		}
		
		.menubar {
			background-color:#eeeeee;
			margin-bottom:5px;
			padding:3px;
		}
		
		.menubar li {
			padding:5px;
			display:inline;
		}
		
		ul.filesul {
			list-style:none;
			padding:0;
			margin:0;
		}
			
		ul.filesul ul {
			padding-left:20px;
		}
	</style>

<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>

	<script type="text/javascript">
	<!--
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
		
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
	//-->
	</script>
</head>
<body>
<?php
require("sidebar.php");
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
?>
		<script type="text/javascript">
			showMessage("Folder successfully created");
		</script>
<?php
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
				if (!isset($_POST['private']))
					$file = createFile( $_POST['filename'], $_POST['filedescription'], $fid, $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $db );
				else {
					$file = createFile( $_POST['filename'], $_POST['filedescription'], $currentUser->getID(), $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $db );
                                	$file->setPrivate(1);
					$file->updateDB();
				}
				$file->setMimeType($_FILES['thefile']['type']);
				move_uploaded_file($_FILES['thefile']['tmp_name'], $file->getDiskName() );
?>
				<script type="text/javascript">
					showMessage("File successfully uploaded");
				</script>
<?php
			}
			else {
				$currentQuota->sendWarning(1);
?>
				<script type="text/javascript">
					showMessage("ERROR: Not enough space for file");
				</script>
<?php
			}
		}
		else {
?>
			<script type="text/javascript">
				showMessage("Error occured during upload, please try again");
			</script>
<?php
		}
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
                                $file = createFile( $oldFile->getNameNoVer(), $_POST['filedescription'], $fid, $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $db );
				$file->setVersion($oldFile->getVersion() + 1);
				$file->updateDB();
                                move_uploaded_file($_FILES['thefile']['tmp_name'], $file->getDiskName() );
?>
                                <script type="text/javascript">
                                        showMessage("File successfully uploaded");
                                </script>
<?php
                        }
                        else {
                                $currentQuota->sendWarning(1);
?>
                                <script type="text/javascript">
                                        showMessage("ERROR: Not enough space for file");
                                </script>
<?php
                        }
                }
		else {
?>
                        <script type="text/javascript">
                                showMessage("ERROR: Could not update file");
                        </script>
<?php
                }
		}
		else {
?>
			<script type="text/javascript">
				showMessage("ERROR: Could not update file. Make sure you selected a file to update.");
			</script>
<?php
		}
        }

	if(isset($_POST['editF']) && !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']!=0 && $currentUser->isGroupModerator($currentFolder->getGroup()))
	{
		if (isset($_POST['foldername']) && !$currentFolder->isIPROFolder())
			$currentFolder->setName( $_POST['foldername'] );
		if (isset($_POST['folderdesc']) && !$currentFolder->isIPROFolder())
			$currentFolder->setDesc( $_POST['folderdesc'] );
		$currentFolder->updateDB();
?>
		<script type="text/javascript">
			showMessage("Folder successfully edited");
		</script>
<?php
	}
	else if(isset($_POST['deleteF']) && !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']!=0 && $currentUser->isGroupModerator($currentFolder->getGroup()))
	{
		$currentFolder->trash();
		$_SESSION['selectedFolder'] = 0;
		$currentFolder = false;
?>
		<script type="text/javascript">
			showMessage("Folder successfully deleted");
		</script>
<?php
	}
	else if(isset($_POST['editF']) || isset($_POST['deleteF']))
	{
?>
		<script type="text/javascript">
			showMessage("Error: Folder operation failed");
		</script>
<?php
	}
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
?>
		<script type="text/javascript">
			showMessage("Selected items successfully moved");
		</script>
<?php
		}
		else {	
?>
		<script type="text/javascript">
			showMessage("Some elements could not be moved");
		</script>
<?php
		}
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
 ?>
                 <script type="text/javascript">
                         showMessage("File or folder renamed");
                 </script>
 <?php
        }
        else if( isset( $_POST['rename'] )) {
?>
        <script type="text/javascript">
                showMessage("Unable to rename. Make sure one file or one folder is selected.");
        </script>
<?php
	}

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
		if ( isset( $_POST['folder'] ) || isset( $_POST['file'] )) {
?>
		<script type="text/javascript">
			showMessage("Selected items successfully deleted");
		</script>
<?php
	}
	else {
?>
		<script type="text/javascript">
			showMessage("Please select file(s) or folder(s) to delete first.");
		</script>
<?php
	}
	}
	
	//------End Form Processing Code---------------------------------//
	
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
				<ul class="filesul"> <?php if (!$currentUser->isGroupGuest($currentGroup) && !isset($_SESSION['selectedSpecial'])) { ?>
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
				<ul id="top" class="filesul">
<?php
					if ( !isset( $_SESSION['selectedSpecial'] ) && $_SESSION['selectedFolder']==0 )
						print '<li><a href="files.php?toggleExpand=yourfiles"><img src="img/folder-expanded.png" border="0" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectFolder=0">Your Files</a></strong>';
					else
						print '<li><a href="files.php?toggleExpand=yourfiles"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectFolder=0">Your Files</a>';
					
						$topFolders = $currentGroup->getGroupFolders();
						if(count($topFolders) > 0) {
							print "<ul class=\"filesul\">";
							
							foreach ( $topFolders as $key => $val ) {
								printFolder( $val );
							}
						print "</ul>";
						}
					print "</li>";

					if ( $_SESSION['selectedSpecial'] == 'obsolete' )
							print '<li><a href="files.php?selectSpecial=obsolete"><img src="img/folder-expanded.png" border="0" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectSpecial=obsolete">Past Versions</a></strong></li>';
						else
							print '<li><a href="files.php?selectSpecial=obsolete"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=obsolete">Past Versions</a></li>';
					if ( $_SESSION['selectedSpecial'] == 'trash' )
							print '<li><a href="files.php?selectSpecial=trash"><img src="img/folder-expanded.png" border="0" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectSpecial=trash">Trash Bin</a></strong</li>';
						else
							print '<li><a href="files.php?selectSpecial=trash"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=trash">Trash Bin</a></li>';
					

					if ( $currentGroup->getType() == 0 ) {
						if ( $_SESSION['selectedSpecial'] == 'ipro' )
							print '<li><a href="files.php?toggleExpand=iprofiles"><img src="img/folder-expanded.png" border="0" alt="-" title="Open folder" /></a>&nbsp;<strong><a href="files.php?selectSpecial=ipro">IPRO Office Files</a></strong>';
						else
							print '<li><a href="files.php?toggleExpand=iprofiles"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=ipro">IPRO Office Files</a>';

						if ( in_array( "iprofiles", $_SESSION['expandFolders'] ) ) {
?>
							<ul class="filesul">
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
				<li><img src="img/folder.png" border="0" alt="+" title="Folder" />&nbsp;<a href="dropbox.php">Secure Dropbox</a></li>
				</ul>
			</div>
		</div>
		<div id="filebox">
			<div class="columnbanner">
<?php
				if ( $currentFolder ) {
					$folderList = $currentFolder->getFolders();
					$fileList = $currentFolder->getFiles();
					print "Contents of ".$currentFolder->getName();
				}
				else {
					if ( isset( $_SESSION['selectedSpecial'] ) ) {
						if ( $_SESSION['selectedSpecial'] == 'trash' ) {
							$fileList = $currentGroup->getGroupTrashBin();
							print "Contents of Trash:";
						}
						else if ($_SESSION['selectedSpecial'] == 'obsolete') {
							$fileList = $currentGroup->getGroupObsolete();
							print "Past Versions";
						}
						else
						if ( $_SESSION['selectedSpecial'] == 'ipro' ) {
							$folderList = $currentGroup->getIPROOfficeFolders();
							print "Contents of IPRO Office Files:";
						}
					}
					else {
						$folderList = $currentGroup->getGroupFolders();
						$fileList = $currentGroup->getGroupFiles();
						print "Contents of Your Files:";
					}
				}
?>			
			</div>
			<form method="post" action="files.php">
				<div class="menubar">
					<?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
					<ul class="filesul">
						<?php if ($_SESSION['selectedSpecial'] != 'obsolete' && $_SESSION['selectedSpecial'] != 'ipro') { if ( $currentFolder == 0 || (is_object($currentFolder) && !$currentFolder->isIPROFolder())) { ?>
						<li><a href="#" onclick="uploadwin=dhtmlwindow.open('uploadbox', 'div', 'upload', 'Upload File', 'width=350px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); return false">Upload File</a></li>
						<li><a href="#" onclick="updatewin=dhtmlwindow.open('updatebox', 'div', 'update', 'Update File', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Update File</a></li>
<?php
					if(count($fileList) > 0 && (($currentFolder == 0 && count($group->getGroupFolders()) > 0) || ($_SESSION['selectedSpecial'] != 'obsolete' && $_SESSION['selectedSpecial'] != 'ipro' )) ) {
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
<?php
					if ($currentFolder && !$currentFolder->isIPROFolder()) {
                                                        printTR();
                                                        print "<td width=\"24\"><img src=\"img/folder.png\" border=\"0\" alt=\"+\" title=\"Folder\" /></td>";
                                                        print "<td align=\"left\" colspan=\"5\"><a href=\"files.php?selectFolder=".$currentFolder->getParentFolderID()."\">..</a></td>";
                                                        print "</tr>\n";
                                        }
					/*if ($folderList) {//Prevents an error from PHP 4 to PHP 5 switch
						foreach ( $folderList as $key => $folder ) {
							printTR();
							print "<td><img src=\"img/folder.png\" border=\"0\" alt=\"+\" title=\"Folder\" /></td>";
							print "<td><a href='files.php?selectFolder=".$folder->getID()."'>".$folder->getName()."</a></td>";
							print "<td colspan='3'>".$folder->getDesc()."</td>";
							print "<td><input type='checkbox' name='folder[".$folder->getID()."]' /></td>";
							print "<td><a href=\"#\" onclick=\"renamewin=dhtmlwindow.open('renamebox', 'ajax', 'renamefile.php?folderid=".$folder->getID()."', 'Rename Folder', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Rename</a></td>";
							print "</tr>\n";
						}
					}*/			
					if ( canViewFiles( $currentUser, $currentFolder ) ) {
					if ($fileList) //Prevents an error from PHP 4 to PHP 5 switch
						foreach ( $fileList as $key => $file ) {
							printTR();
							print "<td><img src=\"img/file.png\" alt=\"File\" title=\"File\" /></td>";
							print "<td><a href=\"download.php?id=".$file->getID()."\">".$file->getName()."</a></td>";
							print "<td>".$file->getDesc()."</td>";
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
							print "<tr><td colspan=\"6\">There are no files or folders in the selected folder</td></tr>\n";
						else if(count($fileList) == 0)
							print "<tr><td colspan=\"6\">There are no files in the selected folder</td></tr>\n";
					}
					else
						print "<tr><td>You do not have access to view the files in this folder</td></tr>\n";
?>
					</table>
				</div>
			</form>
		</div>
	</div>
		<div class="window-content" id="upload" style="display: none">
			<form method="post" action="files.php" enctype="multipart/form-data">
				File: <input type="file" name="thefile" /><br />
				File Name: <input type="text" name="filename" /><br />
				Description: <input type="text" name="filedescription" /><br />
				This file will be placed in the
<?php
				if ( $currentFolder )
					print $currentFolder->getName();
				else
					print "Your Files"
?>
				folder.<br />- or -<br />
				<input type="checkbox" name="private" />&nbsp;Send to Dropbox (viewable only by instructor)<br />
				<input type="submit" name="upload" value="Upload File" />
			</form>
		</div>
                <div class="window-content" id="update" style="display: none">
                        <form method="post" action="files.php" enctype="multipart/form-data">
                                File: <input type="file" name="thefile" /><br />
       				Description: <input type="text" name="filedescription" /><br />
                                This file will be placed in the
<?php
                                if ( $currentFolder )
                                        print $currentFolder->getName();
                                else
                                        print "Your Files"
?>
                                folder.<br />

				<input type="hidden" name="folders" />
                                <input type="hidden" name="files" />
				<input type="hidden" name="fupdate" value="fupdate" />
                                <input type="submit" value="Upload File" onclick="copyCheckBoxes();this.form.submit()" />
                        </form>
                </div>
		<div class="window-content" id="newfolder" style="display: none">
			<form method="post" action="files.php">
				Folder Name: <input type="text" name="foldername" /><br />
				Description: <input type="text" name="folderdescription" /><br />
				This folder will be placed in the
<?php
				if ( $currentFolder )
					print $currentFolder->getName();
				else
					print "Your Files"
?>
				folder.<br />
<?php
				if ( $currentUser->isGroupAdministrator( $currentGroup ) ) {
?>
					<input type="radio" name="status" value="0" checked="checked" />Normal Folder  <input type="radio" name="status" value="1" />Write Only<br />
<?php
				}
				else
					print "<input type=\"hidden\" name=\"status\" value=\"0\" />";
?>
				<input type="submit" name="create" value="Create Folder" />
			</form>
		</div>
		<div class="window-content" id="editfolder" style="display: none">
			<form method="post" action="files.php">
<?php
				if ( $currentFolder ) {
					print "Current Folder Name: ".$currentFolder->getName()."<br />";
					print "New Folder Name: <input type=\"text\" name=\"foldername\" value=\"".$currentFolder->getName()."\" /><br />";
					print "New Folder Description: <input type=\"text\" name=\"folderdesc\" value=\"".$currentFolder->getDesc()."\" /><br />";
					print '<input type="submit" name="editF" value="Edit Folder" />';
					print '<input type="submit" name="deleteF" value="Delete Folder" />';
				}
				else {
					print "You cannot edit the current active category.";
				}
?>
			</form>
	</div>
		<div class="window-content" id="move" style="display: none">
			<form method="post" action="files.php">
				Select Target Folder:
				<select name="target">
					<option value="0">Your Files</option>
<?php
					printOptions( $currentGroup );
?>
				</select>
				<input type="hidden" name="folders" />
				<input type="hidden" name="files" />
				<input type="hidden" name="move" value="move" /><br />
				<input type="button" value="Move Files and Folders" onclick="copyCheckBoxes();this.form.submit()" />
			</form>
		</div>
         </div>
</body>
</html>
