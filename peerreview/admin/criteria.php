<?php 
include_once('../classes/db.php');
include_once('../classes/person.php');
include_once('../classes/group.php');
include_once('../classes/rating.php');
include_once('../classes/criterion.php');
include_once('../classes/criteriaList.php');

include_once('checkadmin.php');

if(isset($_POST['removeFromList']) && isset($_POST['criterionID']))
{
	$criterion = new Criterion($_POST['criterionID'], $db);
	$criteriaID = $criterion->getID();
	$db->query("delete from Criteria where id={$criteriaID}");
}

if(isset($_GET['selectList']) && $_GET['criteria'])
	$_SESSION['criteria'] = $_GET['criteria'];
else if(isset($_GET['selectList']))
{
	unset($_SESSION['criteria']);
	$currentList = 'TestCriteria'; //not sure if we need this
}

//create criterion
if(isset($_POST['createCriterion']))
{
	$criterion_name = $_POST['criterionName'];
	$criterion_desc = $_POST['criterionDesc'];
	$criteria_list = $_SESSION['criteria'];
	
	$criterion = createCriterion($criterion_name, $criterion_desc, $criteria_list, $db);
	$message = "Criterion was successfuly created.";
}

if(isset($_SESSION['criteria']))
	$currentList = new CriteriaList($_SESSION['criteria'], $db);

if($currentUser->isAdministrator())
{
	$query = $db->query("SELECT id FROM CriteriaList ORDER BY name");
	while ($row = mysql_fetch_row($query))
		$lists[] = new CriteriaList($row[0], $db);
}
else
	$lists = $currentList->getCriteria();
	

//create Survey
if(isset($_POST['createList']))
{
	$list_name = $_POST['listName'];
	
	$group = createCriteriaList($list_name, $db);
	$message = 'Survey was successfuly created.';
}
include_once('../includes/header.php');
if(isset($message))
	echo "<script type=\"text/javascript\">showMessage(\"$message\");</script>\n";
?>
<title><?php echo $GLOBALS['systemname']; ?> - Manage Surveys</title>
<?php
include_once('../includes/sidebar.php');
?>
<div id="content">
<h2>Manage Surveys</h2>
<p>Use this section to create new surveys and manage it by adding new items to it or removing unwanted items. To add or remove items to the survey, select the survey name from the dropdown box under 'Edit a Survey'.</p>
<?php
echo "<hr$st>\n";
if($currentUser->isAdministrator())
{
	echo "<form action=\"criteria.php\" method=\"post\"><fieldset><legend>Create New Survey</legend>\n";
	echo "<label>Survey Name: <input type=\"text\" name=\"listName\"$st></label><br$st>\n";
	echo "<input type=\"submit\" name=\"createList\" value=\"Create Survey\"$st>\n";
	echo "</fieldset></form>\n";
}
echo "<form action=\"criteria.php\" method=\"get\"><fieldset><legend>Edit a Survey</legend>\n";
echo "<select name=\"criteria\">\n";

$criteria = getCriteriaLists($db);
foreach ($criteria as $criterialist)
	echo "<option value=\"{$criterialist->getID()}\">".htmlspecialchars($criterialist->getName()).'</option>';
echo "</select><br$st>\n";
echo "<input type=\"submit\" name=\"selectList\" value=\"Select Survey\"$st>\n";
echo "</fieldset></form><br$st>\n";

if(isset($_SESSION['criteria']))
{
	echo "<form action=\"criteria.php\" method=\"post\"><fieldset><legend></legend>\n";
	echo "<table width=\"80%\" class=\"centered\">\n";
	echo "<tr><th colspan=\"3\">".htmlspecialchars($currentList->getName())."</th></tr>\n";
	$criteria = $currentList->getCriteria();

	echo "<tr><td colspan=\"3\"><h4>Survey Items:</h4>\n";
	echo "<table class=\"blank\" width=\"100%\">\n";
	$i = 0;
	foreach($criteria as $criterion)
	{
		$thequestion = " <span class=\"question\">".htmlspecialchars($criterion->getName())."</span><br$st><span class=\"description\">(".htmlspecialchars($criterion->getDescription()).")</span>";
		if ($i==0)
		{
			echo "<tr><td class=\"item\"><input type=\"radio\" name=\"criterionID\" value=\"{$criterion->getID()}\"$st>$thequestion</td>";
			$i++;
		}
		else if($i==1)
		{
			echo "<td class=\"item\"><input type=\"radio\" name=\"criterionID\" value=\"{$criterion->getID()}\"$st>$thequestion</td>";
			$i++;
		}
		else
		{
			echo "<td class=\"item\"><input type=\"radio\" name=\"criterionID\" value=\"{$criterion->getID()}\"$st>$thequestion</td></tr>\n";
			$i=0;
		}
	}
	if($i > 0)
		echo "</tr>\n";
	echo "</table></td></tr>\n";
	if ($currentUser->isAdministrator())
	{
		echo "<tr><td><input type=\"submit\" name=\"removeFromList\" value=\"Remove Item\"$st></td></tr>\n";
		echo "</table></fieldset></form>\n";
		echo "<form action=\"criteria.php\" method=\"post\"><fieldset><legend>Add a New Survey Item</legend>\n";
		echo "<table class=\"centered\">\n";
		echo "<tr><td><label for=\"criterionName\">Name:</label></td><td><input type=\"text\" name=\"criterionName\" id=\"criterionName\"$st></td></tr>\n";
		echo "<tr><td><label for=\"criterionDesc\">Description:</label></td><td><input type=\"text\" name=\"criterionDesc\" id=\"criterionDesc\"$st></td></tr>\n";
		echo "<tr><td colspan=\"2\"><input type=\"submit\" name=\"createCriterion\" value=\"Add New Item\"$st></td></tr>\n";
		echo "</table>\n";
	}
	echo "</fieldset></form>\n";
}
?>
</div></body></html>
