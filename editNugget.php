<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/nugget.php');
	include_once('classes/semester.php');
	include_once('classes/file.php');
	include_once('classes/quota.php');

	$_SESSION['currentGroup'] = $currentGroup;
	
	$currentQuota = new Quota($currentGroup, $db);
	$semester = new Semester($_SESSION['selectedSemester'], $db);
	if(!$semester->isActive())
		errorPage('Invalid Semester', 'You cannot edit a nugget from a previous semester.', 403);
	
	if(isset($_GET['nugID']) && (!is_numeric($_GET['nugID']) || $_GET['nugID'] < 1))
		errorPage('Invalid Nugget ID', 'That nugget ID is not valid', 400);
	
	if($_GET['approve'])
	{
		if(!$currentUser->isGroupAdministrator($currentGroup))
			errorPage('Credentials Required', 'Only group administrators may approve nuggets', 403);
		else if(!isset($_GET['nugID']))
			errorPage('No Nugget Selected', 'No nugget ID was selected', 400);
		$nugget = new Nugget($_GET['nugID'], $db, 0);
		if($nugget->isVerified())
			errorPage('Nugget Already Approved', 'That nugget has already been approved', 400);
		if(!$nugget->verify($currentUser))
			errorPage('Could Not Approve Nugget', "The nugget was not approved. Please contact $contactemail with the information below immediately.", 500);
	}
	
	function printNuggetForm()
	{
		global $db;
		$nugget = new Nugget($_GET['nug'], $db, 0);
		
		$authors = $nugget->getAuthors();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		
		echo "<form method=\"post\" action=\"nuggets.php\" id=\"redirectForm\"><fieldset>\n";
		echo "<input type=\"hidden\" name=\"nuggetType\" />\n";
		echo "</fieldset></form>\n";
		echo "<div class=\"item\"><strong>Nugget Type/Name:</strong> ";
		//used to print a nugget that is from a prior semester or for viewing purposes only
		$nug = $nugget->getType();
		if($nug == 'Code of Ethics')
			$nugprint = 'Ethics Statement';
		else if($nug == 'Website')
			$nugprint = 'Website (optional)';
		else if($nug == 'Midterm Report')
			$nugprint = 'Midterm Presentation';
		else if($nug == 'Team Minutes')
			$nugprint = 'Team Minutes (optional)';
		else if($nug == 'Final Report')
			$nugprint = 'Final Report or Grant Proposal';
		else
			$nugprint = $nug;
		if(isset($style) && $style == 'link')
		{
			if($nugget->isDefault())
				echo '<a href="javascript:nuggetRedirect(\''.$nugget->getType().'\')">'.$nugprint.'</a></div>';
			else
				echo '<a href="javascript:nuggetRedirect(\'Other\')">'.$nugprint.'</a></div>';
		}
		else
			echo "$nugprint</div>";
		echo '<div class="item"><strong>Description:</strong> '.substr($nugget->getDesc(),0,40).'</div>';
		echo '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		echo '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0)
		{
			echo '<ul class="folderlist">';
			
			foreach($authors as $author){
				
				echo '<li>';
				echo $author->getFullName();
				echo "</li>\n";
				
			}
			echo '</ul></div>';
		}
		else
			echo "There are currently no authors for this nugget.</div>";
		echo '<div class="item"><strong>Files:</strong><br />';
		
		if(count($files) > 0)
		{
			echo '<ul class="folderlist">';
			foreach($files as $file)
			{
				echo '<li>';
				echo '<a href="download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp;';
				echo "</li>\n";
			}
			echo '</ul></div>';
		}
		else
			echo "There are currently no files for this nugget.</div>";
	}
	
	function printEditableNugget($nugget)
	{
		global $db, $currentUser, $currentGroup;
		
		$nugget = new Nugget($nugget, $db, 0);
		
		$authors = $currentGroup->getAllGroupMembers();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		if($nugget->isPrivate())
			$private = ' checked="checked"';
		else
			$private = '';

		echo "<form method=\"post\" action=\"editNugget.php?edit=true&amp;nugID={$nugget->getID()}\" id=\"myForm\" enctype=\"multipart/form-data\"><fieldset>";
		//if the nugget is non-default than allow the option to edit the title
		if(!$nugget->isDefault())
			echo "<div class=\"item\"><label for=\"type\">Nugget Type/Name:</label> "."<input type=\"text\" name=\"type\" id=\"type\" value=\"{$nugget->getType()}\" />"."</div>";
		else
			echo "<div class=\"item\"><strong>Nugget Type/Name:</strong> ".$nugget->getType();
		
		if(($nugget->getType() == 'Abstract' || $nugget->getType() == 'Poster') && count($files) > 0)
		{
			$s = ((count($files) > 1) ? 's' : '');
			$are = ((count($files) > 1) ? 'are' : 'is');
			$their = ((count($files) > 1) ? 'their' : 'its');
			if($currentUser->isGroupAdministrator($currentGroup) && !$nugget->isVerified())
				echo "<h1>ATTENTION FACULTY MEMBER</h1>\n<p>The IPRO Office will print this nugget free of charge for IPRO Day. You must approve this nugget before the IPRO Office will do so. In the event of an error in the uploaded file$s, e.g. a typo or a low-quality image, the IPRO Office will NOT re-print. Please check the file$s for errors before approving this nugget for printing.</p>\n<p>By clicking the link below, you verify that you have checked the file$s contained in this nugget, and that you agree that the file$s $are, in $their present state$s, ready to be printed by the IPRO office.</p>\n<p><a href=\"editNugget.php?approve=1&amp;edit=true&amp;nugID={$nugget->getID()}\">Approve this nugget for printing</a></p>\n";
			else if(!$nugget->isVerified())
				echo "<p>This nugget must be approved by your IPRO faculty member before it can be printed by the IPRO office.</p>\n";
			else
				echo "<p><b>Approved</b> by {$nugget->whoVerified()->getFullName()} at {$nugget->whenVerified()}.</p>\n";
		}
		echo "</div>\n";
		
		echo "<div class=\"item\"><label for=\"private\">Make Private?:</label>&nbsp;<input type=\"checkbox\" id=\"private\" name=\"private\"$private /><br />(If selected, this nugget will only be viewable by those in your group and IPRO Staff)</div>";

		//print description box
		echo "<div class=\"item\"><label for=\"description\">Description: </label><br /><textarea cols=\"40\" rows=\"3\" id=\"description\" name=\"description\">".$nugget->getDesc()."</textarea></div>\n";
		
		//print date created
		echo "<div class=\"item\"><strong>Date Created: </strong>".$nugget->getDate()."</div>\n";

?>
		<table cellpadding="1" cellspacing="2" class="edit_author">
		<tr class="item" style="font-weight:bold">
		<td>Authors:</td><td>Add Author&nbsp;</td><td>Delete</td><td></td><td>Add Author</td><td>Delete</td></tr>
		
<?php
		//print the author table
		//print each member of the group
		$count = 0;
		echo "<tr>";
		if(count($authors) > 0)
		{
			foreach($authors as $author)
			{
				if($count == 2)
				{
					echo "</tr>\n<tr>";
					$count = 0;
				}
				//if that member is an author set his/her status to checked
				if($nugget->isAuthor($author->getID()))
					$isAuthor = 'checked';
				else
					$isAuthor = '';

				//print the author name
				echo "<td>".$author->getFullName()."</td>";
			
				if($isAuthor == 'checked')
				{
				
					echo "<td align=\"center\">Added</td>";
					echo "<td align=\"center\">&nbsp;&nbsp;&nbsp;<input type =\"checkbox\" name=\"authorToDelete[]\" value= \"".$author->getID()."\" />&nbsp;&nbsp;&nbsp;</td>";
				}
				else
				{
					echo "<td align=\"center\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"authorToAdd[]\" value= \"".$author->getID()."\"".$isAuthor." />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
					echo "<td></td>";
				
				}
				$count++;
			}
		}
		
		echo "</tr></table>\n";
		echo "<div class=\"item\">";
		if(count($files) > 0){
			
			?>
			
			<table cellpadding="5" cellspacing="0" class="edit_author">
			<tr>
			<td><div class="item"><strong>Files:</strong></div></td><td><div class="item"><strong>Delete</strong></div></td></tr>
			<?php
			//print each file and its respective checkbox

			foreach($files as $file)
				echo "<tr><td><a href=\"download.php?id={$file->getID()}\">{$file->getNameNoVer()}</a></td><td><input type=\"checkbox\" name=\"fileToDelete[]\" value=\"{$file->getID()}\" /></td></tr>\n";
			echo '</table></div>';
			
			//ends the list of files
		}
		else
			echo "<p>There are currently no files for this nugget.</p>";
?>
		<div class="item"><strong>Add Files:</strong></div>
		<p>Option 1: Upload new files</p>
		<div id="files">
		<div id="file1" class="item">
		<label for="thefile1">File 1: </label><input type="file" name="thefile[]" id="thefile1" onchange="javascript:fileAdd(1);" /><br />
		<label for="filename1">File Name: </label><input type="text" name="fileName" id="filename1" /><br />
		<label for="filedescription1">Description: </label><input type="text" name="fileDescription" id="filedescription1" /><br />
		</div>
		</div>
		<?php echo "<p>Option 2: <a href=\"#\" onclick=\"javascript:addFilesFromNugget();getUpdates(".$nugget->getID().")\">Import files from iGroups</a></p>";?>
		<input type="hidden" name="filenames" />
		<input type="hidden" name="descriptions" />
		<input type="hidden" name="igroupsRedirect" value="0" />
		These files will be placed in the Nugget File folder of your files.<br />
<?php
		//Save change button
		echo "<div class=\"item\">";
		echo "<input type=\"button\" onclick=\"javascript:getUpdates(".$nugget->getID().");\" value=\"Save Changes\" />";
		echo "<input type=\"button\" onclick=\"javascript:deleteNugget();\" value=\"Delete this Nugget\" />";
?>
		</div>
		<input type="hidden" name="nuggetDelete" value="0" />
		<input type="hidden" name="toUpdate" value="0" />
<?php
		echo "<input type=\"hidden\" name=\"nuggetType\" value=\"".$_SESSION['nuggetType']."\" />";
		$nugId = $nugget->getID();
		echo "<input type=\"hidden\" name=\"nuggetID\" value=\"".$nugId."\" />";
		echo "<input type=\"hidden\" name=\"deleteMe\" value=\"no\" />";
?>
		</fieldset></form>
<?php
	}
	
	//------Start XHTML Output--------------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Edit Nugget</title>
<script type="text/javascript">
//<![CDATA[
	function addFile(nuggetID)
	{
		document.getElementById("add-file").style.visibility="visible";
	}
	
	function addFiles(nuggetID)
	{
		getUpdates(nuggetID);
	}

	function getUpdates(value)
	{
		form = document.getElementById("myForm");
		form.toUpdate.value = value;
		var loop = 0;
		var files ="";
		var descriptions = "";
		fileNames = document.getElementsByName("fileName");
		fileDescriptions = document.getElementsByName("fileDescription");
		while(loop < fileNames.length-1){
			files = files+"///"+fileNames[loop].value;
			descriptions = descriptions +"///"+fileDescriptions[loop].value;
			loop++;
		}
		form.filenames.value = files;
		form.descriptions.value = descriptions;
		form.submit();
	}

	function fileAdd(num)
	{
		var div = document.createElement("div");
		div.className = "item";
		div.id = "file"+num;
		div.innerHTML = "<label for=\"thefile"+(num+1)+"\">File "+(num+1)+": </label><input type=\"file\" id=\"thefile"+(num+1)+"\" name='thefile[]' onchange='fileAdd("+(num+1)+");' /><br /><label for=\"filename"+(num+1)+"\">File Name "+(num+1)+":</label><input type='text' id=\"filename"+(num+1)+"\" name='fileName' /><br /><label for=\"filedescription"+(num+1)+"\">File Description "+(num+1)+": </label><input type=\"text\" name=\"fileDescription\" id=\"filedescription"+(num+1)+"\" /><br />";
		document.getElementById("files").appendChild(div);
	}
	
	function nuggetRedirect(nugget)
	{
		form = document.getElementById("redirectForm");
		form.nuggetType.value= nugget;
		form.submit();
	}
		
	
	function deleteNugget()
	{
		var form = document.getElementById("myForm");
		form.deleteMe.value="yes";
		form.submit();
	}

	function doProcess(form)
	{
		form.update.value="false";
		form.submit();
	}

	function addFilesFromNugget()
	{
		document.getElementById("myForm").igroupsRedirect.value = true;
	}
//]]>
</script>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\"><h1>Edit Nugget</h1>";
	if(isset($_POST['published']))
	{
		$nugget = new Nugget($_POST['nuggetID'], $db, 0);
		$nugget->publish();
		if($_POST['priv'] != 'false')
			$nugget->makePrivate();
		$nugID = $nugget->getID();
		echo "<script type=\"text/javascript\">\n//<![CDATA[\n\twindow.location = 'viewNugget.php?nug=$nugID'\n//]]>\n</script>";
	}
	if(isset ($_POST['toUpdate']))
	{
		//check each field of the form and make the appropriate changes
		$nugget = new Nugget($_POST['nuggetID'], $db, 0);
		if(isset($_POST['type']))
			$nugget->setType($_POST['type']);
		if(isset($_POST['private']))
			$nugget->makePrivate();
		else
			$nugget->makePublic();

		$nugget->setDesc($_POST['description']);
		$curAuthors = $nugget->getAuthorIDs();
		if(isset($_POST['authorToAdd']))
			foreach ($_POST['authorToAdd'] as $id => $val)
				$message = (in_array($val, $curAuthors) ? "Some authors could not be added as they are already in the author list." : ($nugget->addAuthor($val) ? "Successfully added author\n" : "Error adding author\n"));
		if(isset($_POST['authorToDelete']))
			foreach ($_POST['authorToDelete'] as $id => $val)
				$message = (in_array($val, $curAuthors) ? ($nugget->removeAuthor($val) ? "Successfully removed author\n" : "Error adding author\n") : "Some authors could not be removed as they are not in the author list.");
		
		if(isset($_POST['fileToDelete']))
		{
			$ID = $_POST['nuggetID'];
			foreach ($_POST['fileToDelete'] as $file)
				$message = ($nugget->removeFile($file) ? 'Successfully removed file' : 'Error removing file');
		}
		
		if(isset($_POST['filenames']) && $_POST['filenames'] != '')
		{
			if(!$currentQuota)
				$currentQuota = createQuota( $currentGroup, $db );
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
						//I need to get the current groups home folder
						$file = createFile($filenames[$loop], $description[$loop],0,$currentUser->getID(), $_FILES['thefile']['name'][$key],$currentGroup, $_FILES['thefile']['tmp_name'][$key], $_FILES['thefile']['type'][$key], 0, $db);
						if(!$file)
							$message = "Upload error";
						else
						{
							$message="File successfully uploaded";
							//also add information to nugget
							$db->query("INSERT INTO nuggetFileMap (iNuggetID, iFileID) VALUES ('".$nugget->getID()."', '".$file->getID()."')");
						}
					}	
					else
						$message = 'ERROR: Not enough space for file';
				}
			}
		}
		if($primary)
			$nugget->updateDB($_POST['primary']);
		else
			$nugget->updateDBNoPrimary();

		if($_POST['igroupsRedirect'])
		{
			echo "</script>";
			echo "<script type=\"text/javascript\">
				<!--
				window.location='addFilesToNugget.php?nugget=".$nugget->getID()."'
				//-->
				</script>";
		}
	}
	
	if(isset($_POST['deleteMe']) && $_POST['deleteMe'] == "yes")
	{
		removeNugget($_POST['nuggetID']);
		echo 	'<script type="text/javascript">
					<!--
					window.location="nuggets.php"
					//-->
					</script>';
	}
	if(isset($_GET['nug']))
	{
		//print nugget form
		printNuggetForm();
		$id = $_GET['nug'];
		echo "<a href=\"editNugget.php?edit=true&amp;nugID=$id\">Edit this Nugget</a>";
	}
	else
	{
		if(isset ($_GET['edit']) && $_GET['edit'] == true)
			printEditableNugget($_GET['nugID']);
		else
		{
			//An error has occurred; redirect
			echo 	'<script type="text/javascript">
					<!--
					window.location = "index.php"
					//-->
					</script>';
		}
	}
?>
</div></body></html>
