<?php
	session_start();

	include_once('../globals.php');
	include_once('../classes/db.php');
	include_once('../classes/person.php');
	include_once('../classes/group.php');
	include_once('../classes/nugget.php');
	include_once('../classes/semester.php');
	include_once('../classes/file.php');
	include_once('../classes/quota.php');
	include_once('../checklogin.php');


	//Globals:
	$_NUGPERPAGE = 10;
	$_COUNT = 0;
	
	if(is_numeric($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);

	if(isset($_POST['criteria']))
		$_SESSION['postdata'] = $_POST;
	else if(isset($_SESSION['postdata']))
		$_POST = $_SESSION['postdata'];
	
	//------Start XHTML Output--------------------------------------//
	
	require('../doctype.php');
	require('appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Nugget Library</title>
<script type="text/javascript">
//<![CDATA[
	function refreshSemester(object)
		{
			if ( object.value )
				window.location = object.value
		}

	function nuggetSearch(){
		form = document.getElementById("searchForm");
		//just in case
		form.submit();
	}
	
	function prevResults(){
		form = document.getElementById("navigate");
		form.direction.value = "back";
<?php
		if(isset($_POST['multiplier'])){
			echo "multiplier = ".$_POST['multiplier'].";";
?>
			form.multiplier.value = multiplier-1;
<?php
		}
?>
		form.submit();
	}
	
	function nextResults(){
		form = document.getElementById("navigate");
		form.direction.value = "forward";
<?php
		if(isset($_POST['multiplier'])){
			echo "multiplier =".$_POST['multiplier'].";"
?>
			form.multiplier.value= multiplier+1;
<?php
		}
?>
		form.submit();
	}
//]]>
</script>
</head>
<body>
<?php
	 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
?>
<h2><?php echo $appname; ?> Nugget Library</h2>
<p>Welcome to the <?php echo $appname; ?> Knowledge Management System. Here you can browse the deliverables of IIT's IPRO teams of the past and present. All deliverables and non-deliverables are organized into "nuggets". Nuggets contain downloadable files that make up the deliverable, plus metadata that contains information about the files' author(s) and their description. If you know the name of the team you wish to browse, use the "Browse IPROs" feature to locate it. If you don't know the name of the team, you can search through all of the nuggets by name, author or description.</p>
<form id="searchForm" method = "post" action="main.php"><fieldset>
	<div id="searchBar">
	<table cellspacing="0" cellpadding="6" style="border: thin solid black"><tr><td>
	<b>Search Nuggets</b><br />
		<input type="text" name="search" />
		Search Within: <select name="criteria">
		<option selected="selected" value = "all">All</option>
		<option value="name">Nugget Name/Type</option>
		<option value="author">Nugget Author</option>
		<option value="description">Nugget Description</option>
		</select>
<?php
		echo "<input type=\"hidden\" name=\"multiplier\" value=\"0\" />";
?>
		<input type="button" onclick="javascript:nuggetSearch()" value="Search" />
	</td></tr></table>
	</div>
</fieldset></form>
<form method="get" action="viewIproNuggets.php"><fieldset>
	<div id="sortBar">
	<br />
	<table cellspacing="0" cellpadding="6" style="border: thin solid black"><tr><td><b>Browse IPROs</b><br />
	<table cellspacing="4" cellpadding="0" style="border-style: none">
	<tr><td><select onchange="refreshSemester(this);">
	<option>Select a Semester</option>
<?php
	$sql = "select iID,sSemester from Semesters order by iID";
	$result = $db->query($sql);
	$id = array();
	$sem = array();
	while($row = mysql_fetch_array($result))
	{
		$id[] = $row['iID'];
		$sem[] = $row['sSemester'];
	}

	for($a = count($id)-1; $a >= 0; $a--)
	{
		echo '<option value="main.php?iSemesterID='.$id[$a].'"';
		if(isset($_GET['iSemesterID']) && $id[$a] == $_GET['iSemesterID'])
			echo " selected=\"selected\"";
		else if(!isset($_GET['iSemesterID']) && isset($_SESSION['iSemesterID']) && $id[$a] == $_SESSION['iSemesterID'])
			echo " selected=\"selected\"";
		echo  '>'.$sem[$a].'</option>';
	}
	echo '</select></td>';
	if(isset($_GET['iSemesterID']) || isset($_SESSION['iSemesterID']))
	{ 
		if(isset($_GET['iSemesterID']))
			$_SESSION['iSemesterID'] = $_GET['iSemesterID'];
?>
		<td id="jumpTpBox">

		<select name="id">
		<option>Select an IPRO</option>
<?php
		$sql = "select p.iID,p.sIITID,p.sName from Projects p,ProjectSemesterMap s where s.iSemesterID='".$_SESSION['iSemesterID']."' and s.iProjectID=p.iID group by s.iProjectID order by p.sIITID asc";
		$result = $db->query($sql);

		while($row = mysql_fetch_array($result))
		{
			echo "<option value=\"{$row['iID']}\">{$row['sIITID']}</option>";
		}
?>
		</select>
		<input type="hidden" name="semester" value="<?php echo $_SESSION['iSemesterID'];?>" />
		<input type="submit" value="Browse" />
		</td>
<?php
	}
?>
	</tr></table>
	</td></tr></table>
	</div>
	</fieldset></form><br /><br />
<?php
	//Functions:
	function browseNuggets()
	{
		//nothing has been specified, so just list the nuggets in order, newest first
		$nuggets = allNuggetsByDate();
		displayNuggets($nuggets, '');
	}
	
	function searchAllFields()
	{
		global $db;
		global $_NUGPERPAGE;
		global $_COUNT;
		$search = $_POST['search'];
		$multiplier = $_POST['multiplier'];
		$bottomLim = $multiplier * 10;
		$topLim = $multiplier * 10 + 10;
		//start with nuggets
		$query = "(SELECT iNuggetID, bOldNugget, MATCH(sTitle, sDescription) AGAINST ('$search') as Score FROM iGroupsNuggets WHERE MATCH(sTitle, sDescription) AGAINST ('$search'))UNION(SELECT iID, bOldNugget, MATCH(sTitle, sAbstract) AGAINST ('$search') as Score FROM Nuggets WHERE MATCH(sTitle, sAbstract) AGAINST ('$search')) ORDER BY Score DESC LIMIT $bottomLim,$_NUGPERPAGE";
		$results = $db->query($query);
		$nuggets = array();
		while($row = mysql_fetch_array($results))
			$nuggets[] = new Nugget($row[0], $db, $row['bOldNugget']);
		$query = "(SELECT iNuggetID, bOldNugget, MATCH(sTitle, sDescription) AGAINST ('$search') as Score FROM iGroupsNuggets WHERE MATCH(sTitle, sDescription) AGAINST ('$search'))UNION(SELECT iID, bOldNugget, MATCH(sTitle, sAbstract) AGAINST ('$search') as Score FROM Nuggets WHERE MATCH(sTitle, sAbstract) AGAINST ('$search')) ORDER BY Score DESC";
		$results = $db->query($query);
		$_COUNT = mysql_num_rows($results);
		displayNuggets($nuggets, '');

	}
	
	function orderByCriteria()
	{
		//dertermine the criteria used to sort by
		//and call the appropriate action
		if($_POST['criteria'] == 'name')
		{
			//return the nuggets by name in abc order
			$nuggets =allByName();
			displayNuggets($nuggets, '');
		}
		else if($_POST['criteria'] == 'author')
		{
			//return a list authros 
			$authors = nuggetAuthorList();
			printAuthorList($authors, '');
		}
		else if($_POST['criteria'] == 'description')
		{
			$nuggets = allByDescription();
			displayNuggets($nuggets, '');
		}
		else if($_POST['criteria'] == 'semester')
		{
			//same results as no criteria
			$nuggets = allNuggetsByDate();
			displayNuggets($nuggets, '');
		}
		else if($_POST['criteria'] == 'ipro')
		{
			//return a list of ipros
			$ipros = nuggetIproList();
			printIprosList($ipros, '');
		}
	}
	
	function searchWithCriteria()
	{
		global $db;
		global $_NUGPERPAGE;
		global $_COUNT;
		
		
		$multiplier = $_POST['multiplier'];
		$bottomLim = $multiplier * 10;
		$topLim = $multiplier * 10 + 10;
		//determine the criteria requested to determine the search method
		
		echo "<h2>Search Results</h2>\n";
		if($_POST['criteria'] == 'name')
		{
			//search by the nugget name
			$search = $_POST['search'];
			if ($search == '')
				echo "<p>No criteria provided. Please go back and provide a search term.</p>";
			else
			{
				$query = "(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score,iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score, iID, bOldNugget FROM Nuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE)) ORDER BY score DESC LIMIT $bottomLim,$_NUGPERPAGE";
				$results = $db->query($query);
				$nuggets = array();
				while($row = mysql_fetch_array($results))
					$nuggets[] = new Nugget($row[1], $db, $row[2]);
				$query = "(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score,iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score, iID, bOldNugget FROM Nuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE)) ORDER BY score DESC";
				$results = $db->query($query);
				$_COUNT = mysql_num_rows($results);

				displayNuggets($nuggets, '');
			}
		}
		else if($_POST['criteria'] == 'author')
		{
			$search = $_POST['search'];
			if($search == '')
				echo "<p>No criteria provided. Please go back and provide a search term.</p>\n";
			else
			{
				$query = "SELECT MATCH(sFName, sLName) AGAINST('$search' IN BOOLEAN MODE) AS score, iID FROM People WHERE MATCH(sFName, sLName) AGAINST('$search' IN BOOLEAN MODE) ORDER BY score DESC LIMIT $bottomLim,$_NUGPERPAGE";
				//if the length of the search is 3 characters or fewer, have to use the old fashioned test
				$authors = array();
				if(strlen($search) < 4)
					$query = "SELECT sLName, iID FROM People WHERE sLName = '$search'";

				//if there is a space make sure that both names are larger than 3 characters, if they are not do a field by field comparison
				if(strpos($search, ' '))
				{
					//two names check length of each
					if(strlen($search) < 9 || strpos($search, ' ') < 4 || strlen($search) - strpos($search, ' ') <= 4)
					{
						//do individual checks
						$str1 = substr($search, 0, strpos($search, ' '));
						$str2 = substr($search, strpos($search, ' ')+1);
						$specialQuery1 = "SELECT sFName, iID FROM People WHERE (sLName = '$str2' AND sFName = '$str1') OR (sFname = '$str2' AND sLName = '$str1')";
					}
				}
				if(isset($specialQuery1))
				{
					$newResults = $db->query($specialQuery1);
					while($row = mysql_fetch_array($newResults))
					{
						$person = new Person($row[1], $db);
						if(count($person->getNuggets()) > 0)
							$authors[] = $person;
					}	
				}
				$results = $db->query($query);
				while($row = mysql_fetch_array($results))
				{
					$person = new Person($row[1], $db);
					if(count($person->getNuggets()) > 0)
						$authors[] = $person;
				}
				printAuthorList($authors, '');
			}
		}
		else if($_POST['criteria'] == 'description')
		{
			$search = $_POST['search'];
			if ($search == '')
				echo "<p>No criteria provided. Please go back and provide a search term.</p>\n";
			else
			{
				//if search is too small do default search
				if(strlen($search) < 4)
					$query = "(SELECT iNugget, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE sDescription LIKE '%$search%')UNION(SELECT iID, iID, bOldNugget FROM Nuggets WHERE sAbstract LIKE '%$search%' LIMIT $bottomLim,$_NUGPERPAGE)";
				else
					$query = "(SELECT MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE) as Score, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE) as Score, iID, bOldNugget FROM Nuggets WHERE MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE))ORDER BY Score DESC LIMIT $bottomLim,$_NUGPERPAGE";
				$results = $db->query($query);
				$nuggets = array();
				while($row = mysql_fetch_array($results))
					$nuggets[] = new Nugget($row[1], $db, $row[2]);
				if(strlen($search) < 4)
					$query = "(SELECT iNugget, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE sDescription LIKE '%$search%')UNION(SELECT iID, iID, bOldNugget FROM Nuggets WHERE sAbstract LIKE '%$search%')";
				else
					$query = "(SELECT MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE) as Score, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE) as Score, iID, bOldNugget FROM Nuggets WHERE MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE))ORDER BY Score DESC";
				$results = $db->query($query);
				$_COUNT = mysql_num_rows($results);
				displayNuggets($nuggets, '');
			}
		}
		else if($_POST['criteria'] == 'semester')
		{
			$search = $_POST['search'];
			$query = "SELECT MATCH(sSemester) AGAINST('$search' IN BOOLEAN MODE) as Score, iID FROM Semesters WHERE MATCH(sSemester) AGAINST('$search' IN BOOLEAN MODE) ORDER BY Score DESC";
			$semesters = array();
			$results = $db->query($query);
			while($row = mysql_fetch_array($results))
				$semesters[] = $row[1];
			$ipros = array();
			//for each semester matching the criteria return the ipro list
			foreach($semesters as $semester)
			{
				$groupQuery = "SELECT iProjectID FROM ProjectSemesterMap WHERE iSemesterID=$semester";
				$results = $db->query($groupQuery);
				while($row = mysql_fetch_array($results))
				{
					$id = $row[0];
					$ipros[] = new Group($id, 0, $semester,$db);
				}
			}

			printIprosList($ipros, '');
		}
		else if($_POST['criteria'] == 'ipro')
		{
			$search = $_POST['search'];
			//do the most simple search firs
			$projects = array();
			$query1 = "SELECT iID FROM Projects WHERE sIITID LIKE ('$search')";
			$results1 = $db->query($query1);
			while($row = mysql_fetch_array($results1))
				$projects[] = $row[0];

			//union the two possible searches
			if(strpos($search, ' '))
			{
				$search2 = substr($search,0,4).substr($search,5);
				$query2 = "SELECT iID FROM Projects WHERE sIITID Like('$search2')";
				$results2 = $db->query($query2);
				while($row = mysql_fetch_array($results2))
					$projects[] = $row[0];
			}
			else
			{
				$search2 = substr($search,0,4).' '.substr($search,4);
				$query2 = "SELECT iID FROM Projects WHERE sIITID Like('$search2')";
				$results2 = $db->query($query2);
				while($row = mysql_fetch_array($results2))
					$projects[] = $row[0];
			}
			$query = "(SELECT MATCH(sIITID) AGAINST('$search' IN BOOLEAN MODE) as Score, iID FROM Projects WHERE MATCH(sIITID) AGAINST('$search' IN BOOLEAN MODE))UNION (SELECT MATCH(sIITID) AGAINST('$search2' IN BOOLEAN MODE) as Score, iID FROM Projects WHERE MATCH(sIITID) AGAINST('$search2' IN BOOLEAN MODE))Order By Score DESC";
			$results = $db->query($query);
			while($row = mysql_fetch_array($results))
				$projects[] = $row[1];

			//now add the other possiblities to the list
			$finalProject = array();
			foreach($projects as $project)
			{
				$query = "SELECT iProjectID, iSemesterID FROM ProjectSemesterMap WHERE iProjectID = $project";
				$results = $db->query($query);
				while($row = mysql_fetch_array($results))
					$finalProject[] = new Group($row[0],0,$row[1],$db);
			}
		
			printIprosList($finalProject, '');
		}		
	}
	
	function displayNuggets($nuggets, $criteria)
	{
		global $_NUGPERPAGE;
		if(count($nuggets) == 0)
			echo "<p>No results found.</p>";
		else
		{
?>
			<table>
			<tr><th>Group</th><th>Semester</th><th>Nugget Title</th><th>Description/Abstract</th><th>Date Created</th></tr>
<?php
			foreach($nuggets as $nug)
			{
				echo '<tr><td>'.$nug->getGroupName().'</td>';
				echo '<td>'.$nug->getSemester()->getName().'</td>';
				$title = "<a href=\"viewNugget.php?nuggetID=".$nug->getID()."&amp;isOld=".$nug->getOld()."\">".$nug->getType()."</a>";

				if($nug->isPrivate())
					$priv = "&nbsp;(Private)";
				else
					$priv = '';
				echo "<td>$title$priv</td>";
				echo '<td>'.htmlspecialchars($nug->getDescShort())."</td>";
				echo '<td>'.$nug->getDate().'</td>';
				echo "</tr>\n";
			}
			echo "</table>\n";
		}
	}
	function printIprosList($ipros, $criteria)
	{
		global $_NUGPERPAGE;
		global $db;
		
		if(count($ipros) == 0)
			echo '<p>No results found.</p>';
		else
		{
			echo '<table><tr>';
			if(isset($criteria) && $criteria != '')
				echo "<td>Search Criteria</td>";
			echo "<td>IPRO</td><td>Semester</td><td>Description</td></tr>\n";
			foreach($ipros as $ipro)
			{
				echo '<tr>';
				if(isset($criteria) && $criteria != '')
					echo '<td>something</td>';
				echo '<td>';
				$id = $ipro->getID();
				echo "<a href=\"viewIproNuggets?id=$id\">{$ipro->getName()}</a></td>";
				$semNum = $ipro->getSemester();
				$query = "SELECT sSemester FROM Semesters WHERE iID = $semNum";
				$result = $db->query($query);
				$row = mysql_fetch_array($result);
				echo "<td>{$row[0]}</td>";
				echo "<td>";
				echo htmlspecialchars($ipro->getDesc());
				echo "</td></tr>\n";
			}
			echo "</table>\n";
		}
	}
	function printAuthorList($authors, $criteria)
	{
		global $_NUGPERPAGE;
		if(count($authors) == 0)
			echo '<p>No results found.</p>';
		else
		{
?>
			<table>
			<tr><th>Nugget</th><th>Group</th><th>Semester</th></tr>
<?php
			foreach($authors as $author)
			{
				echo "<tr><th colspan=\"3\">".$author->getFullName()."</th></tr>\n";
				//generate nuggetList per author
				$tempNugs = $author->getNuggets();
				foreach($tempNugs as $nug)
				{
					$name = $nug->getType();
					if($name != null)
					{
						$id = $nug->getID();
						$old = ($nug->isOld() ? 1 : 0); 
						echo "<tr><td><a href=\"viewNugget.php?nuggetID=$id&amp;isOld=$old\">$name</a></td>";
						$grp = $nug->getGroupName();
						echo "<td>$grp</td>";
						$sem = $nug->getSemester()->getName();
						echo "<td>$sem</td></tr>\n";
					}
				}
			}
			echo "</table>\n";
		}
	}

	function getSemesters(){
		global $db;
		$query = "SELECT iID, sSemester FROM Semesters ORDER BY sSemester";
		$results = $db->query($query);
		$semesters = array();
		while($row = mysql_fetch_array($results)){
			$semesters[$row[1]] = $row[0];

		}
		return $semesters;
	}

	function getIpros()
	{
		global $db;
		$query = 'SELECT iID, sIITID FROM Projects ORDER BY sIITID';
		$results = $db->query($query);
		$ipros = array();
		while($row = mysql_fetch_array($results))
			$ipros[$row[1]] = $row[0];
		return $ipros;
	}
	//Form processing:
	
	if(isset($_POST['multiplier']))
	{
		//this variables are set when a user has pressed search
		$_DIRECTION = $_POST['direction'];
		$_MULTIPLIER = $_POST['multiplier'];
		
	}
	if(isset($_POST['semester']))
	{
		$semester = $_POST['semester'];
		$ipro = new Group($_POST['ipro'], 0,$semester, $db);
		$ipros = array();
		$ipros[] = $ipro;
		printIprosList($ipros, '');
	}
	if(isset($_POST['criteria']))
	{
		//they have submitted the form now process the request
		//check for search everything case
		$_SESSION['criteria'] = $_POST['criteria'];
		if($_POST['criteria'] == 'all' && $_POST['search'] == '')
			echo "<p>No criteria provided. Please try again, providing a search term.</p>";
		else
		{
			if($_POST['criteria'] == 'all' && $_POST['search'] != '')
			{
				//search all fields for a particular word
				searchAllFields();
				$_SESSION['search'] = $_POST['search'];
			}
		}
		if($_POST['criteria'] != 'all' and $_POST['search'] == '')
			echo '<p>No criteria provided. Please try again, providing a search term.</p>';
		else
		{
			if($_POST['criteria'] != 'all' and $_POST['search'] != '')
			{
				//search a particular field for a desired phrase
				searchWithCriteria();
				$_SESSION['search'] = $_POST['search'];
			}
		}
	}
?>
<form id="navigate" method="post" action="main.php"><fieldset>
	<div id="navigatePart">
<?php
		if(isset($_POST['search']) && ($_MULTIPLIER > 0))
				echo "<input type=\"button\" onclick=\"javascript:prevResults()\" value=\"&lt;\" />";
		echo '&nbsp;';
		if($_COUNT > 0)
		{
			if(($_MULTIPLIER*$_NUGPERPAGE+$_NUGPERPAGE) < $_COUNT)
				$upBound = $_MULTIPLIER*$_NUGPERPAGE+$_NUGPERPAGE;
			else
				$upBound = $_COUNT;
			echo " Showing results ".($_MULTIPLIER*$_NUGPERPAGE+1)." through $upBound of $_COUNT.&nbsp;";
		}
		if(isset($_POST['search']) && ($_MULTIPLIER * $_NUGPERPAGE+$_NUGPERPAGE < $_COUNT))
			echo "<input type=\"button\" onclick=\"javascript:nextResults()\" value=\"&gt;\" />";
		echo "<input type=\"hidden\" name=\"multiplier\" />";
		echo "<input type=\"hidden\" name=\"direction\" />";
		$search = $_SESSION['search'];
		$criteria = $_SESSION['criteria'];
		echo "<input type=\"hidden\" name=\"search\" value=\"$search\" />";
		echo "<input type=\"hidden\" name=\"criteria\" value=\"$criteria\" />";
?>
</div></fieldset></form>
<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body>
</html>
