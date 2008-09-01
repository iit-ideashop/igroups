<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );	
	include_once( "classes/grouppicture.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Pictures</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		.picture-container {
			display:inline;
		}
	</style>
	<script type="text/javascript">
<!--
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
	print "<div id=\"content\"><div id=\"topbanner\">";
	print $currentGroup->getName()."</div>";
	if ( isset( $_POST['addpic'] ) ) {
		if ( $_FILES['picture']['error'] == UPLOAD_ERR_OK  && @getimagesize($_FILES['picture']['tmp_name']) && @is_uploaded_file($_FILES['picture']['tmp_name']) && ($_FILES['picture']['type'] == 'image/gif' || $_FILES['picture']['type'] == 'image/jpeg' || $_FILES['picture']['type'] == 'image/bmp' || $_FILES['picture']['type'] == 'image/x-windows-bmp' || $_FILES['picture']['type'] == 'image/png' || $_FILES['picture']['type'] == 'image/pjpeg')) {
			$pic = createGroupPicture( $_FILES['picture']['name'], $_POST['title'], $currentGroup, $db );
			move_uploaded_file($_FILES['picture']['tmp_name'], $pic->getDiskName() );
?>
			<script type="text/javascript">
				showMessage("Picture successfully added");
			</script>
<?php
		}
	}
	
	if ( isset( $_POST['deletepics'] ) ) {
		if (isset($_POST['picture'])) {
		foreach( $_POST['picture'] as $picid => $val ) {
			$pic = new GroupPicture( $picid, $db );
			$pic->delete();
		}
		
?>
		<script type="text/javascript">
			showMessage("Selected pictures successfully deleted");
		</script>
<?php
		}
	}
?>
	<form method="post" action="grouppictures.php"><fieldset>
<?php
	$pictures = $currentGroup->getGroupPictures();
	if(count($pictures) > 0) {
			print "<table>";
			$i=false;
			if (isset($_GET['start']) && is_numeric($_GET['start']) && ($_GET['start'] % 6 == 0) && ($_GET['start'] < count($pictures)))
				$start = $_GET['start'];
			else
				$start = 0;
			if ((count($pictures) - $start) <= 6)
				$end = count($pictures);
			else
				$end = $start + 6;
			$start2 = $start + 1;
			if (count($pictures) > 0)
				print "<tr><th colspan=\"2\">Viewing Pictures {$start2} - {$end}</th></tr><br />";
			for ($j = $start; $j<$end; $j++) {
				if ( !$i )
					print "<tr>";
				print "<td>";
				print "<img width=\"300\" src=\"http://igroups.iit.edu/".$pictures[$j]->getRelativeName()."\" alt=\"".$pictures[$j]->getRelativeName()."\" title=\"".$pictures[$j]->getRelativeName()."\" />";
				print "<br /><center><input type=\"checkbox\" name=\"picture[".$pictures[$j]->getID()."]\" /><b>{$pictures[$j]->getTitle()}</b></center>";
				print "</td>";
				if ( $i )
					print "</tr>";
				$i=!$i;
			}
			print "</table>";
	}
?>
	<br />
<?php
// multi-page display
if (count($pictures) > 6) {
	print "<center>";
	//end
	if ($start && (count($pictures)-$start <= 6)) {
		$prev = $start-6;
		print "<a href=\"grouppictures.php?start=$prev\">Previous Page</a>";
	}
	//middle
	else if ($start) {
		$prev = $start-6;
		$next = $start+6;
		print "<a href=\"grouppictures.php?start=$prev\">Previous Page</a> | <a href=\"grouppictures.php?start=$next\">Next Page</a>";
	}
	//start
	else {
		print "<a href=\"grouppictures.php?start=6\">Next Page</a>";
	}
	print "</center><br /><br />";
}

if (!$currentUser->isGroupGuest($currentGroup)) { 
	if ($pictures != null)
		print "<input type=\"submit\" name=\"deletepics\" value=\"Delete Selected Pictures\" />";
	else
		print "<h5>You have not uploaded any pictures.</h5>";
?>
	</fieldset></form>
<form method="post" action="grouppictures.php" enctype="multipart/form-data"><fieldset><legend>Upload a new picture</legend>
	<label for="picture">Picture:</label><input type="file" name="picture" id="picture" />
	<label for="title">Title:</label><input type="text" name="title" id="title" /><br />
	<input type="submit" name="addpic" value="Upload Picture" />
</fieldset></form>
<?php } ?>
</div></body>
</html>
