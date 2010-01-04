<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/nugget.php');
	include_once('classes/semester.php');
	include_once('classes/file.php');
	include_once('classes/quota.php');
	
	$_SESSION['currentGroup'] = $currentGroup;
	$currentQuota = new Quota($currentGroup, $db);
	
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
		$nug = $nugget->getType();
		if($nug == 'Code of Ethics')
			$nugprint = 'Ethics Statement';
		else if($nug == 'Website')
			$nugprint = 'Website (optional)';
		else if($nug == 'Midterm Report')
			$nugprint = 'Midterm Presentation';
		else if($nug == 'Team Minutes')
			$nugprint = 'Team Minutes (optional)';
		else if($nug == 'Final Report')
			$nugprint = 'Final Report or Grant Proposal';
		else
			$nugprint = $nug;
		echo $nugprint.'</div>';
		echo '<div class="item"><strong>Description:</strong> '.htmlspecialchars($nugget->getDesc()).'</div>';
		echo '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		echo '<div class="item"><strong>Security:</strong> ';
		if($nugget->isPrivate())
			echo 'Protected (Not publicly viewable)';
		else
			echo 'Public';
		echo "</div>";
		echo '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0)
		{
			echo '<ul>';
			
			foreach($authors as $author)
			{
				//print the author name
				echo '<li>';
				echo $author->getFullName();
				echo '</li>';
			}
			echo '</ul></div>';
		}
		else
			echo "There are no authors for this nugget.</div>";
		echo '<div class="item"><strong>Files:</strong>'.'<br />';
		
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
				echo '</li>';
			}
			echo '</ul></div>';
		}
		else
			echo 'There are no files for this nugget.</div>';
	}
	
	//----------Start XHTML Output----------------------------------//
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Nuggets</title>
<script type="text/javascript">
//<![CDATA[
	function nuggetRedirect(nugget){
		form = document.getElementById("redirectForm");
		form.nuggetType.value= nugget;
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
	
	if(isset($_GET['nug']))
	{
		//proceed with view
		
		//check that the nugget belongs to the group before displaying
		if(isset($_GET['isOld']))
			$nugget = new Nugget($_GET['nug'], $db, 1);
		else
			$nugget = new Nugget($_GET['nug'], $db, 0);
		$nugGroup = $nugget->getGroupID();
		if($nugGroup != $currentGroup->getID())
		{
			echo 	"<script type=\"text/javascript\">
					<!--
					window.location = 'index.php'
					//-->
					</script>";
		}
		else //proceed
			printNugget($nugget);
	}
	else
	{
	echo 	"<script type=\"text/javascript\">
					<!--
					window.location = 'nuggets.php'
					//-->
					</script>";
	}
?>

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlcontentfoot.php');
  // ends main container
  /****** end html footer*****/
?>

</body></html>
