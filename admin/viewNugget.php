<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/nugget.php');
	include_once('../classes/semester.php');
	include_once('../classes/file.php');
	include_once('../classes/quota.php');
	
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
		echo $nugget->getType()."</div>";
		if($nugget->getType() == 'Abstract' || $nugget->getType() == 'Poster')
			echo '<div class="item"><strong>Approved:</strong> '.($nugget->isVerified() ? "{$nugget->whoVerified()->getFullName()} at {$nugget->whenVerified()}" : 'No').'</div>';
		echo '<div class="item"><strong>Description:</strong> '.htmlspecialchars($nugget->getDesc()).'</div>';
		echo '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		echo '<div class="item"><strong>Security:</strong> ';
		if ($nugget->isPrivate())
			echo 'Protected (Not publically viewable)';
		else
			echo 'Public';
		echo "</div>\n";
		echo '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors))
		{
			echo '<ul>';
			
			foreach($authors as $author)
			{
				//print the author name
				echo '<li>';
				echo $author->getFullName();
				echo "</li>\n";
			}
			echo "</ul></div>\n";
		}
		else
			echo "There are no authors for this nugget.</div>";
		echo '<div class="item"><strong>Files:</strong><br />';
		
		if(count($files))
		{
			echo '<ul>';
			foreach($files as $file)
			{
				echo '<li>';
				if($nugget->isOld())
					echo "<a href=\"downloadOld.php?file={$file[0]}\">{$file[1]}</a>&nbsp;";
				else
					echo '<a href="download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp;';
				echo "</li>\n";
			}
			echo '</ul></div>';
		}
		else
			echo "There are no files for this nugget.</div>";
	}
	
	//---------Begin XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/nuggets.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/nuggets.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Nuggets</title>
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
		 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/

	if(isset($_GET['nuggetID']))
	{
		//proceed with view
		
		//check that the nugget belongs to the group before displaying
		if(isset($_GET['isOld']) && $_GET['isOld'] == 1)
			$nugget = new Nugget($_GET['nuggetID'], $db, 1);
		else
			$nugget = new Nugget($_GET['nuggetID'], $db, 0);
		
		$nugGroup = $nugget->getGroupID();
		printNugget($nugget);
	}
?>
<br />
<a href="nuggets.php">Back</a> 
</div></body></html>
