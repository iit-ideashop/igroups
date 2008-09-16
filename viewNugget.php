<?php
	include_once("checklogin.php");
	include_once( "classes/nugget.php" );
	include_once( "classes/semester.php" );
	include_once( "classes/file.php" );
	include_once( "classes/quota.php" );
	
	$_SESSION['currentGroup'] = $currentGroup;
	$currentQuota = new Quota( $currentGroup, $db );
	
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
		$nug = $nugget->getType();
		if($nug == "Code of Ethics")
			$nugprint = "Ethics Statement";
		else if($nug == "Website")
			$nugprint = "Website (optional)";
		else if($nug == "Midterm Report")
			$nugprint = "Midterm Presentation";
		else if($nug == "Team Minutes")
			$nugprint = "Team Minutes (optional)";
		else if($nug == "Final Report")
			$nugprint = "Final Report or Grant Proposal";
		else
			$nugprint = $nug;
		print $nugprint."</div>";
		print '<div class="item"><strong>Description:</strong> '.htmlspecialchars($nugget->getDesc()).'</div>';
		print '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		print '<div class="item"><strong>Security:</strong> ';
		if ($nugget->isPrivate())
			print "Protected (Not publicly viewable)";
		else
			print "Public";
		print "</div>";
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
<title>iGroups - Nuggets</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		table.nugget {
			width: 70%;
		}
		
		table.nugget tr {
			
		}	
			
		table.nugget td {
			border: 3px solid #ccc;
			padding: 20px;
			width:50%;
		}	
		
		.item {
			padding-top:5px;
			padding-bottom:5px;
			border-bottom:1px solid #ccc;
		}	
	</style>
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
	
	if( isset ($_GET['nug'])){
		//proceed with view
		
		//check that the nugget belongs to the group before displaying
		if (isset($_GET['isOld']))
			$nugget = new Nugget($_GET['nug'], $db, 1);
		else
			$nugget = new Nugget($_GET['nug'], $db, 0);
		$nugGroup = $nugget->getGroupID();
		if($nugGroup != $currentGroup->getID()){
			print 	"<script type=\"text/javascript\">
					<!--
					window.location = 'index.php'
					//-->
					</script>";
		}else{
			//proceed
			printNugget($nugget);
		}
	}else{
	print 	"<script type=\"text/javascript\">
					<!--
					window.location = 'nuggets.php'
					//-->
					</script>";
	}
?>
</div></body>
</html>
