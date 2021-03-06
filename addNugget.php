<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/nugget.php');
	include_once('classes/semester.php');
	include_once('classes/file.php');
	include_once('classes/quota.php');
	include_once('nuggetTypes.php');
	
	global $_DEFAULTNUGGETS;
	$semester = new Semester($_SESSION['selectedSemester'], $db);

	$currentQuota = new Quota($currentGroup, $db);
	if(!$semester->isActive())
		errorPage('Invalid Semester', 'You cannot add a nugget to a previous semester.', 403);

	function peopleSort($array) 
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}

	//------Start of Code for Form Processing-----------------------//
	if(isset($_POST['newName']))
	{
		echo "<script type=\"text/javascript\">";
		//retrieve user input
		$name = $_POST['newName'];
		$description = $_POST['description'];

		//create the nugget
		$id = newNugget($name, $description, $currentGroup);

		//retrieve a class of the recently created nugget
		$nugget = new Nugget($id, $db, 0);

		//set privacy
		if(isset($_POST['private']))
			$nugget->makePrivate();

		//if the user added authors, add them
		if(isset($_POST['authorToAdd']) && count($_POST['authorToAdd']) > 0)
			foreach($_POST['authorToAdd'] as $author)
				$nugget->addAuthor($author);
		
		//if the users added a file, add it to the nugget	
		if(isset($_POST['filenames']) && $_POST['filenames'] != '')
		{
			//check the group quota
			if(!$currentQuota)
				$currentQuota = createQuota($currentGroup, $db);

			//if the quota checks out make sure upload was ok
			$filenames = explode('///', $_POST['filenames']);
			$description = explode('///', $_POST['descriptions']);
			$loop = 0;
			foreach($_FILES['thefile']['error'] as $key => $error)
			{
				if($error == UPLOAD_ERR_OK)
				{
					if($currentQuota->checkSpace(filesize($_FILES['thefile']['tmp_name'][$key])))
					{
						$currentQuota->increaseUsed(filesize($_FILES['thefile']['tmp_name'][$key]));
						$currentQuota->updateDB();
	
						//create the file
						$file = createFile($filenames[$loop], $description[$loop], 0, $currentUser->getID(), $_FILES['thefile']['name'][$key], $currentGroup, $_FILES['thefile']['tmp_name'][$key], $_FILES['thefile']['type'][$key], 0, $db);
						if(!is_object($file))
						{
							if($file == 1)
								$message = 'Error: The files directory is full. The IPRO Office has been notified of this problem.';
							else
								$message = 'Error saving file. Please try again.';
							break;
						}
	
						//also add information to nugget
						$db->query("INSERT INTO nuggetFileMap (iNuggetID, iFileID) VALUES ('".$id."', '".$file->getID()."')");
					}
					else //if they ran out of room send a warning
						$currentQuota->sendWarning(1);
				}
				$loop++;
			}
		}
		if(isset($_POST['igroupFiles']))
		{
			$files = explode(',', $_POST['igroupFiles']);
			foreach($files as $file)
				$db->query("INSERT INTO nuggetFileMap (iNuggetID, iFileID) VALUES('".$id."', '".$file."')");
		}	
		//redirect to view/edit
		$nugID = $nugget->getID();
		if(!$_POST['igroupsRedirect'])
		{
			echo "</script>\n";
			echo "<script type=\"text/javascript\">\n";
			echo "<!--\n";
			echo "\twindow.location = 'editNugget.php?nug=$nugID'\n";
			echo "//-->\n";
			echo "</script>\n";
		}
		else
		{
			echo "</script>\n";
			echo "<script type=\"text/javascript\">\n";
			echo "<!--\n";
			echo "\twindow.location='addFilesToNugget.php?nugget=$nugID'\n";
			echo "//-->\n";
			echo "</script>\n";
		}
	}
	//------End of Code for Form Processing-------------------------//
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Group Files</title>
<script type="text/javascript">
//<![CDATA[
	function submitForm(){
		var loop = 0;
		var files = "";
		var descriptions = "";
		theform = document.getElementById("myForm");
		fileNames = document.getElementsByName("fileName");
		fileDescriptions = document.getElementsByName("fileDescription");
		while(loop < fileNames.length-1){
			files = files + fileNames[loop].value+"///";
			descriptions = descriptions + fileDescriptions[loop].value+"///";
			loop++;	

		}
		theform.filenames.value=files;
		theform.descriptions.value=descriptions;
		theform.submit();
	}
	
	function fileAdd(num) {
			var div = document.createElement('div');
			div.className = "item";
			div.id = "file"+num;
			div.innerHTML = 
				"<label for=\"thefile"+(num+1)+"\">File "+(num+1)+": </label><input type=\"file\" id=\"thefile"+(num+1)+"\" name='thefile[]' onchange='fileAdd("+(num+1)+");' /><br /><label for=\"filename"+(num+1)+"\">File Name "+(num+1)+":</label><input type='text' id=\"filename"+(num+1)+"\" name='fileName' /><br /><label for=\"filedescription"+(num+1)+"\">File Description "+(num+1)+": </label><input type=\"text\" name=\"fileDescription\" id=\"filedescription"+(num+1)+"\" /><br />";
			document.getElementById('files').appendChild(div);
		
	}

	function addFilesFromNugget(){
		document.getElementById("myForm").igroupsRedirect.value = true;
		submitForm();
	}
//]]>	
</script>
</head>
<body>
<?php
	require('htmlhead.php');
	echo "<h1>Create new Nugget</h1>\n";

	//only works if the url contained a nugget type
	if(isset($_GET['files']))
	{
		$files = explode(',', $_GET['files']);
		$filesArray = array();
		foreach($files as $file)
			$filesArray[] = new File($file, new dbConnection(), 0);
	}
	if(isset($_GET['type']))
	{
?>
		<form method="post" action="addNugget.php" id="myForm" enctype="multipart/form-data"><fieldset>

		<div class="item"><strong>Nugget Type/Name:</strong>
<?php
		if(!in_array($_GET['type'], $_DEFAULTNUGGETS))
			echo "<input type=\"text\" name=\"newName\" value=\"Other\" />";
		else
		{
			$type = $_GET['type'];
			echo "<input type=\"hidden\" name=\"newName\" value=\"$type\" />";
			echo $type;
		}
?>
		</div>
		<div class="item"><label for="private">Make Private?:</label>&nbsp;<input type="checkbox" name="private" id="private" /><br />(If selected, this nugget will only be viewable by those in your group and IPRO Staff)</div>
		<div class="item"><label for="description">Description:</label><br /><textarea cols="40" rows="3" name="description" id="description"></textarea></div>
		<div class="item">
		<table cellpadding="1" cellspacing="1" style="border-style: none" id="edit_author">
		<tr>
		<td><div class="item" style="font-weight: bold">Authors:</div></td><td><div class="item" style="font-weight: bold">Add Author</div></td><td></td><td><div class="item" style="font-weight: bold">Add Author</div></td>
		</tr>
<?php
		$authors = peopleSort($currentGroup->getAllGroupMembers());
		if(count($authors) > 0)
		{
			$count = 0;
			echo '<tr>';
			foreach($authors as $author)
			{
				if($count == 2)
				{
					echo "</tr>\n<tr>";
					$count = 0;
				}
				$name = $author->getFullName();
				$id = $author->getID();
				echo "<td>$name</td><td align=\"center\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"authorToAdd[]\" value=\"$id\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
				$count++;
			}
			echo "</tr>\n";
		}
?>
		</table>
		</div>
<?php
		if(isset($filesArray))
		{
			echo "The following files have been marked for addition:<br />\n";
			foreach($filesArray as $file)
				echo $file->getName()."<br />\n";
			$files = $_GET['files'];
			echo "<input type=\"hidden\" name=\"igroupFiles\" value=\"$files\" />";
		}
?>
		<div id="files">
		<h4>Add Files</h4>
		<p>Option 1: Upload new files</p>
		<div id="file1" class="item">
		<label for="thefile1">File 1:</label><input type="file" name="thefile[]" id="thefile1" onchange="javascript:fileAdd(1);" /><br />
		<label for="filename1">File Name 1:</label><input type="text" name="fileName" id="filename1" /><br />
		<label for="filedescription1">Description 1:</label><input type="text" name="fileDescription" id="filedescription1" /><br />
		</div>
		</div>
		<p>Option 2: <a href="#" onclick="javascript:addFilesFromNugget();">Import files from iGroups</a></p>
		<input type="hidden" name="filenames" />
		<input type="hidden" name="descriptions" />
		<input type="hidden" name="igroupsRedirect" value="0" />
		These files will be placed in the Nugget File folder of your files.<br />
		<br />
		<input type="button" value="Create Nugget" onclick="javascript:submitForm();" />
		</fieldset></form>
<?php
	}
	else
	{
		echo "<script type=\"text/javascript\">\n";
		echo "<!--\n";
		echo "\twindow.location = 'index.php';\n";
		echo "//-->\n";
		echo "</script>\n";
	}
?>
<?php 
require('htmlcontentfoot.php');
?>
</body></html>
