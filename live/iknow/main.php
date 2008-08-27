<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/nugget.php" );
	include_once( "../classes/semester.php" );
	include_once( "../classes/file.php" );
	include_once( "../classes/quota.php" );
	
	//Globals:
	$_NUGPERPAGE = 10;
	$_DB = new dbConnection();
	$_COUNT = 0;

	if (isset($_POST['criteria']))
		$_SESSION['postdata'] = $_POST;
	else if (isset($_SESSION['postdata']))
		$_POST = $_SESSION['postdata'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Nugget Library</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
<script language="JavaScript" type="text/Javascript">
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
			print "multiplier = ".$_POST['multiplier'].";";
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
			print "multiplier =".$_POST['multiplier'].";"
?>
			form.multiplier.value= multiplier+1;
<?php
		}
?>
		form.submit();
	}
	
</script>
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content">
<hr />
<h2>iKnow/iGroups Nugget Library</h2>
<p>Welcome to the iGroups Knowledge Management System. Here you can browse the deliverables of IIT's IPRO teams of the past and present. All deliverables and non-deliverables are organized into "nuggets". Nuggets contain downloadable files that make up the deliverable, plus metadata that contains information about the files' author(s) and their description. If you know the name of the team you wish to browse, use the "Browse IPROs" feature to locate it. If you don't know the name of the team, you can search through all of the nuggets by name, author or description.</p>
<form id='searchForm' method = 'post' action='main.php'>
	<div id='searchBar'>
	<table border="1" cellspacing="0" cellpadding='6'><tr><td bgcolor='#EEEEEE'>
	<b>Search Nuggets</b><br />
		<input type='text' name='search' />
		Search Within: <select name='criteria'>
		<option selected="selected" value = "all">All</option>
		<option value="name">Nugget Name/Type</option>
		<option value="author">Nugget Author</option>
		<option value="description">Nugget Description</option>
		</select>
<?php
		print "<input type='hidden' name='multiplier' value='0' />";
?>
		<input type='button' onclick="JavaScript:nuggetSearch()" value="Search" />
	</td></tr></table>
	</div>
</form>
<form method = 'get' action='viewIproNuggets.php'>
	<div id='sortBar'>
	<br />
	<table border='1' cellspacing='0' cellpadding='6'><tr><td bgcolor='#EEEEEE'><b>Browse IPROs</b><br />
		<table border="0" cellspacing="4" cellpadding="0">
                                <tr>
                                        <td>
                                                <select onchange="refreshSemester(this);">
                                                <option>Select a Semester</option>
                                                <?php

                                                $sql = "select iID,sSemester from Semesters order by iID";
                                                $result = $_DB->igroupsQuery($sql);
                                                $id = array();
                                                $sem = array();
                                                while($row = mysql_fetch_array($result))
                                                {
                                                        $id[] = $row['iID'];
                                                        $sem[] = $row['sSemester'];
                                                }

                                                for($a=count($id)-1; $a>=0; $a--)
                                                {
                                                        echo '<option value="main.php?iSemesterID=' . $id[$a] . '"';
                                                        if(isset($_GET['iSemesterID']) && $id[$a]==$_GET['iSemesterID'])
                                                                echo " selected=\"selected\"";
							else if (!isset($_GET['iSemesterID']) && isset($_SESSION['iSemesterID']) && $id[$a]==$_SESSION['iSemesterID'])
								echo " selected=\"selected\"";
                                                        echo  '>' . $sem[$a] .  "</option>";
                                                }
                                                ?>
                                        </select></td>

                                        <?php if(isset($_GET['iSemesterID']) || isset($_SESSION['iSemesterID'])){ 
						if (isset($_GET['iSemesterID']))
							$_SESSION['iSemesterID'] = $_GET['iSemesterID'];

?>
                                        <td id="jumpTpBox">

                                        <select name='id'>
                                        <option>Select an IPRO</option>

                                                <?php
                                                $sql = "select p.iID,p.sIITID,p.sName from Projects p,ProjectSemesterMap s where s.iSemesterID='" . $_SESSION['iSemesterID'] . "' and s.iProjectID=p.iID group by s.iProjectID order by p.sIITID asc";
                                                $result = $_DB->igroupsQuery($sql);

                                                while($row = mysql_fetch_array($result))
                                                {
                                                        echo "<option value='{$row['iID']}'>{$row['sIITID']}</option>";
                                                }
                                                ?>
                                        </select>
					<input type='hidden' name='semester' value='<?php print $_SESSION['iSemesterID'];?>' />
					<input type='submit' value="Browse" />
                                        </td>
                                        <?php } ?>
                                </tr></table>
                                </td></tr></table>
	</div>
</form>
<br />
<br />
<?php

	//Functions:
	function browseNuggets(){
		//nothing has been specified, so just list the nuggets in order, newest first
		$nuggets = allNuggetsByDate();
		displayNuggets($nuggets,"");
	}
	
	function searchAllFields(){
		global $_DB;
		global $_NUGPERPAGE;
		global $_COUNT;
		$search = $_POST['search'];
		$multiplier = $_POST['multiplier'];
		$bottomLim = $multiplier * 10;
		$topLim = $multiplier * 10 + 10;
		//start with nuggets
		$query = "(SELECT iNuggetID, bOldNugget, MATCH(sTitle, sDescription) AGAINST ('$search') as Score FROM iGroupsNuggets WHERE MATCH(sTitle, sDescription) AGAINST ('$search'))UNION(SELECT iID, bOldNugget, MATCH(sTitle, sAbstract) AGAINST ('$search') as Score FROM Nuggets WHERE MATCH(sTitle, sAbstract) AGAINST ('$search')) ORDER BY Score DESC LIMIT $bottomLim,$_NUGPERPAGE";
		$results = $_DB->igroupsQuery($query);
		$nuggets = array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[0], $_DB, $row['bOldNugget']);
		}
		$query = "(SELECT iNuggetID, bOldNugget, MATCH(sTitle, sDescription) AGAINST ('$search') as Score FROM iGroupsNuggets WHERE MATCH(sTitle, sDescription) AGAINST ('$search'))UNION(SELECT iID, bOldNugget, MATCH(sTitle, sAbstract) AGAINST ('$search') as Score FROM Nuggets WHERE MATCH(sTitle, sAbstract) AGAINST ('$search')) ORDER BY Score DESC";
		$results = $_DB->igroupsQuery($query);
		$_COUNT = mysql_num_rows($results);
		displayNuggets($nuggets, "");

	}
	
	function orderByCriteria(){
		//dertermine the criteria used to sort by
		//and call the appropriate action
		if($_POST['criteria'] == "name"){
			//return the nuggets by name in abc order
			$nuggets =allByName();
			displayNuggets($nuggets, "");
		}else if($_POST['criteria'] == "author"){
			//return a list authros 
			$authors = nuggetAuthorList();
			printAuthorList($authors,"");
		}else if($_POST['criteria'] == "description"){
			$nuggets = allByDescription();
			displayNuggets($nuggets, "");
		}else if($_POST['criteria'] == "semester"){
			//samme results as no criteria
			$nuggets = allNuggetsByDate();
			displayNuggets($nuggets, "");
		}else if($_POST['criteria'] == "ipro"){
			//return a list of ipros
			$ipros = nuggetIproList();
			printIprosList($ipros, "");
		}
	}
	
	function searchWithCriteria(){
		print "<h2>Search Results</h2>";
		global $_DB;
		global $_NUGPERPAGE;
                global $_COUNT;
                $multiplier = $_POST['multiplier'];
                $bottomLim = $multiplier * 10;
                $topLim = $multiplier * 10 + 10;
		//determine the criteria requested to determine the search method
		//
		if($_POST['criteria'] == "name"){
		//search by the nugget name
			$search = $_POST['search'];
			if ($search == '')
				print "<p>No criteria provided. Please go back and provide a search term.</p>";
			else {
                        $query = "(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score,iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score, iID, bOldNugget FROM Nuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE)) ORDER BY score DESC LIMIT $bottomLim,$_NUGPERPAGE";
                        $results = $_DB->igroupsQuery($query);
                        $nuggets = array();
                        while($row = mysql_fetch_array($results)){
				$nuggets[] = new Nugget($row[1],$_DB, $row[2]);
			}
			$query = "(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score,iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE) AS score, iID, bOldNugget FROM Nuggets WHERE MATCH(sTitle) AGAINST('$search' IN BOOLEAN MODE)) ORDER BY score DESC";
			$results = $_DB->igroupsQuery($query);
	                $_COUNT = mysql_num_rows($results);

			displayNuggets($nuggets, "");
			}
		}else if($_POST['criteria']=='author'){
			$search = $_POST['search'];
			if ($search == '')
				print "<p>No criteria provided. Please go back and provide a search term.</p>";
			else {
			$query = "SELECT MATCH(sFName, sLName) AGAINST('$search' IN BOOLEAN MODE) AS score, iID FROM People WHERE MATCH(sFName, sLName) AGAINST('$search' IN BOOLEAN MODE) ORDER BY score DESC LIMIT $bottomLim,$_NUGPERPAGE";
			//if the length of the search is 3 characters or fewer, have to use the old fashioned test
			$authors = array();
			if(strlen($search) < 4){
				$query = "SELECT sLName, iID FROM People WHERE sLName = '$search'";
			}
			//if there is a space make sure that both names are larger than 3 characters, if they are not do a field by field comparison
			if(strpos($search, ' ')){
				//two names check length of each
				if(strlen($search) < 9 || strpos($search, ' ') < 4 || strlen($search) - strpos($search, ' ') <= 4){
					//do individual checks
					$str1 = substr($search, 0, strpos($search, ' '));
					$str2 = substr($search, strpos($search, ' ')+1);
					$specialQuery1 = "SELECT sFName, iID FROM People WHERE (sLName = '$str2' AND sFName = '$str1') OR (sFname = '$str2' AND sLName = '$str1')";
				}
			}
			if(isset($specialQuery1)){
				$newResults = $_DB->igroupsQuery($specialQuery1);
				while($row = mysql_fetch_array($newResults)){
					$person = new Person($row[1], $_DB);
					if (count($person->getNuggets()) > 0)
						$authors[] = $person;
				}	
			}
			$results = $_DB->igroupsQuery($query);
			while($row = mysql_fetch_array($results)){
				$person = new Person($row[1], $_DB);
                                if (count($person->getNuggets()) > 0)
                                        $authors[] = $person;
			}
			printAuthorList($authors, "");
			}
		}else if($_POST['criteria'] == "description"){
			$search = $_POST['search'];
			if ($search == '')
				print "<p>No criteria provided. Please go back and provide a search term.</p>";
			else {
			//if search is too small do default search
			if(strlen($search) < 4){
				$query = "(SELECT iNugget, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE sDescription LIKE '%$search%')UNION(SELECT iID, iID, bOldNugget FROM Nuggets WHERE sAbstract LIKE '%$search%' LIMIT $bottomLim,$_NUGPERPAGE)";
			}else{
				$query = "(SELECT MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE) as Score, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE) as Score, iID, bOldNugget FROM Nuggets WHERE MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE))ORDER BY Score DESC LIMIT $bottomLim,$_NUGPERPAGE";
			}
			$results = $_DB->igroupsQuery($query);
			$nuggets = array();
			while($row = mysql_fetch_array($results)){
				$nuggets[] = new Nugget($row[1], $_DB, $row[2]);
			}
			if(strlen($search) < 4){
                                $query = "(SELECT iNugget, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE sDescription LIKE '%$search%')UNION(SELECT iID, iID, bOldNugget FROM Nuggets WHERE sAbstract LIKE '%$search%')";
                        }else{
                                $query = "(SELECT MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE) as Score, iNuggetID, bOldNugget FROM iGroupsNuggets WHERE MATCH(sDescription) AGAINST('$search' IN BOOLEAN MODE))UNION(SELECT MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE) as Score, iID, bOldNugget FROM Nuggets WHERE MATCH(sAbstract) AGAINST('$search' IN BOOLEAN MODE))ORDER BY Score DESC";
                        }
			$results = $_DB->igroupsQuery($query);
			$_COUNT = mysql_num_rows($results);
			displayNuggets($nuggets, "");
			}
		}else if($_POST['criteria'] == 'semester'){
			$search = $_POST['search'];
			$query = "SELECT MATCH(sSemester) AGAINST('$search' IN BOOLEAN MODE) as Score, iID FROM Semesters WHERE MATCH(sSemester) AGAINST('$search' IN BOOLEAN MODE) ORDER BY Score DESC";
			$semesters = array();
			$results = $_DB->igroupsQuery($query);
			while($row = mysql_fetch_array($results)){
				$semesters[] = $row[1];
			}
			$ipros = array();
			//for each semester matching the criteria return the ipro list
			foreach($semesters as $semester){
				$groupQuery = "SELECT iProjectID FROM ProjectSemesterMap WHERE iSemesterID = $semester";
				$results = $_DB->igroupsQuery($groupQuery);
				while($row = mysql_fetch_array($results)){
					$id = $row[0];
					$ipros[] = new Group($id,0,$semester,$_DB);
				}
			}

			printIprosList($ipros, "");
		}else if($_POST['criteria']=='ipro'){
			$search = $_POST['search'];
			//do the most simple search firs
			$projects = array();
			$query1 = "SELECT iID FROM Projects WHERE sIITID LIKE ('$search')";
			$results1 = $_DB->igroupsQuery($query1);
			while($row = mysql_fetch_array($results1)){
				$projects[] = $row[0];
			}
			//union the two possible searches
			if(strpos($search, ' ')){
				$search2 = substr($search,0,4).substr($search,5);
				$query2 = "SELECT iID FROM Projects WHERE sIITID Like('$search2')";
				$results2 = $_DB->igroupsQuery($query2);
				while($row = mysql_fetch_array($results2)){
					$projects[] = $row[0];
				}
			}else{
				$search2 = substr($search,0,4)." ".substr($search,4);
				$query2 = "SELECT iID FROM Projects WHERE sIITID Like('$search2')";
				$results2 = $_DB->igroupsQuery($query2);
				while($row = mysql_fetch_array($results2)){
					$projects[] = $row[0];
				}
			}
			$query = "(SELECT MATCH(sIITID) AGAINST('$search' IN BOOLEAN MODE) as Score, iID FROM Projects WHERE MATCH(sIITID) AGAINST('$search' IN BOOLEAN MODE))UNION (SELECT MATCH(sIITID) AGAINST('$search2' IN BOOLEAN MODE) as Score, iID FROM Projects WHERE MATCH(sIITID) AGAINST('$search2' IN BOOLEAN MODE))Order By Score DESC";
			$results = $_DB->igroupsQuery($query);
			while($row = mysql_fetch_array($results)){
				$projects[] = $row[1];
			}
			//now add the other possiblities to the list
			$finalProject = array();
			foreach($projects as $project){
				$query = "SELECT iProjectID, iSemesterID FROM ProjectSemesterMap WHERE iProjectID = $project";
				$results = $_DB->igroupsQuery($query);
				while($row = mysql_fetch_array($results)){
					$finalProject[] = new Group($row[0],0,$row[1],$_DB);
				}
			}
		
			printIprosList($finalProject, "");
		}		

	}
	
	function displayNuggets($nuggets,$criteria){
		global $_NUGPERPAGE;
		if(count($nuggets) == 0){
			print "<p>No results found.</p>";
		}else{
?>
			<table>
				<tr>
			<th>Group</th><th>Semester</th><th>Nugget Title</th><th>Description/Abstract</th><th>Date Created</th>
			</tr>
<?php
			foreach($nuggets as $nug){
				print '<tr>';
				print '<td>'.$nug->getGroupName().'</td>';
				print '<td>'.$nug->getSemester()->getName().'</td>';
				$title = "<a href=\"viewNugget.php?nuggetID=".$nug->getID()."&amp;isOld=".$nug->getOld()."\">".$nug->getType()."</a>";

				if ($nug->isPrivate())
					$priv="&nbsp;(Private)";
				else
					$priv='';
				print "<td>$title$priv</td>";
				print '<td>'.$nug->getDescShort()."</td>";
				print '<td>'.$nug->getDate().'</td>';
				print '</tr>';
			}
?>
			</table>
		
<?php		}
	}
	function printIprosList($ipros, $criteria){
		global $_NUGPERPAGE;
		global $_DB;
		if(count($ipros) == 0){
			print "<p>No results found.</p>";
		}else{
?>
			<table>
				<tr>
<?php
			if(isset($criteria) && $criteria != ""){
				print "<td>Search Criteria</td>";
			}
?>				<td>IPRO</td><td>Semester</td><td>Description</td>
				</tr>
<?php
				foreach($ipros as $ipro){
					print '<tr>';
					if(isset($criteria) && $criteria != ""){
						print "<td>something</td>";
					}
					print "<td>";
					$id = $ipro->getID();
					print "<a href=\"viewIproNuggets?id=$id\">".$ipro->getName()."</a>";
					print "</td>";
					$semNum = $ipro->getSemester();
					$query = "SELECT sSemester FROM Semesters WHERE iID = $semNum";
					$result = $_DB->igroupsQuery($query);
					$row = mysql_fetch_array($result);
					print "<td>";
					print $row[0];
					print "</td>";
					print "<td>";
					print $ipro->getDesc();
					print "</td>";
					print "</tr>";
				}
				print "</table>";
		}
	}
	function printAuthorList($authors, $criteria){
		global	$_NUGPERPAGE;
		if(count($authors) == 0){
			print "<p>No results found.</p>";
		}else{
?>
			<table>
				<tr>
			<th>Nugget</th><th>Group</th><th>Semester</th>
			</tr>
<?php
			foreach($authors as $author){
				print "<tr><th colspan='3'>".$author->getFullName()."</td></tr>";
				//generate nuggetList per author
				$tempNugs = $author->getNuggets();
				foreach($tempNugs as $nug){
					$name = $nug->getType();
					if ($name != null) {
						$id = $nug->getID();
						if ($nug->isOld()) 
							$old = 1;
						else
							$old = 0;
						print "<tr><td><a href=\"viewNugget.php?nuggetID=$id&amp;isOld=$old\">$name</a></td>";
						$grp = $nug->getGroupName();
						print "<td>$grp</td>";
						$sem = $nug->getSemester()->getName();
						print "<td>$sem</td></tr>";
					}
				}
			}
			print "</table>";
		}
	}

	function getSemesters(){
		global $_DB;
		$query = "SELECT iID, sSemester FROM Semesters ORDER BY sSemester";
		$results = $_DB->igroupsQuery($query);
		$semesters = array();
		while($row = mysql_fetch_array($results)){
			$semesters[$row[1]] = $row[0];

		}
		return $semesters;
	}

	function getIpros(){
		global $_DB;
		$query = "SELECT iID, sIITID FROM Projects ORDER BY sIITID";
		$results = $_DB->igroupsQuery($query);
		$ipros = array();
		while($row = mysql_fetch_array($results)){
			$ipros[$row[1]] = $row[0];
		}
		return $ipros;
	}
	//Form processing:
	
	if(isset ($_POST['multiplier'])){
		//this variables are set when a user has pressed search
		$_DIRECTION = $_POST['direction'];
		$_MULTIPLIER = $_POST['multiplier'];
		
	}
	if(isset($_POST['semester'])){
		$semester = $_POST['semester'];
		$ipro = new Group($_POST['ipro'], 0,$semester, $_DB);
		$ipros = array();
		$ipros[] = $ipro;
		printIprosList($ipros, "");
	}
	if(isset($_POST['criteria'])){
		//they have submitted the form now process the request
		//check for search everything case
		$_SESSION['criteria'] = $_POST['criteria'];
		if($_POST['criteria'] == "all" AND $_POST['search']==""){
			//we have to go to browse mode
			//browseNuggets();
			print "<p>No criteria provided. Please try again, providing a search term.</p>";
		}else{
			if($_POST['criteria'] == "all" AND $_POST['search'] != ""){
				//search all fields for a particular word
				searchAllFields();
				$_SESSION['search'] = $_POST['search'];
			}
		}
		if($_POST['criteria'] != "all" and $_POST['search'] == ""){
			//Order all the nuggets based on a certain criteria
			//orderByCriteria();
			print "<p>No criteria provided. Please try again, providing a search term.</p>";
		}else{
			if($_POST['criteria'] != "all" and $_POST['search'] != ""){
				//search a particular field for a desired phrase
				searchWithCriteria();
				$_SESSION['search'] = $_POST['search'];
			}
		}
	}
	
	if(isset($_GET['nugget'])){
		//nugget display view
	}
?>
<form id='navigate' method = 'post' action='main.php'>
	<div id='navigatePart'>
<?php
		if(isset($_POST['search']) && ($_MULTIPLIER > 0))
				print "<input type='button' onclick='JavaScript:prevResults()' value='&lt;' />";
		print "&nbsp;";
		if($_COUNT > 0) {
			if(($_MULTIPLIER*$_NUGPERPAGE+$_NUGPERPAGE) < $_COUNT)
				$upBound = $_MULTIPLIER*$_NUGPERPAGE+$_NUGPERPAGE;
			else
				$upBound = $_COUNT;
			print " Showing results ".($_MULTIPLIER*$_NUGPERPAGE+1)." through ".($upBound)." of $_COUNT.&nbsp";
		}
		if(isset($_POST['search']) && ($_MULTIPLIER * $_NUGPERPAGE+$_NUGPERPAGE < $_COUNT))
			print "<input type='button' onclick='JavaScript:nextResults()' value='&gt;' />";
		print "<input type='hidden' name='multiplier' />";
		print "<input type='hidden' name='direction' />";
		$search = $_SESSION['search'];
		$criteria = $_SESSION['criteria'];
		print "<input type='hidden' name='search' value='$search' />";
		print "<input type='hidden' name='criteria' value='$criteria' />";
?>
	</div>
</form></div>
</body>
</html>
	
