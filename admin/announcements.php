<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/announcement.php" );
	
	$db = new dbConnection();
	
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], "@") === FALSE)
			$userName = $_COOKIE['userID']."@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
	}
	else
		die("You are not logged in.");
		 
	if ( !$currentUser->isAdministrator() )
		die("You must be an administrator to access this page.");
		
	if ( isset( $_POST['addannouncement'] ) ) {
		createAnnouncement( $_POST['heading'], $_POST['body'], $_POST['date'], $db );
		$message = "Announcement successfully added.";
	}	
		
	if ( isset( $_POST['editannouncement'] ) ) {
		$ann = new Announcement( $_POST['id'], $db );
		$ann->setHeading( $_POST['heading'] );
		$ann->setBody( $_POST['body'] );
		$ann->setExpirationDate( $_POST['date'] );
		$ann->updateDB();
		$message = "Announcement successfully edited.";
	}
	
	if ( isset( $_POST['deleteannouncement'] ) ) {
		$ann = new Announcement( $_POST['id'], $db );
		$ann->delete();
		$message = "Announcement successfully deleted.";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Announcement Editor</title>
<link rel="stylesheet" href="../default.css" type="text/css" />
<style type="text/css">
.box {
	width:40%;
}

#announcement-list {
	position:absolute;
	left:70%;
	top:0%;
}
		
#calendarmenu {
	border:solid 1px #000;
	background-color:#fff;
	visibility:hidden;
	position:absolute;
	left:50px;
}

#edit1 {
	display:none;
}

#edit2 {
	display:none;
}

h2 {
	font-size:11pt;
	color:#CC0000;
	margin-bottom:5px;
}
</style>
<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript" src="speller/spellChecker.js">
</script>

<!-- Call a function like this to handle the spell check command -->
<script type="text/javascript">
function openSpellChecker() {
	var speller = new spellChecker();
	speller.spellCheckAll();
}
function showCalendar( event ) {
	document.getElementById('calendarmenu').style.top=(event.clientY+document.documentElement.scrollTop+25)+"px";
	document.getElementById('calendarmenu').style.visibility='visible';
}

function selectDate( date ) {
	document.getElementById('date').value=date;
	document.getElementById('calendarmenu').style.visibility='hidden';
}

function loadEditor( id, heading, body, date ) {
	document.getElementById('id').value=id;
	document.getElementById('heading').value=heading;
	document.getElementById('body').value=body;
	document.getElementById('date').value=date;
	document.getElementById('edit1').style.display='inline';
	document.getElementById('edit2').style.display='inline';
	document.getElementById('add').style.display='none';
}

function clearEditor() {
	document.getElementById('id').value="new";
	document.getElementById('heading').value="";
	document.getElementById('body').value="";
	document.getElementById('date').value="";
	document.getElementById('edit1').style.display='none';
	document.getElementById('edit2').style.display='none';
	document.getElementById('add').style.display='inline';
}
</script>
</head>
<body>
<?php
	require("sidebar.php");
	print "<div id=\"content\"><div id=\"topbanner\">Announcements</div>";
?>
	<div class="box">
		<span class="box-header">Announcement Editor</span>
		<i>(Delete Annoucements 14 mo. after expiration)</i><br />
		<form method="post" action="announcements.php"><fieldset>
			<input type="hidden" id="id" name="id" value="new" />
			<label for="date">Expiration Date (MM/DD/YY):</label><input type="text" id="date" name="date" size="15" /><input type="button" onclick="calwin=dhtmlwindow.open('calbox', 'div', 'calendarmenu', 'Select date', 'width=600px,height=165px,left=300px,top=100px,resize=0,scrolling=0'); return false" value="Select Date" /><br />
			<label for="heading">Heading:</label><input type="text" id="heading" name="heading" size="50" /><br />
			<label for="body">Body:</label><br /><textarea name="body" id="body" cols="45" rows="8"></textarea><br />
			<input type="submit" id="add" name="addannouncement" value="Add Announcement" />
			<input type="submit" id="edit1" name="editannouncement" value="Edit Announcement" />
			<input type="submit" id="edit2" name="deleteannouncement" value="Delete Announcement" />
			<input type="button" onclick="clearEditor()" value="Reset Editor" />
		</fieldset></form>
	</div>	
	<div id="calendarmenu">
		<table>
			<tr>
<?php
				$currentMonth = date( "n" );
				$currentYear = date( "Y" );
				for ( $i=$currentMonth; $i<$currentMonth+4; $i++ ) {
					print "<td valign=\"top\">";
					print "<table>";
					print "<tr><td colspan=\"7\">".date( "F Y", mktime( 0, 0, 0, $i, 1, $currentYear ) )."</td></tr>";
					print "<tr><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>";
					$startDay = date( "w", mktime( 0, 0, 0, $i, 1, $currentYear ) );
					$endDay = date( "j", mktime( 0, 0, 0, $i+1, 0, $currentYear ) );
					if ( $startDay != 0 )
						print "<tr><td colspan=\"$startDay\"></td>";
					$weekDay = $startDay;
					for ( $j=1; $j<=$endDay; $j++ ) {
						if ( $weekDay == 0 )
							print "<tr>";
						print "<td><a href=\"#\" onclick=\"selectDate('".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."'); calwin.close();\">$j</a></td>";
						$weekDay++;
						if ( $weekDay == 7 ) {
							print "</tr>";
							$weekDay = 0;
						}
					}
					if ( $weekDay != 0 ) 
						print "<td colspan=\"".(7-$weekDay)."\"></td></tr>";
					print "</table>";
					print "</td>";
				}
?>
			</tr>
		</table>
	</div>
<div id="announcement-list">
				<h2>Current Announcements</h2>
<ul>
<?php
				$announcementResults = $db->igroupsQuery( "SELECT iID FROM News ORDER BY iID DESC" );
				while ( $row = mysql_fetch_row( $announcementResults ) ) {
					$announcement = new Announcement( $row[0], $db );
					print "<li>";
					print "<a href=\"#\" onclick=\"loadEditor( ".$announcement->getID().", '".htmlspecialchars($announcement->getHeadingJava())."', '".htmlspecialchars($announcement->getBodyJava())."', '".$announcement->getExpirationDate()."' );\">";
					print htmlspecialchars($announcement->getHeadingJava())."</a></li>";
				}
?>
</ul>
			</div>
</div>
</body>
</html>
