<?php
	include_once("globals.php");
	include_once("checklogin.php");	
	include_once( "classes/grouppicture.php" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Group Pictures</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
	<style type="text/css">
		.picture-container {
			display:inline;
		}
	</style>
</head>
<body>
<?php
	if ( isset( $_POST['addpic'] ) ) {
		if ( $_FILES['picture']['error'] == UPLOAD_ERR_OK  && @getimagesize($_FILES['picture']['tmp_name']) && @is_uploaded_file($_FILES['picture']['tmp_name']) && ($_FILES['picture']['type'] == 'image/gif' || $_FILES['picture']['type'] == 'image/jpeg' || $_FILES['picture']['type'] == 'image/bmp' || $_FILES['picture']['type'] == 'image/x-windows-bmp' || $_FILES['picture']['type'] == 'image/png' || $_FILES['picture']['type'] == 'image/pjpeg')) {
			$pic = createGroupPicture( $_FILES['picture']['name'], $_POST['title'], $currentGroup, $db );
			move_uploaded_file($_FILES['picture']['tmp_name'], $pic->getDiskName() );
			$message = "Picture successfully added";
		}
	}
	
	if ( isset( $_POST['deletepics'] ) ) {
		if (isset($_POST['picture'])) {
		foreach( $_POST['picture'] as $picid => $val ) {
			$pic = new GroupPicture( $picid, $db );
			$pic->delete();
		}
		$message = "Selected pictures successfully deleted";
		}
	}
	require("sidebar.php");
	print "<div id=\"content\"><div id=\"topbanner\">";
	print $currentGroup->getName()."</div>";
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
				print "<tr><th colspan=\"2\">Viewing Pictures {$start2} - {$end}</th></tr>";
			for ($j = $start; $j<$end; $j++) {
				if ( !$i )
					print "<tr>";
				print "<td>";
				print "<img width=\"300\" src=\"$appurl/".$pictures[$j]->getRelativeName()."\" alt=\"".htmlspecialchars($pictures[$j]->getTitle())."\" title=\"".htmlspecialchars($pictures[$j]->getTitle())."\" />";
				print "<br /><span style=\"text-align: center\"><input type=\"checkbox\" name=\"picture[".$pictures[$j]->getID()."]\" /><b>".htmlspecialchars($pictures[$j]->getTitle())."</b></span>";
				print "</td>";
				if ( $i )
					print "</tr>";
				$i=!$i;
			}
			if($i)
				print "</tr>";
			print "</table>";
	}
?>
	<br />
<?php
// multi-page display
if (count($pictures) > 6) {
	print "<span style=\"text-align: center\">";
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
	print "</span><br /><br />";
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
