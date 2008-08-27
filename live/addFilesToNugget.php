<?php
	session_start();
	
	include_once("classes/db.php");
	include_once("classes/person.php");
	include_once("classes/group.php");
	include_once("classes/folder.php");
	include_once("classes/file.php");
	include_once("classes/quota.php");
	include_once("classes/nugget.php" );
	include_once("nuggetTypes.php" );

	$_DB = new dbConnection();
	global $_DEFAULTNUGGETS;
	if( isset ($_SESSION['userID'])){
		$currentUser = new Person($_SESSION['userID'], $_DB);
	}else{
		die("You are not logged in.");
	}

	if( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester'])){
		$currentGroup = new Group($_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $_DB);
	}else{
		die("You have not selected a valid group.");
	}

	if(isset($_GET['selectFolder'] )){
		unset($_SESSION['selectedSpecial']);
		$_SESSION['selectedFolder'] = $_GET['selectFolder'];
	}

	if( isset($_GET['selectSpecial'])){ 
		unset($_SESSION['selectedFolder']);
		$_SESSION['selectedSpecial'] = $_GET['selectSpecial'];
	}

	if(isset($_SESSION['selectedFolder']) && $_SESSION['selectedFolder'] != 0 && $_SESSION['selectedFolder'] != 1){
		$currentFolder = new Folder($_SESSION['selectedFolder'], $_DB);
	}else
		$currentFolder = false;
	

	if(!isset($_SESSION['expandFolders'])){
		$_SESSION['expandFolders'] = array();
	}

	if(isset($_GET['toggleExpand'])){
		if( in_array($_GET['toggleExpand'], $_SESSION['expandFolders'])){
			$temp = array($_GET['toggleExpand']);
			$_SESSION['expandFolders'] = array_diff($_SESSION['expandFolders'], $temp);
		}else{
			$_SESSION['expandFolders'][] = $_GET['toggleExpand'];
		}
	}
	
	if(isset($_POST['nugget'])){
		$id = $_POST['nugget'];
		$nugget = new Nugget($id, $_DB, 0);
		$files =  $_POST['files'];
		$fileList = explode( ",", $files);
		foreach($fileList as $file){
			if (is_numeric($file))
				$nugget->addFile($file);
		}
		$folderList = $_POST['folders'];
		$folders = explode( ",", $folderList);
		foreach($folders as $folder) {
			if (is_numeric($folder)) {
			$folder = new Folder($folder, $_DB);
			$folderFiles = $folder->getFiles();
			foreach ($folderFiles as $file) {
				$nugget->addFile($file->getID());
			}
			}
		}
		$header = "Location:editNugget.php?nug=$id";
		header($header);
			
	}

	function printFolder($folder){
		if($_SESSION['selectedFolder'] == $folder->getID()){
			print "<li><a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;toggleExpand=".$folder->getID()."'><img src='img/folder-expanded.gif' border='0' alt='' /></a>&nbsp;<strong><a href='addFilesToNugget.php?selectFolder=".$folder->getID()."'>".$folder->getName()."</a></strong>\n";
		}else{
			print "<li><a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;toggleExpand=".$folder->getID()."'><img src='img/folder.gif' border='0' alt='' /></a>&nbsp;<a href='addFilesToNugget.php?selectFolder=".$folder->getID()."'>".$folder->getName()."</a>\n";
		}
		$subfolder = $folder->getFolders();
		if(in_array($folder->getID(), $_SESSION['expandFolders'])){
			print "<ul>\n";
			foreach($subfoler as $key=> $val){
				printFolder($val);
			}
			print "</ul>\n";
		}
		print "</li>\n";
	}

	function printOptions($group){
		$folder = $group->getGroupFolders();
		foreach($folders as $key=>$subfolder){
			print "<option value=".$subfolder->getID().">+ ".$subfolder->getName()."</option>\n";
			printOptionsRecurse( $subfolder, "&nbsp;&nbsp;&nbsp;+ " );
		}
	}

	function printOptionsRecurse($folder, $indent){
		$folders = $folder->getFolders();
		foreach($folders as $key => $subfolder){
			print "<option value =".$subfolder->getID().">".$indent.$subfolder->getName()."</option>\n";
			printOptionsRecurse($subfolder, "&nbsp;&nbsp;&nbsp;", $indent);
		}
	}

	function printTR(){
		static $i = 0;
		if($i){
			print "<tr class='shade'>";
		}else{
			print "<tr>";
		}

		$i = !$i;
	}

	function canViewFiles($user, $folder){
		if(!$folder)
			return true;
		if($folder->getGroupID() == 0)
			return true;
		if(!$folder->isWriteOnly() )
			return true;
		if($user->isGroupAdministrator($folder->getGroup()))
			return true;
		return false;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups 2.1 - Nugget File Transfer</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		#container{
			margin:auto;
			padding:0;
		}

		#folderbox{
			float:left;
			width:30%;
			margin:5px;
			padding:2px;
			border:1px solid #000;
		}

		#folders {
			width:100%;
			text-align:left;
			background-color:#fff;
			padding-top:5px;
		}
		
		#filebox{
			float:left;
			margin:5px;
			padding:2px;
			width:64%;
			border:1px solid #000;
		}

		#files{
			width:100%;
			text-align-left;
			background-color:#fff;
		}
	
		#menubar{
			background-color:#eeeeee;
			margin-bottom:5px;
			padding:3px;
		}

		ul.addul {
			list-style:none;
			padding:0;
			margin:0;
		}

		ul.addul ul {
			padding-left:20px;
		}

		.window{
			width:500px;
			background-color:#FFF;
			border:1px solid #000;
			visibility:hidden;
			position:absolute;
			left:20px;
			top:20px;
		}
		
		.window-topbar{
			padding-left:5px;
			font-size:14pt;
			color:#FFF;
			background-color:#C00;
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

	<script language="javascript" type= "text/javascript">
	<!--
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
	
		function showMessage(msg){
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore(msgDiv,null);
			window.setTimeout(function() {msgDiv.style.display='none';},3000);
		}
	//-->
	</script></head><body>
<?php
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
			<div id="folders">
				<ul id="top" class="addul">
<?php
					if( !isset( $_SESSION['selectedSpecial']) && $_SESSION['selectedFolder']==0){
						print "<li><a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;toggleExpand=yourfiles'><img src='img/folder-expanded.gif' border='0' alt='' /></a>&nbsp;<strong><a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=0'>Your Files</a></strong>";
						$topFolders=$currentGroup->getGroupFolders();
						if(count($topFolders) > 0) {
							print "<ul>";
							
							foreach($topFolders as $key=> $val){
								printFolder($val);
							}

							print "</ul>";
						}
?>
						</li>
<?php
					}else
						print "<li><a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;toggleExpand=yourfiles'><img src='img/folder.gif' border='0' alt='' /></a>&nbsp;<a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=0'>Your Files</a>";
?>
				</ul>
			</div>
		</div>
		<div id="filebox">
			<div class="columnbanner">
<?php
				if($currentFolder){
					$folderList = $currentFolder->getFolders();
					$fileList = $currentFolder->getFiles();
					print "Contents of ".$currentFolder->getName();
				}else{
					if(isset($_SESSION['selectedSpecial'])){
						if($_SESSION['selectedSpecial'] == 'ipro'){
							$folderList = $currentGroup->getIPROOfficeFolders();
							print "Contents of IPRO Office Files:";
						}
					}else if($_SESSION['selectedFolder']==0){
						$folderList = $currentGroup->getGroupFolders();
						$fileList = $currentGroup->getGroupFiles();
						print "Contents of Your files:";
					}else if($_SESSION['selectedFolder'] == 1){
						$fileList = $currentGroup->getNuggetFiles();
						print "Your nugget Files:";
					}
				}
?>
			</div>
			<form method="post" action = "addFilesToNugget.php">
				<div id = "menubar">
					<?php if (!$currentUser->isGroupGuest($currentGroup)){?>
					<ul class="addul">
						<li><a href="#" onclick="addwin=dhtmlwindow.open('addbox', 'div', 'update', 'Add Files to Nugget', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1')">Add to Nugget</a></li>
<?php
					}?>
					</ul>
				</div>
				<div id="files">
					<table width="100%">
<?php
					if($folderList)
						foreach($folderList as $key=> $folder){
							printTR();
							print "<td><img src='img/folder.gif' alt='' /></td>";
							print "<td><a href='addFilesToNugget.php?nugget={$_GET['nugget']}&amp;selectFolder=".$folder->getID()."'>".$folder->getName()."</a></td>";
							print "<td colspan='3'>".$folder->getDesc()."</td>";
							print "<td align='right'><input type='checkbox' name='folder[".$folder->getID()."]' /></td>";
							print "</tr>\n";
						}
					if(canViewFiles($currentUser, $currentFolder)){
						if($fileList)
							foreach($fileList as $key=>$file){
								printTR();
								print "<td><img src='img/file.gif' alt='' /></td>";
								print "<td><a href='download.php?id=".$file->getID()."'>".$file->getName()."</a></td>";
								if($_SESSION['selectedFolder']==1){
									$nuggetName=$file->getNugget()->getType();
									print "<td>$nuggetName nugget file</td>";
								}
								print "<td>".$file->getDesc()."</td>";
								$author = $file->getAuthor();
								if($author)
									print "<td>".$author->getFullName()."</td>";
								else
									print "<td></td>";
								print "<td>".$file->getDate()."</td>";
								print "<td align='right'><input type='checkbox' name='file[".$file->getID()."]' /></td>";
								print "</tr>\n";
							}
						if(count($folderList)+count($fileList)==0)
							print "<tr><td>There are no files or folders in the selected folder</td></tr>\n";
					}else
						print "<tr><td>You do not have access to view the files in this folder</td></tr>\n";
?>
					</table>
				</div>
			</form>
		</div>
	</div>
		<div class="window-content" id="update" style="display: none">
			<form method="post" action="addFilesToNugget.php" id="moveFile">
				Are you sure you wish to add the selected files to the nugget?
				<input type="hidden" name="folders" />
				<input type="hidden" name="files" />
<?php
				$nugget = $_GET['nugget'];
				print "<input type ='hidden' name='nugget' value='$nugget' />";
?>
				<input type="submit" value="Yes" onclick="copyCheckBoxes();this.form.submit()" />
			</form>
		</div>
	</div>
</body>
</html>
