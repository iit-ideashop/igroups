<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/folder.php');
	include_once('../classes/file.php');
	include_once('../classes/quota.php');
	include_once('../classes/semester.php');
	include_once('../classes/filelist.php');
		
	if(isset($_GET['selectList']))
	{
		$_SESSION['selectedList'] = $_GET['selectList'];
		unset($_SESSION['selectedIPROFolder']);
	}
	
	if(isset($_GET['selectFolder']))
	{
		$_SESSION['selectedIPROFolder'] = $_GET['selectFolder'];
	}
	
	//------Start of Code for Form Processing-------------------------//
	
	if(isset($_POST['createlist']))
	{
		$id = createIPROFolder($_POST['listname'], $_POST['listname'], 0, 0, $db);
		$_SESSION['selectedList'] = createFileList($_POST['listname'], $id, $db);
		unset($_SESSION['selectedIPROFolder']);
	}
	
	if(isset($_SESSION['selectedList']))
	{
		$currentList = new FileList($_SESSION['selectedList'], $db);
	}
	
	if(isset($_POST['create']))
	{
		if(isset($_SESSION['selectedIPROFolder'])) 
			$pfid = $_SESSION['selectedIPROFolder'];
		else
		{
			$folder = $currentList->getBaseFolder();
			$pfid = $folder->getID();
		}
		createIPROFolder($_POST['foldername'], $_POST['folderdescription'], 0, $pfid, $db);
		$message = 'Folder was successfully created';
	}
	
	if(isset($_POST['upload']))
	{
		// Get target folder ID
		if(isset($_SESSION['selectedIPROFolder'])) 
			$fid = $_SESSION['selectedIPROFolder'];
		else
		{
			$folder = $currentList->getBaseFolder();
			$fid = $folder->getID();
		}
		
		if($_FILES['thefile']['error'] == UPLOAD_ERR_OK)
		{
			$file = createIPROFile($_POST['filename'], $_POST['filedescription'], $fid, $currentUser->getID(), $_FILES['thefile']['name'], $db);
			move_uploaded_file($_FILES['thefile']['tmp_name'], $file->getDiskName());
			$message = 'File was successfully uploaded';
		}
	}
	
	if(isset($_POST['dellist']))
	{
		foreach($_POST['list'] as $listid => $val)
		{
			$list = new FileList($listid, $db);
			$list->delete();
			if($_SESSION['selectedList'] == $listid)
				unset($_SESSION['selectedList']);
		}
	}
	
	if(isset($_POST['delete']))
	{
		if(isset($_POST['folder']))
		{
			foreach($_POST['folder'] as $folderid => $val)
			{
				$folder = new Folder($folderid, $db);
				$folder->delete();
			}
		}
		if(isset($_POST['file']))
		{
			foreach($_POST['file'] as $fileid => $val)
			{
				$file = new File($fileid, $db);
				$file->delete();
			}
		}
		$message = 'Selected items were successfully deleted';
	}
	
	if(isset($_GET['selectSemester']))
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
	
	if(!isset($_SESSION['selectedIPROSemester'])|| $_SESSION['selectedIPROSemester'] == 0)
	{
		$semester = $db->query("SELECT iID FROM Semesters WHERE bActiveFlag=1");
		$row = mysql_fetch_row($semester);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}
	
	$currentSemester = new Semester($_SESSION['selectedIPROSemester'], $db);
	
	if(isset($_POST['updategroups']))
	{
		if($currentSemester)
		{
			$db->query("DELETE FROM GroupListMap WHERE iListID=".$_SESSION['selectedList']." AND iSemesterID=".$currentSemester->getID());
			foreach($_POST['uselist'] as $key => $val)
			{
				$group = new Group($key, 0, $currentSemester->getID(), $db);
				$group->addFileList($_SESSION['selectedList']);
			}
		}
	}
	
	//------End Form Processing Code---------------------------------//
	//------Begin Special Display Functions--------------------------//
			
	function printFolder($folder)
	{
		// Prints tree structure of folders
		global $skin;
		echo "<li><a href=\"iprofiles.php?selectFolder=".$folder->getID()."\"><img src=\"../skins/$skin/img/folder.png\" alt=\"\" style=\"border-style: none\" />".$folder->getName()."</a>\n";
		$subfolder = $folder->getFolders();
		if(count($subfolder) > 0)
		{
			echo "<ul class=\"prof\">\n";
			foreach($subfolder as $key => $val)
				printFolder($val);
			echo "</ul>\n";
		}
		echo "</li>\n";
	}
	
	function groupSort($array)
	{
		$newArray = array();
		foreach($array as $group)
		{
			if($group)
				$newArray[$group->getName()] = $group;
		}
		ksort($newArray);
		return $newArray;
	}
	
	//----End Display Functions-------------------------------------//
	//----Start XHTML Output----------------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/files.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/files.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - IPRO Office Files</title>
<style type="text/css">
.window {
	display: none;
}
</style>
<script type="text/javascript">
//<![CDATA[
function selectAll(name)
{
	var inputs = document.getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].type == "checkbox")
		{
			values = inputs[i].name.split(/\x5b|\x5d/);
			if(values[0] == name)
				inputs[i].checked = true;
		}
	}
}
//]]>
</script>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
</head>
<body>
<?php
	require("sidebar.php");
	echo "<div id=\"content\"><div id=\"topbanner\">IPRO Office Files</div>";
	echo "<form method=\"post\" action=\"iprofiles.php\"><fieldset><ul class=\"prof\">";
	$lists = $db->query("SELECT iID FROM FileLists");
	while($row = mysql_fetch_row($lists))
	{
		$list = new FileList($row[0], $db);
		echo "<li><input type=\"checkbox\" name=\"list[".$list->getID()."]\" /><a href=\"iprofiles.php?selectList=".$list->getID()."\">".$list->getName()."</a></li>";
	}
	echo "<li></li></ul>";
	echo "<input type=\"button\" onclick=\"newwin=dhtmlwindow.open('newbox', 'div', 'newlist', 'Create a File List', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\" value=\"Add a new list\" />";
	echo "<input type=\"submit\" name=\"dellist\" value=\"Delete selected lists\" /></fieldset></form>";
?>
<div class="window" id="newlist">
	<form method="post" action="iprofiles.php"><fieldset>
		<label for="listname">List Name:</label><input type="text" name="listname" id="listname" /><br />
		<input type="submit" name="createlist" value="Create List" />
	</fieldset></form>
</div>
<?php
	if(isset($_SESSION['selectedList']))
	{
		echo '<div id="folderbox"><div class="columnbanner">';
		echo "Folders in ".$currentList->getName().":";
		echo "</div>\n<ul class=\"prof\">";

		printFolder($currentList->getBaseFolder());
		echo "<li></li></ul>\n</div><div id=\"filebox\">\n'";
		echo "<div class=\"columnbanner\">";
		
		if(isset($_SESSION['selectedIPROFolder'])) 
			$folder = new Folder($_SESSION['selectedIPROFolder'], $db);
		else
			$folder = $currentList->getBaseFolder();
			
		echo "Contents of ".$folder->getName().":";
?>
			</div>
			
			<form method="post" action="iprofiles.php"><fieldset>

			<div id="menubar">
				<ul class="prof">
					<li><a href="#" onclick="uploadwin=dhtmlwindow.open('uploadbox', 'div', 'upload', 'Upload File', 'width=350px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); return false">Upload File</a></li>
					<li><a href="#" onclick="newfolderwin=dhtmlwindow.open('newfolderbox', 'div', 'newfolder', 'Create Folder', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Create Folder</a></li>
					<li><a href="#" onclick="document.getElementById('delete').form.submit()">Delete</a>
					<input type="hidden" id="delete" name="delete" value="delete" /></li>
				</ul>
			</div>
			
<?php
			echo "<table width=\"100%\">";
			$folderList = $folder->getFolders();
			$fileList = $folder->getFiles();
			
			foreach($folderList as $key => $folder)
			{
				printTR();
				echo "<td><img src=\"../skins/$skin/img/folder.png\" alt=\"\" /></td>";
				echo "<td><a href=\"iprofiles.php?selectFolder=".$folder->getID()."\">".$folder->getName()."</a></td>";
				echo "<td>".$folder->getDesc()."</td>";
				echo "<td align=\"right\"><input type=\"checkbox\" name=\"folder[".$folder->getID()."]\" /></td>";
				echo "</tr>\n";
			}
			
			foreach($fileList as $key => $file)
			{
				printTR();
				echo "<td><img src=\"../skins/$skin/img/file.png\" alt=\"\" /></td>";
				echo "<td><a href=\"../download.php?id=".$file->getID()."\">".$file->getName()."</a></td>";
				echo "<td>".$file->getDesc()."</td>";
				echo "<td align=\"right\"><input type=\"checkbox\" name=\"file[".$file->getID()."]\" /></td>";
				echo "</tr>\n";
			}
		
			if(count($folderList) + count($fileList) == 0)
				echo "<tr>There are no files or folders in the selected folder</tr>\n";

			echo "</table>";
?>
			</fieldset></form>
		</div>
		<div id="groups" style="clear:both;">
			<form method="post" action="iprofiles.php"><fieldset>
<?php
			$groups = $currentSemester->getGroups();
			echo "<legend>".$currentSemester->getName()."</legend>";
			$groups = groupSort($groups);
			
			foreach($groups as $group)
			{
				echo "<input type=\"checkbox\" name=\"uselist[".$group->getID()."]\"";
				if($group->usesFileList($_SESSION['selectedList']))
					echo " checked=\"checked\"";
				echo " /> ".$group->getName()."<br />";
			}
?>
				<input type="button" value="Select All" onclick="selectAll('uselist')" /><input type="submit" name="updategroups" value="Update Selected Groups" />
			</fieldset></form>
		</div>
		<div id="semesterSelect">
				<form method="get" action="iprofiles.php"><fieldset>
					<select name="semester">
<?php
					$semesters = $db->query("SELECT iID FROM Semesters ORDER BY iID DESC");
					while($row = mysql_fetch_row($semesters))
					{
						$semester = new Semester($row[0], $db);
						echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
					}
?>
					</select>
					<input type="submit" name="selectSemester" value="Select Semester" />
				</fieldset></form>
		</div>
		<div class="window" id="upload">
				<form method="post" action="iprofiles.php" enctype="multipart/form-data"><fieldset>
					<label for="thefile">File:</label><input type="file" name="thefile" id="thefile" /><br />
					<label for="filename">File Name:</label><input type="text" name="filename" id="filename" /><br />
					<label for="filedescription">Description:</label><input type="text" name="filedescription" id="filedescription" /><br />
					This file will be placed in the <?php echo $folder->getName(); ?> folder.<br />
					<input type="submit" name="upload" value="Upload File" />
				</fieldset></form>
		</div>
		<div class="window" id="newfolder">
				<form method="post" action="iprofiles.php"><fieldset>
					<label for="foldername">Folder Name:</label><input type="text" name="foldername" id="foldername" /><br />
					<label for="folderdescription">Description:</label><input type="text" name="folderdescription" id="folderdescription" /><br />
					This folder will be placed in the <?php	echo $folder->getName(); ?> folder.<br />
					<input type="submit" name="create" value="Create Folder" />
				</fieldset></form>
		</div>
<?php
	}
?></div></body></html>
