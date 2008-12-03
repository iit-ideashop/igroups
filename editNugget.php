<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/nugget.php" );
	include_once( "classes/semester.php" );
	include_once( "classes/file.php" );
	include_once( "classes/quota.php" );

	$_SESSION['currentGroup'] = $currentGroup;
	
	$currentQuota = new Quota( $currentGroup, $db );
	$semester = new Semester($_SESSION['selectedSemester'], $db);
	if(!$semester->isActive()){
		die("You cannot edit a nugget from a previous semester.");
	}
	
	function printNuggetForm(){
		global $db;
		$nugget = new Nugget($_GET['nug'], $db, 0);
		
		$authors = $nugget->getAuthors();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		
		print "<form method=\"post\" action=\"nuggets.php\" id=\"redirectForm\"><fieldset>";
		print "<input type=\"hidden\" name=\"nuggetType\" />";
		print "</fieldset></form>";
		print "<div class=\"item\"><strong>Nugget Type/Name:</strong> ";
		//used to print a nugget that is from a prior semester or for viewing purposes only
		$nug = $nugget->getType();
		if($nug == "Code of Ethics")
			$nugprint = "Ethics Statement";
		else if($nug == "Website")
			$nugprint = "Website (optional)";
		else if($nug == "Midterm Report")
			$nugprint = "Midterm Presentation";
		else if($nug == "Team Minutes")
			$nugprint = "Team Minutes (optional)";
		else if($nug == "Final Report")
			$nugprint = "Final Report or Grant Proposal";
		else
			$nugprint = $nug;
		if(isset($style) && $style == "link"){
				
			if($nugget->isDefault()){
				print '<a href="javaScript:nuggetRedirect(\''.$nugget->getType().'\')">'.$nugprint.'</a></div>';
			}
			else{
				print '<a href="javascript:nuggetRedirect(\'Other\')">'.$nugprint.'</a></div>';
			}
			
		}
		else{
			print $nugprint."</div>";
		}
		print '<div class="item"><strong>Description:</strong> '.substr($nugget->getDesc(),0,40).'</div>';
		print '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		print '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0){
			print '<ul class="folderlist">';
			
			foreach($authors as $author){
				
				print '<li>';
				print $author->getFullName();
				print '</li>';
				
			}
			print '</ul></div>';
		}
		else{
			print "There are currently no authors for this nugget.</div>";
		}
		print '<div class="item"><strong>Files:</strong>'.'<br />';
		
		if(count($files)>0){
			print '<ul class="folderlist">';
			foreach($files as $file){
				print '<li>';
				print '<a href="download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp;';
				print '</li>';
			}
			print '</ul></div>';
		}
		else{
			print "There are currently no files for this nugget.</div>";
		}
	}
	
	function printEditableNugget($nugget){
	
		global $db;
		global $currentGroup;
		
		$nugget = new Nugget($nugget, $db, 0);
		
		$authors = $currentGroup->getAllGroupMembers();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		if ($nugget->isPrivate())
			$private = ' checked="checked"';
		else
			$private = '';

		print "<form method=\"post\" action=\"editNugget.php?edit=true&amp;nugID=".$nugget->getID()."\" id=\"myForm\" enctype=\"multipart/form-data\"><fieldset>";
		//if the nugget is non-default than allow the option to edit the title
		if(!$nugget->isDefault()){
			print "<div class=\"item\"><label for=\"type\">Nugget Type/Name:</label> "."<input type=\"text\" name=\"type\" id=\"type\" value=\"".$nugget->getType()."\" />"."</div>";
		}
		else{
			print "<div class=\"item\"><strong>Nugget Type/Name:</strong> ".$nugget->getType()."</div>";
		}
		
		print "<div class=\"item\"><label for=\"private\">Make Private?:</label>&nbsp;<input type=\"checkbox\" id=\"private\" name=\"private\"$private /><br />(If selected, this nugget will only be viewable by those in your group and IPRO Staff)</div>";

		//print description box
		print "<div class=\"item\"><label for=\"description\">Description: </label><br /><textarea cols=\"40\" rows=\"3\" id=\"description\" name=\"description\">".$nugget->getDesc()."</textarea></div>";
		
		//print date created
		print "<div class=\"item\"><strong>Date Created: </strong>".$nugget->getDate()."</div>";

?>
		<table cellpadding="1" cellspacing="2" class="edit_author">
		<tr class="item" style="font-weight:bold">
		<td>Authors:</td><td>Add Author&nbsp;</td><td>Delete</td><td></td><td>Add Author</td><td>Delete</td></tr>
		
<?php
		//print the author table
		//print each member of the group
		$count = 0;
		print "<tr>";
		if (count($authors) > 0) {
		foreach($authors as $author){
			if($count == 2){
				print "</tr><tr>";
				$count = 0;
			}
			//if that member is an author set his/her status to checked
			if($nugget->isAuthor($author->getID())){
				$isAuthor = 'checked';
			}else{
				$isAuthor = '';
			}
			//print the author name
			print "<td>".$author->getFullName()."</td>";
			
			if($isAuthor == 'checked'){
				
				print "<td align=\"center\">Added</td>";
				print "<td align=\"center\">&nbsp;&nbsp;&nbsp;<input type =\"checkbox\" name=\"authorToDelete[]\" value= \"".$author->getID()."\" />&nbsp;&nbsp;&nbsp;</td>";
			}else{
				print "<td align=\"center\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"authorToAdd[]\" value= \"".$author->getID()."\"".$isAuthor." />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
				print "<td></td>";
				
			}
			$count++;
		}}
		
		print "</tr></table>";
		print "<div class = \"item\">";
		if(count($files) > 0){
			
			?>
			
			<table cellpadding="5" cellspacing="0" class="edit_author">
			<tr>
			<td><div class="item"><strong>Files:</strong></div></td><td><div class="item"><strong>Delete</strong></div></td></tr>
			<?php
			//print each file and its respective checkbox

			foreach($files as $file){

				print '<tr>';
				print '<td>';
				print "<a href=\"download.php?id=".$file->getID()."\">".$file->getNameNoVer()."</a>&nbsp;";
				print '</td>';
				print '<td>';
				print "<input type=\"checkbox\" name=\"fileToDelete[]\" value = \"".$file->getID()."\" />";
				print '</td>';
				print '</tr>';
			}
			print '</table></div>';
			
			//ends the list of files
		}
		else{
			
			print "<p>There are currently no files for this nugget.</p>";
			
		}
		
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
		<?php print "<p>Option 2: <a href=\"#\" onclick=\"javascript:addFilesFromNugget();getUpdates(".$nugget->getID().")\">Import files from iGroups</a></p>";?>
		<input type="hidden" name="filenames" />
		<input type="hidden" name="descriptions" />
		<input type="hidden" name="igroupsRedirect" value="0" />
		These files will be placed in the Nugget File folder of your files.<br />
<?PHP
		//Save change button
		print "<div class=\"item\">";
		print "<input type=\"button\" onclick=\"javascript:getUpdates(".$nugget->getID().");\" value=\"Save Changes\" />";
		print "<input type=\"button\" onclick=\"javascript:deleteNugget();\" value=\"Delete this Nugget\" />";
?>
		</div>
		<input type="hidden" name="nuggetDelete" value="0" />
		<input type="hidden" name="toUpdate" value="0" />
<?php
		print "<input type=\"hidden\" name=\"nuggetType\" value=\"".$_SESSION['nuggetType']."\" />";
		$nugId = $nugget->getID();
		print "<input type=\"hidden\" name=\"nuggetID\" value=\"".$nugId."\" />";
		print "<input type=\"hidden\" name=\"deleteMe\" value=\"no\" />";
?>
		</fieldset></form>
<?php
	}
?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Edit Nugget</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
	<script type="text/javascript">
	<!--
		function addFile(nuggetID) {
			document.getElementById("add-file").style.visibility="visible";
		}
		
		function addFiles(nuggetID){
			getUpdates(nuggetID);
		}

		function getUpdates(value) {
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

		function fileAdd(num){
			var div = document.createElement("div");
			div.className = "item";
			div.id = "file"+num;
			div.innerHTML = 
			"<label for=\"thefile"+(num+1)+"\">File "+(num+1)+": </label><input type=\"file\" id=\"thefile"+(num+1)+"\" name='thefile[]' onchange='fileAdd("+(num+1)+");' /><br /><label for=\"filename"+(num+1)+"\">File Name "+(num+1)+":</label><input type='text' id=\"filename"+(num+1)+"\" name='fileName' /><br /><label for=\"filedescription"+(num+1)+"\">File Description "+(num+1)+": </label><input type=\"text\" name=\"fileDescription\" id=\"filedescription"+(num+1)+"\" /><br />";
			document.getElementById("files").appendChild(div);
		}
		function nuggetRedirect(nugget){
			form = document.getElementById("redirectForm");
			form.nuggetType.value= nugget;
			form.submit();
		}
			
		
		function deleteNugget(){
			var form = document.getElementById("myForm");
			form.deleteMe.value="yes";
			form.submit();
		}

		function doProcess(form){
			form.update.value="false";
			form.submit();
		}

		function addFilesFromNugget(){

			document.getElementById("myForm").igroupsRedirect.value = true;
		}
	//-->
	</script>
	</head>
<body>
<?php
require("sidebar.php");
print "<div id=\"content\"><h1>Edit Nugget</h1>";
	if(isset ($_POST['published'])){
		$nugget = new Nugget($_POST['nuggetID'], $db, 0);
		$nugget->publish();
		if($_POST['priv'] != 'false'){
			$nugget->makePrivate();
		}
		$nugID = $nugget->getID();
		print 	"<script type=\"text/javascript\">
					<!--
					window.location = 'viewNugget.php?nug=$nugID'
					//-->
					</script>";
	}
	if(isset ($_POST['toUpdate'])){
		//check each field of the form and make the appropriate changes
		$nugget = new Nugget($_POST['nuggetID'], $db, 0);
		if(isset($_POST['type'])){
			$nugget->setType($_POST['type']);
		}
		if (isset($_POST['private']))
			$nugget->makePrivate();
		else
			$nugget->makePublic();

		$nugget->setDesc($_POST['description']);
		$curAuthors = $nugget->getAuthorIDs();
		if(isset($_POST['authorToAdd'])){
			//construct nugget author list
			foreach ($_POST['authorToAdd'] as $id => $val){
				
				if(!in_array($val, $curAuthors)){
					
					if($nugget->addAuthor($val)){
						$message = "Successfully added author\n";
					}
					else
						$message = "Error adding author\n";
				}
				else{
					$message = "Some authors could not be added as they are already in the author list.";
				}
			}
		}
		if(isset($_POST['authorToDelete'])){
			//construct nugget author list
			foreach ($_POST['authorToDelete'] as $id => $val){
				if(in_array($val, $curAuthors)){
					if($nugget->removeAuthor($val)){
						$message = "Successfully removed author\n";
					}
					else
						$message = "Error adding author\n";
				}else{
					$message = "Some authors could not be removed as they are not in the author list.";
				}
			}
		}
		
		if(isset($_POST['fileToDelete'])){
			$ID = $_POST['nuggetID'];
		
			foreach ($_POST['fileToDelete'] as $file){
				
				if($nugget->removeFile($file)){
					$message = "Successfully removed file";
				}
				else
					$message = "Error removing file";
			}
		}
		
		if(isset($_POST['filenames']) && $_POST['filenames']!=""){
			if ( !$currentQuota ) {
			$currentQuota = createQuota( $currentGroup, $db );
			}
			$filenames = explode("///", $_POST['filenames']);
			$description = explode("///", $_POST['descriptions']);
			$loop = 0;

			foreach($_FILES['thefile']['error'] as $key=>$error){
					
				if ( $error == UPLOAD_ERR_OK ) {
					if ( $currentQuota->checkSpace( filesize( $_FILES['thefile']['tmp_name'][$key] ) ) ) {
						$currentQuota->increaseUsed( filesize( $_FILES['thefile']['tmp_name'][$key] ) );
						$currentQuota->updateDB();
						//I need to get the current groups home folder
						$file = createFile($filenames[$loop], $description[$loop],0,$currentUser->getID(), $_FILES['thefile']['name'][$key],$currentGroup, $_FILES['thefile']['tmp_name'][$key], $_FILES['thefile']['type'][$key], 0, $db );
						if(!$file)
							$message = "Upload error";
						else
						{
							$message="File successfully uploaded";
							//also add information to nugget
							$db->igroupsQuery("INSERT INTO nuggetFileMap (iNuggetID, iFileID) VALUES ('".$nugget->getID()."', '".$file->getID()."')");
						}
					}	
					else {
						//$currentQuota->sendWarning(1);
						$message="ERROR: Not enough space for file";
					}
				}
			}
		}
		if($primary){
			$nugget->updateDB($_POST['primary']);
		}else{
			$nugget->updateDBNoPrimary();
		}

		if($_POST['igroupsRedirect']){
			print "</script>";
			print "<script type=\"text/javascript\">
				<!--
				window.location='addFilesToNugget.php?nugget=".$nugget->getID()."'
				//-->
				</script>";
		}
															//
	}
	
	if(isset ($_POST['deleteMe']) && $_POST['deleteMe'] == "yes"){
		removeNugget($_POST['nuggetID']);
		print 	'<script type="text/javascript">
					<!--
					window.location="nuggets.php"
					//-->
					</script>';
	}
	if(isset ($_GET['nug'])){
		//print nugget form
		printNuggetForm();
		$id = $_GET['nug'];
		print "<a href=\"editNugget.php?edit=true&amp;nugID=$id\">Edit this Nugget</a>";
	}else{
		if(isset ($_GET['edit']) && $_GET['edit'] == true){
			printEditableNugget($_GET['nugID']);
		}else{
		//an error has occured redirect
			print 	'<script type="text/javascript">
					<!--
					window.location = "index.php"
					//-->
					</script>';
		}
	}
	
	
?>
</div></body>
</html>
