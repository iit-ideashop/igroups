<?php
	include_once('globals.php');
	include_once('checklogin.php');	
	include_once('classes/grouppicture.php');
	
	//----------Start XHTML Output----------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Group Pictures</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
<style type="text/css">
.picture-container
{
	display: inline;
}
</style>
</head>
<body>
<?php
	if(isset( $_POST['addpic']))
	{
		if($_FILES['picture']['error'] == UPLOAD_ERR_OK && @getimagesize($_FILES['picture']['tmp_name']) && @is_uploaded_file($_FILES['picture']['tmp_name']) && ($_FILES['picture']['type'] == 'image/gif' || $_FILES['picture']['type'] == 'image/jpeg' || $_FILES['picture']['type'] == 'image/bmp' || $_FILES['picture']['type'] == 'image/x-windows-bmp' || $_FILES['picture']['type'] == 'image/png' || $_FILES['picture']['type'] == 'image/pjpeg'))
		{
			$pic = createGroupPicture($_FILES['picture']['name'], $_POST['title'], $currentGroup, $db);
			move_uploaded_file($_FILES['picture']['tmp_name'], $pic->getDiskName() );
			$message = 'Picture successfully added';
		}
	}
	
	if(isset($_POST['deletepics']))
	{
		if(isset($_POST['picture']))
		{
			foreach($_POST['picture'] as $picid => $val)
			{
				$pic = new GroupPicture($picid, $db);
				$pic->delete();
			}
			$message = 'Selected pictures successfully deleted';
		}
	}
	/**** begin html head *****/
   require('htmlhead.php'); //starts main container
/****end html head content ****/	

	echo "<form method=\"post\" action=\"grouppictures.php\"><fieldset>\n";
	$pictures = $currentGroup->getGroupPictures();
	if(count($pictures) > 0)
	{
			echo "<table>";
			$i = false;
			if(isset($_GET['start']) && is_numeric($_GET['start']) && ($_GET['start'] % 6 == 0) && ($_GET['start'] < count($pictures)))
				$start = $_GET['start'];
			else
				$start = 0;
			if((count($pictures) - $start) <= 6)
				$end = count($pictures);
			else
				$end = $start + 6;
			$start2 = $start + 1;
			if(count($pictures) > 0)
				echo "<tr><th colspan=\"2\">Viewing Pictures {$start2} - {$end}</th></tr>";
			for($j = $start; $j<$end; $j++)
			{
				if(!$i)
					echo "<tr>";
				echo "<td>";
				echo "<img width=\"300\" src=\"$appurl/".$pictures[$j]->getRelativeName()."\" alt=\"".htmlspecialchars($pictures[$j]->getTitle())."\" title=\"".htmlspecialchars($pictures[$j]->getTitle())."\" />";
				echo "<br /><span style=\"text-align: center\"><label><input type=\"checkbox\" name=\"picture[".$pictures[$j]->getID()."]\" /><b>".htmlspecialchars($pictures[$j]->getTitle())."</b></label></span>";
				echo "</td>";
				if($i)
					echo "</tr>\n";
				$i = !$i;
			}
			if($i)
				echo "</tr>\n";
			echo "</table>";
	}
?>
	<br />
<?php
	// multi-page display
	if(count($pictures) > 6)
	{
		echo "<span style=\"text-align: center\">";
		if($start && (count($pictures)-$start <= 6))
		{
			$prev = $start-6;
			echo "<a href=\"grouppictures.php?start=$prev\">Previous Page</a>";
		}
		else if($start)
		{
			$prev = $start-6;
			$next = $start+6;
			echo "<a href=\"grouppictures.php?start=$prev\">Previous Page</a> | <a href=\"grouppictures.php?start=$next\">Next Page</a>";
		}
		else
		{
			echo "<a href=\"grouppictures.php?start=6\">Next Page</a>";
		}
		echo "</span><br /><br />";
	}

	if(!$currentUser->isGroupGuest($currentGroup))
	{ 
		if($pictures != null)
			echo "<input type=\"submit\" name=\"deletepics\" value=\"Delete Selected Pictures\" />";
		else
			echo "<h5>You have not uploaded any pictures.</h5>";
?>
		</fieldset></form>
		<form method="post" action="grouppictures.php" enctype="multipart/form-data"><fieldset><legend>Upload a new picture</legend>
		<label for="picture">Picture:</label><input type="file" name="picture" id="picture" />
		<label for="title">Title:</label><input type="text" name="title" id="title" /><br />
		<input type="submit" name="addpic" value="Upload Picture" />
		</fieldset></form>
<?php
	}
?>

<?php
//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>	


</body></html>
