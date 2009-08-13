<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/category.php');
	include_once('classes/email.php');
?>
<div class="window-content" id="moveFrame">
<?php
	if(isset($_GET['id']))
	{
		$categories = $currentGroup->getGroupCategories();
?>
		<form method="post" action="email.php">
		Move email to category:
		<select name="targetcategory"><option value="0">No Category</option>
<?php
		$categories = $currentGroup->getGroupCategories();
		foreach($categories as $category)
			echo "<option value=\"".$category->getID()."\">".$category->getName()."</option>";
		echo "</select><input type=\"hidden\" name=\"email\" value=\"".$_GET['id']."\" />";
?>
		<br />
		<input type="submit" name="move" value="Move Email" /></form>
<?php
	}
	else
		echo "<p>Error: No email selected.</p>\n";
?>
</div>
