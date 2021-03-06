<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	
	if(isset($_POST['newname']))
	{
		$priv = $_POST['private'] ? 0 : 1;
		if($db->query('insert into Skins (sName, bPublic) values ("'.mysql_real_escape_string($_POST['newname']).'",'.$priv.')'))
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
				$db->query('update Skins set sName="'.mysql_real_escape_string($_POST["N$id"]).'", bPublic='.($_POST["P$id"] ? 0 : 1)." where iID=$id");
		}
		if(is_numeric($_POST['default']) && mysql_num_rows($db->query('select * from Skins where iID='.$_POST['default'])))
		{
			$db->query('update Skins set bDefault=0');
			$db->query('update Skins set bDefault=1, bPublic=1 where iID='.$_POST['default']);
		}
		if(!mysql_num_rows($db->query('select * from Skins where bPublic=1')))
			$db->query('update Skins set bPublic=1 where bDefault=1');
	}
	else if(is_numeric($_GET['delete']))
	{
		$default = mysql_fetch_row($db->query('select bPublic, bDefault from Skins where iID='.$_GET['delete']));
		$count = mysql_fetch_row($db->query('select count(*) from Skins'.($default[0] ? '' : ' where bPublic=1')));
		if($count[0] <= 1)
			$messages[] = 'Cannot delete this skin: Must have at least one public skin';
		else if($default[1])
			$messages[] = 'Cannot delete this skin: This skin is the default skin';
		else if($db->query('delete from Skins where iID='.$_GET['delete']))
			$messages[] = 'Skin deleted';
		else
			$messages[] = 'Error occurred while deleting skin: '.mysql_error();
	}
	
	//----------Start XHTML Output----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Skins</title>
</head>
<body>
<?php
		 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/

	echo '<p>Warning: Do not add or edit a skin using this interface unless the requisite CSS files have been placed in the skins folder.</p>';
	$query = $db->query('select * from Skins');
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
		echo '<td><input type="radio" name="default" value="'.$row['iID'].'" '.($row['bDefault'] ? 'checked="checked" ' : '').'/></td>';
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

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>

</body>
</html>
