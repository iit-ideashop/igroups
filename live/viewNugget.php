<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/nugget.php" );
	include_once( "classes/semester.php" );
	include_once( "classes/file.php" );
	include_once( "classes/quota.php" );

	$_DB = new dbConnection();
	$msg = array();
	if ( isset( $_SESSION['userID'] ) )
		$currentUser = new Person( $_SESSION['userID'], $_DB);

	else
		 die("You are not logged in.");

	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) ){
		$_CURRENTGROUP= new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $_DB );
		$currentGroup = $_CURRENTGROUP;
		$_SESSION['currentGroup'] = $currentGroup;
	}
	else{
		die("You have not selected a valid group.");
	}
	$currentQuota = new Quota( $currentGroup, $_DB );
	
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
		
		print "<div class='item'><strong>Nugget Type/Name:</strong> ";
		//used to print a nugget that is from a prior semester or for viewing purposes only
		print $nugget->getType()."</div>";
		print '<div class="item"><strong>Description:</strong> '.$nugget->getDesc().'</div>';
		print '<div class="item"><strong>Date Created:</strong> '.$nugget->getDate().'</div>';
		print '<div class="item"><strong>Security:</strong> ';
		if ($nugget->isPrivate())
			print "Protected (Not publically viewable)";
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
					print "<a href='downloadOld.php?file={$file[0]}'>{$file[1]}</a>&nbsp;";
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
	<script language="JavaScript" type="text/Javascript">
	<!--
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}

		function nuggetRedirect(nugget){
			form = document.getElementById("redirectForm");
			form.nuggetType.value= nugget;
			form.submit();
		}
	//-->	
	</script>
</head>

<body>	
<?php
require("sidebar.php");
print "<div class=\"content\">";
	//Prints all notifications
	if( isset ($msg)){
		foreach($msg as $ms){
			print '<script language = "javascript">showMessage(\''.$ms.'\')</script>';
		}
	}
	
	if( isset ($_GET['nug'])){
		//proceed with view
		
		//check that the nugget belongs to the group before displaying
		if (isset($_GET['isOld']))
			$nugget = new Nugget($_GET['nug'], $_DB, 1);
		else
			$nugget = new Nugget($_GET['nug'], $_DB, 0);
		$nugGroup = $nugget->getGroupID();
		if($nugGroup != $currentGroup->getID()){
			print 	"<script type='text/javascript'>
					<!--
					window.location = 'index.php'
					//-->
					</script>";
		}else{
			//proceed
			printNugget($nugget);
		}
	}else{
	print 	"<script type='text/javascript'>
					<!--
					window.location = 'nuggets.php'
					//-->
					</script>";
	}
?>
</div></body>
</html>
