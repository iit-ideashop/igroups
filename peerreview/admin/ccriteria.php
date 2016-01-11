<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/rating.php');
include_once('../classes/criterion.php');
include_once('../classes/criteriaList.php');
include_once('../classes/customcriteria.php');

include_once('checkadmin.php');
	
if(isset($_SESSION['group']) && !is_numeric($_SESSION['group']))
	unset($_SESSION['group']);
if(!is_numeric($_GET['group']) || $_GET['group'] < 1)
{
	unset($_GET['group']);
	$get = '';	
}
else
	$get = "?group={$_GET['group']}";

if(isset($_GET['selectGroup']) && $_GET['group'])
{
	$_SESSION['group'] = $_GET['group'];
}
else if(isset($_GET['selectGroup']))
{
	unset($_SESSION['group']);
	$currentGroup = 0;
}

if(isset($_SESSION['group']) && $_GET['group'] != 0)
	$currentGroup = new Group($_SESSION['group'], $db);
else if($_GET['group'] == 0)
	$currentGroup = 0;

if(isset($_POST['create']) && $currentGroup)
	createCustomCriterion($_POST['name'], $_POST['desc'], $currentGroup->getID(), $db);
else if(isset($_POST['edit']) && $currentGroup)
{
	$crits = getCustomCriteria($currentGroup->getID(), $db);
	foreach($crits as $id => $crit)
	{
		if($_POST["X$id"])
			$crit->delete();
		else
			$crit->update($_POST["N$id"], $_POST["D$id"]);
	}
}

$groups = array();

if($currentUser->isAdministrator())
{
	$query = $db->query('SELECT id FROM Groups ORDER BY name');
	
	while($row = mysql_fetch_row($query))
		$groups[] = new Group($row[0], $db);
}
else
	$groups = $currentUser->getGroups();

include_once('../includes/header.php');
if(isset($message))
	echo "<script type=\"text/javascript\">showMessage(\"$message\");</script>\n";
?>
<title><?php echo $GLOBALS['systemname']; ?> - Manage Custom Criteria</title>
<?php
include_once('../includes/sidebar.php');
?>
<div id="content">
<h2>Manage Custom Criteria</h2>
<p>Here you can provide groups with custom survey questions, beyond the survey questions that come standard.</p>
<?php
echo "<hr$st>\n<form action=\"ccriteria.php$get\" method=\"get\"><fieldset><legend>Edit a Group</legend>\n";
echo "<select name=\"group\">\n";

foreach($groups as $group)
{
	if(!isset($currentGroup))
		$currentGroup = new Group($group->getID(), $db);
	$gpname = htmlspecialchars($group->getName());
	if($currentGroup && $currentGroup->getID() == $group->getID())
		echo "\t<option value=\"{$group->getID()}\" $selected>$gpname</option>\n";
	else
		echo "\t<option value=\"{$group->getID()}\">$gpname</option>\n";
}
echo "</select>\n";
echo "<input type=\"submit\" name=\"selectGroup\" value=\"Select Group\"$st>\n";
echo "</fieldset></form><br$st>\n";

if(isset($_SESSION['group']) && $currentGroup)
{
	$customcriteria = getCustomCriteria($currentGroup->getID(), $db);
	$cc = count($customcriteria) > 0 ? true : false;
	if($cc)
		echo "<form action=\"ccriteria.php$get\" method=\"post\"><fieldset><legend>Manage Criteria</legend>\n";
	echo "<table><tr><th>Name</th><th>Description</th>".($cc ? '<th>Delete</th>' : '')."</tr>\n";
	$criteria = getCriteria($currentGroup->getListID(), $db);
	foreach($criteria as $criterion)
		echo "<tr><td>".htmlspecialchars($criterion->getName())."</td><td>".htmlspecialchars($criterion->getDescription())."</td>".($cc ? '<td>*</td>' : '')."</tr>\n";
	foreach($customcriteria as $criterion)
		echo "<tr><td><input type=\"text\" name=\"N{$criterion->getID()}\" value=\"".htmlspecialchars($criterion->getName())."\"$st></td><td><input type=\"text\" name=\"D{$criterion->getID()}\" value=\"".htmlspecialchars($criterion->getDescription())."\" size=\"80\"$st></td><td><input type=\"checkbox\" name=\"X{$criterion->getID()}\"$st></td></tr>\n";
	if($cc)
	{
		echo "<tr><td colspan=\"3\"><input type=\"submit\" name=\"edit\" value=\"Edit Criteria\"$st><input type=\"reset\"$st></td></tr></table>\n</fieldset></form>\n";
		echo "<p>* - Admin set criterion, cannot be deleted or edited</p>\n";
	}
	else
		echo "</table>\n";
?>
<form action="ccriteria.php<?php echo $get; ?>" method="post"><fieldset><legend>Add a New Custom Criterion</legend>
<table>
<tr><th>Field</th><th>Input</th><th>Example</th></tr>
<tr><td><label for="name">Name:</label></td>
<td><input type="text" name="name" id="name"<?php echo $st; ?>></td>
<td>Defines Tasks</td></tr>
<tr><td><label for="desc">Description:</label></td>
<td><input type="text" name="desc" id="desc"<?php echo $st; ?>></td>
<td>Identifies the problem and breaks it into manageable parts</td></tr>
<tr><td colspan="3"><input type="submit" name="create" value="Create Criterion"<?php echo $st; ?>></td></tr></table></fieldset></form>
<?php
}
?>
</div></body></html>
