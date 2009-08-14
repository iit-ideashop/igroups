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

	if(is_numeric($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}
	
	function printNugget($nugget)
	{
		$authors = $nugget->getAuthors();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		
		echo "<div class=\"item\"><strong>Nugget Type/Name:</strong> ";
		//used to print a nugget that is from a prior semester or for viewing purposes only
		echo $nugget->getType().'</div>';
		echo '<div class="item"><strong>Description:</strong> '.$nugget->getDesc().'</div>';
		echo '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		echo '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0)
		{
			echo '<ul>';
			foreach($authors as $author)
				echo "<li>{$author->getFullName()}</li>\n";
			echo '</ul></div>';
		}
		else
			echo "There are no authors for this nugget.</div>";
		echo '<div class="item"><strong>Files:</strong><br />';
		
		if(count($files) > 0)
		{
			echo '<ul>';
			foreach($files as $file)
			{
				echo '<li>';
				if($nugget->isOld())
					echo "<a href=\"downloadOld.php?file={$file[0]}\">{$file[1]}</a>&nbsp;";
				else
					echo '<a href="download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp;';
				echo "</li>\n";;
			}
			echo '</ul></div>';
		}
		else
			echo "There are no files for this nugget.</div>";
	}
	
	//-------Start XHTML Output-------------------------------------//
	
	require('../doctype.php');
	require('appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Nuggets</title>

<script type="text/javascript">
//<![CDATA[
	function nuggetRedirect(nugget)
	{
		form = document.getElementById("redirectForm");
		form.nuggetType.value= nugget;
		form.submit();
	}
//]]>	
</script>
</head>
<body>
<?php
	require('sidebar.php');
	echo "<div id=\"content\">";
	if(isset($_GET['nuggetID']))
	{
		//proceed with view
		
		//check that the nugget belongs to the group before displaying
		if(isset($_GET['isOld']) && $_GET['isOld'] == 1)
			$nugget = new Nugget($_GET['nuggetID'], $db, 1);
		else
			$nugget = new Nugget($_GET['nuggetID'], $db, 0);
		
		$nugGroup = $nugget->getGroupID();
		if($nugget->isPrivate())
			echo "<h3>Access Denied</h3><p>This nugget has been protected by its creator. It can only be viewed by members of its group and IPRO Office Staff. If you are a group member, login to iGroups and access this nugget through the group's nugget management interface. If you are IPRO Office Staff, login to iGroups and access this nugget through administrative tools.</p>";
		else
			printNugget($nugget);
	}
?>
<br />
<a href="viewIproNuggets.php?id=<?php echo $nugGroup; ?>">Back</a> 
</div></body></html>
