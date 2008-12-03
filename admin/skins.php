<?php
	include_once("../globals.php");
	include_once( "checkadmin.php" );
	
	if(isset($_POST['newname']))
	{
		$priv = $_POST['private'] ? 1 : 0;
		if($db->igroupsQuery('insert into Skins (sName, bPrivate) values ("'.mysql_real_escape_string($_POST['newname']).'",'.$priv.')'))
			$messages[] = 'New skin created';
		else
			$messages[] = 'Error occurred while creating skin: '.mysql_error();
	}
	else if(isset($_POST['update']))
	{
		$ids = explode(',', $_POST['update']);
		foreach($ids as $id)
		{
			if(is_numeric($id))
				$db->igroupsQuery('update Skins set sName="'.mysql_real_escape_string($_POST["N$id"]).'", bPrivate='.($_POST["P$id"] ? 1 : 0)." where iID=$id");
		}
		if(is_numeric($_POST['default']) && mysql_num_rows($db->igroupsQuery('select * from Skins where iID='.$_POST['default'])))
		{
			$db->igroupsQuery('update Skins set bDefault=0');
			$db->igroupsQuery('update Skins set bDefault=1, bPublic=1 where iID='.$_POST['default']);
		}
	}
	else if(is_numeric($_GET['delete']))
	{
		$default = mysql_fetch_row($db->igroupsQuery('select bPrivate, bDefault from Skins where iID='.$_GET['delete']));
		$count = mysql_fetch_row($db->igroupsQuery('select count(*) from Skins'.($default[0] ? '' : ' where bPrivate=0')));
		if($count[0] <= 1)
			$messages[] = 'Cannot delete this skin: Must have at least one public skin';
		else if($default[1])
			$messages[] = 'Cannot delete this skin: This skin is the default skin';
		else if($db->igroupsQuery('delete from Skins where iID='.$_GET['delete']))
			$messages[] = 'Skin deleted';
		else
			$messages[] = 'Error occurred while deleting skin: '.mysql_error();
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php require("../iknow/appearance.php"); ?>
<title><?php echo $appname;?> - Skins</title>
</head>
<body>
<?php
	require("sidebar.php");
	echo '<div id="content">';
	$query = $db->igroupsQuery('select * from Skins');
	echo "<form method=\"post\"><fieldset><legend>Edit Skins</legend>\n";
	echo "<table><tr><th>Skin</th><th>Private</th><th>Default</th><th>Delete</th></tr>\n";
	$ids = '';
	$i = 0;
	while($row = mysql_fetch_array($query))
	{
		if($i)
			$ids .= ',';
		$ids .= $row['iID'];
		echo '<tr>';
		echo '<td><input type="text" name="N'.$row['iID'].'" value="'.htmlspecialchars(stripslashes($row['sName'])).'" /></td>';
		echo '<td><input type="checkbox" name="P'.$row['iID'].'" '.($row['bPublic'] ? '' : 'checked="checked" ').'/></td>';
		echo '<td><input type="radio" name="default" value="'.$row['iID'].' '.($row['bDefault'] ? 'checked="checked" ' : '').'/></td>';
		echo '<td><a href="skins.php?delete='.$row['iID'].'">Delete</a></td>';
		echo "</tr>\n";
		$i++;
	}
	echo "</table>\n";
	echo "<input type=\"hidden\" name=\"update\" value=\"$ids\" />\n";
?>
<input type="submit" value="Update skins" /><input type="reset" /></fieldset></form>
<form method="post"><fieldset><legend>New Skin</legend>
<label>Name: <input type="text" name="newname" /></label><br />
<label><input type="checkbox" name="private" /> Private</label><br />
<input type="submit" value="Create skin" />
</fieldset></form>
</div></body>
</html>
