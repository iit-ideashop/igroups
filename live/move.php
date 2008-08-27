<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/category.php" );
	include_once( "classes/email.php" );
	
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
<div class="window-content" id="moveFrame">
<?php
	if(isset($_GET['id'])) {
		$categories = $currentGroup->getGroupCategories();
?>
			<form method="post" action="email.php">
			Move email to category:
			<select name="targetcategory"><option value="0">No Category</option>
<?php
			$categories = $currentGroup->getGroupCategories();
			foreach ( $categories as $category ) {
				print "<option value=\"".$category->getID()."\">".$category->getName()."</option>";
			}
			print "</select><input type=\"hidden\" name=\"email\" value=\"".$_GET['id']."\" />";
?>
			<br />
			<input type="submit" name="move" value="Move Email" /></form>
<?php
	}
	else {
?>
	<p>Error: No email selected.</p>
<?php
}
?>
</div>
