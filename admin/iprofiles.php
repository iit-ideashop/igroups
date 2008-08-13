<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/folder.php" );
	include_once( "../classes/file.php" );
	include_once( "../classes/quota.php" );
	include_once( "../classes/semester.php");
	include_once( "../classes/filelist.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( !$currentUser->isAdministrator() )
		die("You must be an administrator to access this page.");
		
	if ( isset( $_GET['selectList'] ) ) {
		$_SESSION['selectedList'] = $_GET['selectList'];
		unset( $_SESSION['selectedIPROFolder'] );
	}
	
	if ( isset( $_GET['selectFolder'] ) ) {
		$_SESSION['selectedIPROFolder'] = $_GET['selectFolder'];
	}

	//------Begin Special Display Functions--------------------------//
			
	function printFolder( $folder ) {
	// Prints tree structure of folders
		print "<li><a href='iprofiles.php?selectFolder=".$folder->getID()."'><img src='../img/folder.gif'>".$folder->getName()."</a>\n";
		$subfolder = $folder->getFolders();
		print "<ul>\n";
		foreach ( $subfolder as $key => $val ) {
			printFolder( $val );
		}
		print "</ul>\n";
		print "</li>\n";
	}
	
	function groupSort( $array ) {
		$newArray = array();
		foreach ( $array as $group ) {
			if ( $group )
				$newArray[$group->getName()] = $group;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}
	
	//----End Display Functions-------------------------------------//
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - IPRO Office Files</title>
	<style type="text/css">
		@import url("../default.css");	
		
		#folderbox {
			float:left;
			width:30%;
			margin:5px;
			padding:2px;
			border:3px solid #000;
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
			border:3px solid #000;
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
		
		ul {
			list-style:none;
			padding:0;
			margin:0;
		}
			
		ul ul {
			padding-left:20px;
		}
			
		.window {
			width:500px;
			background-color:#FFF;
			border: 1px solid #000;
			visibility:hidden; 
			position:absolute;
			left:20px;
			top:20px;
		}
		
		.window-topbar {
			padding-left:5px;
			font-size:14pt;
			color:#FFF;
			background-color:#C00;
		}
		
		.window-content {
			padding:5px;
		}
	</style>
	<script type="text/javascript">
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
		
		function selectAll( name ) {
			var inputs = document.getElementsByTagName('input');
			for ( var i=0; i < inputs.length; i++ ) {
				if ( inputs[i].type == "checkbox" ) {
					values = inputs[i].name.split( /\x5b|\x5d/ );
					if ( values[0] == name )
						inputs[i].checked = true;
				}
			}
		}
	</script>
</head>
<body>
<?php
	
	//------Start of Code for Form Processing-------------------------//
	
	if ( isset( $_POST['createlist'] ) ) {
		$id = createIPROFolder( $_POST['listname'], $_POST['listname'], 0, 0, $db );
		$_SESSION['selectedList'] = createFileList( $_POST['listname'], $id, $db );
		unset( $_SESSION['selectedIPROFolder'] );
	}
	
	if ( isset( $_SESSION['selectedList'] ) ) {
		$currentList = new FileList( $_SESSION['selectedList'], $db );
	}
	
	if ( isset( $_POST['create'] ) ) {
		if ( isset( $_SESSION['selectedIPROFolder'] ) ) 
			$pfid = $_SESSION['selectedIPROFolder'];
		else {
			$folder = $currentList->getBaseFolder();
			$pfid = $folder->getID();
		}
		createIPROFolder( $_POST['foldername'], $_POST['folderdescription'], 0, $pfid, $db );
?>
		<script type="text/javascript">
			showMessage("Folder was successfully created");
		</script>
<?php
	}
	
	if ( isset( $_POST['upload'] ) ) {
		// Get target folder ID
		if ( isset( $_SESSION['selectedIPROFolder'] ) ) 
			$fid = $_SESSION['selectedIPROFolder'];
		else {
			$folder = $currentList->getBaseFolder();
			$fid = $folder->getID();
		}
		
		if ( $_FILES['thefile']['error'] == UPLOAD_ERR_OK ) {
			$file = createIPROFile( $_POST['filename'], $_POST['filedescription'], $fid, $currentUser->getID(), $_FILES['thefile']['name'], $db );
			move_uploaded_file($_FILES['thefile']['tmp_name'], $file->getDiskName() );
?>
		<script type="text/javascript">
			showMessage("File was successfully uploaded");
		</script>
<?php
		}
	}
	
	if ( isset( $_POST['dellist'] ) ) {
		foreach( $_POST['list'] as $listid => $val ) {
			$list = new FileList( $listid, $db );
			$list->delete();
			if ( $_SESSION['selectedList'] == $listid )
				unset( $_SESSION['selectedList'] );
		}
	}
	
	if ( isset( $_POST['delete'] ) ) {
		if (isset($_POST['folder'])) {
		foreach( $_POST['folder'] as $folderid => $val ) {
			$folder = new Folder( $folderid, $db );
			$folder->delete();
		}
		}
		if (isset($_POST['file'])) {
		foreach( $_POST['file'] as $fileid => $val ) {
			$file = new File( $fileid, $db );
			$file->delete();
		}
		}
?>
		<script type="text/javascript">
			showMessage("Selected items were successfully deleted");
		</script>
<?php
	}
	
	if ( isset( $_GET['selectSemester'] ) ) {
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
	}
	
	if ( !isset( $_SESSION['selectedIPROSemester'] ) || $_SESSION['selectedIPROSemester'] == 0 ) {
		$semester = $db->iknowQuery( "SELECT iID FROM Semesters WHERE bActiveFlag=1" );
		$row = mysql_fetch_row( $semester );
		$_SESSION['selectedIPROSemester'] = $row[0];
	}
	
	$currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db );
	
	if ( isset( $_POST['updategroups'] ) ) {
		if ( $currentSemester ) {
			$db->igroupsQuery( "DELETE FROM GroupListMap WHERE iListID=".$_SESSION['selectedList']." AND iSemesterID=".$currentSemester->getID() );
			foreach ( $_POST['uselist'] as $key => $val ) {
				$group = new Group( $key, 0, $currentSemester->getID(), $db );
				$group->addFileList( $_SESSION['selectedList'] );
			}
		}
	}
	
	//------End Form Processing Code---------------------------------//
	
	print "<form method='post' action='iprofiles.php'><ul>";
	$lists = $db->igroupsQuery( "SELECT iID FROM FileLists" );
	while ( $row = mysql_fetch_row( $lists ) ) {
		$list = new FileList( $row[0], $db );
		print "<li><input type='checkbox' name='list[".$list->getID()."]'><a href='iprofiles.php?selectList=".$list->getID()."'>".$list->getName()."</a></li>";
	}
	print "</ul>";
	print "<input type='button' onClick=\"document.getElementById('newlist').style.visibility='visible';\" value='Add a new list'>";
	print "<input type='submit' name='dellist' value='Delete selected lists'></form>";
?>
<div class="window" id="newlist">
	<div class="window-topbar">
		Create a File List <input class="close-button" type="button" onClick="document.getElementById('newlist').style.visibility='hidden';">
	</div>
	<div class="window-content">
		<form method="post" action="iprofiles.php">
			List Name: <input type="text" name="listname"><br>
			<input type="submit" name="createlist" value="Create List">
		</form>
	</div>
</div>
<?php
	if ( isset( $_SESSION['selectedList'] ) ) {
?>
		<div id="folderbox">
			<div id="columnbanner">
<?php
				print "Folders in ".$currentList->getName().":";
?>
			</div>
			<ul>
<?php
				printFolder( $currentList->getBaseFolder() );
?>
			</ul>
		</div>
		<div id="filebox">
			<div id="columnbanner">
<?php
				if ( isset( $_SESSION['selectedIPROFolder'] ) ) 
					$folder = new Folder( $_SESSION['selectedIPROFolder'], $db );
				else
					$folder = $currentList->getBaseFolder();
					
				print "Contents of ".$folder->getName().":";
?>
			</div>
			
			<form method="post" action="iprofiles.php">

			<div id="menubar">
				<ul>
					<li><a href="#" onClick="document.getElementById('upload').style.visibility='visible';">Upload File</a></li>
					<li><a href="#" onClick="document.getElementById('newfolder').style.visibility='visible';">Create Folder</a></li>
					<li><a href="#" onClick="document.getElementById('delete').form.submit()">Delete</a>
					<input type='hidden' id='delete' name='delete' value='delete'></li>
				</ul>
			</div>
			
<?php
			print "<table width='100%'>";
			$folderList = $folder->getFolders();
			$fileList = $folder->getFiles();
			
			foreach ( $folderList as $key => $folder ) {
				printTR();
				print "<td><img src='../img/folder.gif'></td>";
				print "<td><a href='iprofiles.php?selectFolder=".$folder->getID()."'>".$folder->getName()."</a></td>";
				print "<td>".$folder->getDesc()."</td>";
				print "<td align='right'><input type='checkbox' name='folder[".$folder->getID()."]'></td>";
				print "</tr>\n";
			}
			
			foreach ( $fileList as $key => $file ) {
				printTR();
				print "<td><img src='../img/file.gif'></td>";
				print "<td><a href='../download.php?id=".$file->getID()."'>".$file->getName()."</a></td>";
				print "<td>".$file->getDesc()."</td>";
				print "<td align='right'><input type='checkbox' name='file[".$file->getID()."]'></td>";
				print "</tr>\n";
			}
		
			if ( count( $folderList ) + count( $fileList ) == 0 )
				print "<tr>There are no files or folders in the selected folder</tr>\n";

			print "</table>";
?>
			</form>
		</div>
		<div id="groups" style="clear:both;">
			<form method="post" action="iprofiles.php">
<?php
			$groups = $currentSemester->getGroups();
			print "<h1>".$currentSemester->getName()."</h1>";
			$groups = groupSort( $groups );
			
			foreach ( $groups as $group ) {
				print "<input type='checkbox' name='uselist[".$group->getID()."]'";
				if ( $group->usesFileList( $_SESSION['selectedList'] ) )
					print " checked";
				print "> ".$group->getName()."<br>";
			}
?>
				<input type="button" value="Select All" onClick="selectAll('uselist')"><input type="submit" name="updategroups" value="Update Selected Groups">
			</form>
		</div>
		<div id="semesterSelect">
				<form method="get" action="iprofiles.php">
					<select name="semester">
<?php
					$semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
					while ( $row = mysql_fetch_row( $semesters ) ) {
						$semester = new Semester( $row[0], $db );
						print "<option value=".$semester->getID().">".$semester->getName()."</option>";
					}
?>
					</select>
					<input type="submit" name="selectSemester" value="Select Semester">
				</form>
		</div>
		<div class="window" id="upload">
			<div class="window-topbar">
				File Upload <input class="close-button" type="button" onClick="document.getElementById('upload').style.visibility='hidden';">
			</div>
			<div class="window-content">
				<form method="post" action="iprofiles.php" enctype="multipart/form-data">
					File: <input type="file" name="thefile"><br>
					File Name: <input type="text" name="filename"><br>
					Description: <input type="text" name="filedescription"><br>
					This file will be placed in the
	<?php
					print $folder->getName();
	?>
					folder.<br>
					<input type="submit" name="upload" value="Upload File">
				</form>
			</div>
		</div>
		<div class="window" id="newfolder">
			<div class="window-topbar">
				Create a Folder <input class="close-button" type="button" onClick="document.getElementById('newfolder').style.visibility='hidden';">
			</div>
			<div class="window-content">
				<form method="post" action="iprofiles.php">
					Folder Name: <input type="text" name="foldername"><br>
					Description: <input type="text" name="folderdescription"><br>
					This folder will be placed in the
<?php					
					print $folder->getName();
?>
					folder.<br>
					<input type="submit" name="create" value="Create Folder">
				</form>
			</div>
		</div>
<?php
	}
?>
