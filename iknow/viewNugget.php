<?php
	session_start();

	include_once("../globals.php");
	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/nugget.php" );
	include_once( "../classes/semester.php" );
	include_once( "../classes/file.php" );
	include_once( "../classes/quota.php" );

	$_DB = new dbConnection();
	
	function peopleSort( $array ) {
		$newArray = array();
		foreach ( $array as $person ) {
			$newArray[$person->getCommaName()] = $person;
		}
		ksort( $newArray );
		return $newArray;
	}
	
	function printNugget($nugget){
		$authors = $nugget->getAuthors();
		$files = $nugget->getFiles();
		$semester = $nugget->getSemester();
		
		print "<div class=\"item\"><strong>Nugget Type/Name:</strong> ";
		//used to print a nugget that is from a prior semester or for viewing purposes only
		print $nugget->getType()."</div>";
		print '<div class="item"><strong>Description:</strong> '.$nugget->getDesc().'</div>';
		print '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		print '<div class="item"><strong>Authors:</strong> <br />';
		
		if(count($authors) > 0){
			print '<ul>';
			
			foreach($authors as $author){
				//print the author name
				print '<li>';
				print $author->getFullName();
				print '</li>';
			}
			print '</ul></div>';
		}
		else{
			print "There are no authors for this nugget.</div>";
		}
		print '<div class="item"><strong>Files:</strong>'.'<br />';
		
		if(count($files)>0){
			print '<ul>';
			foreach($files as $file){
				print '<li>';
				if ($nugget->isOld())
					print "<a href=\"downloadOld.php?file={$file[0]}\">{$file[1]}</a>&nbsp;";
				else
					print '<a href="download.php?id='.$file->getID().'">'.$file->getNameNoVer().'</a>&nbsp;';
				print '</li>';
			}
			print '</ul></div>';
		}
		else{
			print "There are no files for this nugget.</div>";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title><?php echo $appname; ?> - Nuggets</title>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"../skins/$skin/nuggets.css\" type=\"text/css\" />\n";
?>
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
require("sidebar.php");
	print "<div id=\"content\">";
	if( isset ($_GET['nuggetID'])){
		//proceed with view
		
		//check that the nugget belongs to the group before displaying
		if (isset($_GET['isOld']) && $_GET['isOld'] == 1)
			$nugget = new Nugget($_GET['nuggetID'], $_DB, 1);
		else
			$nugget = new Nugget($_GET['nuggetID'], $_DB, 0);
		
		$nugGroup = $nugget->getGroupID();
		if ($nugget->isPrivate())
			print "<h3>Access Denied</h3><p>This nugget has been protected by its creator. It can only be viewed by members of its group and IPRO Office Staff. If you are a group member, login to iGroups and access this nugget through the group's nugget management interface. If you are IPRO Office Staff, login to iGroups and access this nugget through administrative tools.</p>";
		else
			printNugget($nugget);
	}
?>
<br />
<a href="viewIproNuggets.php?id=<?php print "$nugGroup"; ?>">Back</a> 
</div></body>
</html>
