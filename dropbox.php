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

	
	//------Begin Special Display Functions--------------------------//
			
	function printFolder( $folder ) {
	// Prints tree structure of folders
		$subfolder = $folder->getFolders();
		if ( $_SESSION['selectedFolder'] == $folder->getID()) //This is the selected folder
			print "<li><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"=\" title=\"Open folder\" />&nbsp;<strong><a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a></strong>\n";
		else if(in_array($_SESSION['selectedFolder'], $folder->getAllFolderIDs())) //The selected folder is a subfolder of this folder
			print "<li><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"=\" title=\"Open folder\" />&nbsp;<a href=\"files.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a>\n";
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
			width:64%;
			border:1px solid #000;
		}
		
		#files {
			width:100%;
			text-align:left;
			background-color:#fff;
		}
		
		#menubar {
			background-color:#eeeeee;
			margin-bottom:5px;
			padding:3px;
		}
		
		#menubar li {
			padding:5px;
			display:inline;
		}
		
		ul.dropul {
			list-style:none;
			padding:0;
			margin:0;
		}
			
		ul.dropul ul {
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
	<script language="javascript" type="text/javascript">
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

if (isset($_POST['upload'])) {

		if ( $_FILES['thefile']['error'] == UPLOAD_ERR_OK ) {
                                $file = createFile( $_POST['filename'], $_POST['filedescription'], $currentUser->getID(), $currentUser->getID(), $_FILES['thefile']['name'], $currentGroup, $db );
				$file->setPrivate(1);
                                $file->setMimeType($_FILES['thefile']['type']);
				$file->updateDB();
                                move_uploaded_file($_FILES['thefile']['tmp_name'], $file->getDiskName() );
?>
                                <script type="text/javascript">
                                        showMessage("File successfully uploaded");
                                </script>
<?php
                }
                else {
?>
                        <script type="text/javascript">
                                showMessage("Error occured during upload, please try again");
                        </script>
<?php
                }

}

if ( isset( $_POST['delete'] ) ) {
                if ( isset( $_POST['file'] )) {
                foreach( $_POST['file'] as $fileid => $val ) {
                        $file = new File( $fileid, $db );
                        if ( $currentUser->getID() == $file->getAuthorID() ) {
                                        $file->delete();
                        }
                }
                }
                if ( isset( $_POST['file'] )) {
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
                        <div id="folders">
                                <ul id="top" class="dropul">
<?php
                                                print '<li><img src="img/folder.png" border="0" alt="=" title="Folder" />&nbsp;<a href="files.php?selectFolder=0">Your Files</a></li>';
                                                print '<li><a href="files.php?selectSpecial=obsolete"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=obsolete">Past Versions</a></li>';
                                                print '<li><a href="files.php?selectSpecial=trash"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=trash">Trash Bin</a></li>';
                                                print '<li><a href="files.php?toggleExpand=iprofiles"><img src="img/folder.png" border="0" alt="+" title="Folder" /></a>&nbsp;<a href="files.php?selectSpecial=ipro">IPRO Office Files</a></li>';

?>
                                <li><img src="img/folder-expanded.png" border="0" alt="-" title="Open folder" />&nbsp;<a href="dropbox.php">Secure Dropbox</a></li>
                                </ul>
                        </div>
                </div>

		<div id="filebox">
			<div class="columnbanner">
<?php
				print "<span id=\"boxtitle\">My Secure Dropbox</span><br /><span id=\"boxdesc\">Files in your dropbox can only be viewed by you and your instructor</span>";
?>			
			</div>
			<form method="post" action="dropbox.php">
				<div id="menubar">
					<?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
					<ul class="dropul">
						<li><a href="#" onclick="uploadwin=dhtmlwindow.open('uploadbox', 'div', 'upload', 'Upload File', 'width=350px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); return false">Add File</a></li>
						<li><a href="#" onclick="document.getElementById('delete').form.submit()">Delete File</a>
						<input type='hidden' id='delete' name='delete' value='delete' /></li>
					</ul>
					<?php } ?>
				</div>
<?php
					if ($currentUser->isGroupAdministrator($currentGroup))
						$query = $db->igroupsQuery("SELECT * FROM Files WHERE iGroupID={$currentGroup->getID()} AND iSemesterID={$_SESSION['selectedSemester']} AND bPrivate=1 ORDER BY iAuthorID");
					else
						$query = $db->igroupsQuery("SELECT * FROM Files WHERE iGroupID={$currentGroup->getID()} AND iSemesterID={$_SESSION['selectedSemester']} AND bPrivate=1 AND iAuthorID={$currentUser->getID()}");
					$files = array();
					while ($result = mysql_fetch_array($query))
						$files[] = new File($result['iID'], $db);

				if(count($files) > 0) {
					print '<div id="files"><table width="100%">';
					foreach ($files as $file) {
						printTR();
                                                print "<td><img src=\"img/file.png\" alt=\"File\" title=\"File\" /></td>";
                                                print "<td><a href='download.php?id=".$file->getID()."'>".$file->getName()."</a></td>";
                                                print "<td>".$file->getDesc()."</td>";
                                                $author = $file->getAuthor();
                                                if ( $author )
                                                        print "<td>".$author->getFullName()."</td>";
                                                else
                                                        print "<td></td>";
                                                print "<td>".$file->getDateTime()."</td>";
                                                print "<td align='right'><input type='checkbox' name='file[".$file->getID()."]' /></td>";
                                                print "</tr>\n";
					}
					if(count($files) == 0)
						print "<tr><td colspan=\"6\">There are no files in your dropbox.</td></tr>\n";
					print '</table></div>';
				}
?>
			</form>
		</div>
	</div>
		<div class="window-content" id="upload" style="display:none">
			<form method="post" action="dropbox.php" enctype="multipart/form-data">
				File: <input type="file" name="thefile" /><br />
				File Name: <input type="text" name="filename" /><br />
				Description: <input type="text" name="filedescription" /><br />
				<input type="submit" name="upload" value="Upload File" />
			</form>
		</div>
	</div>
</body>
</html>
