<?php
	session_start();

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/announcement.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
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

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<head>
<title>iGROUPS 2.0 - Announcement Editor</title>
<style type="text/css">
@import url("../default.css");	

#editor-container {
	width:80%;
	margin:0 auto;
	background-color: #EEEEEE;	
	background-image: url('../img/menu-right-border.gif');
	background-repeat: repeat-y;	
	background-position: right;
}

#box-top {
	height:12px;
}
			
#box-topleft {
	height:12px;
	width:9px;
	float:left;
	background-image: url('../img/top-left.gif');
	background-repeat: no-repeat;
}

*html #box-topleft {
	position: relative;
	margin-left: -3px;
}

#box-topright {
	height:12px;
	width:16px;
	float:right;
	background-image: url('../img/top-right.gif');
	background-repeat: no-repeat;
}

#box-bottom {
	height:33px;
	background-repeat: repeat-x;	
	background-position: bottom;
	background-image: url('../img/bottom-slice.gif');
}

#box-bottomleft {
	height:33px;
	width:11px;
	float:left;
	background-image: url('../img/bottom-left.gif') ;
}

*html #box-bottomleft {
	position: absolute;
	margin-left: -3px;
}

#box-bottomright {
	height:33px;
	width:24px;
	float:right;
	background-image: url('../img/bottom-right.gif');
}

#box-content {
	text-align:left;
	margin-right:20px;
	margin-left:10px;
	position:relative;
}

#editor {
	width:60%;
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
<script language="javascript" type="text/javascript" src="speller/spellChecker.js">
</script>

<!-- Call a function like this to handle the spell check command -->
<script language="javascript" type="text/javascript">
function openSpellChecker() {
	var speller = new spellChecker();
	speller.spellCheckAll();
}
</script>
<script type="text/javascript">
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
	<div id="editor-container">
		<div id="box-top">
			<div id="box-topleft">
			</div>					
			<div id="box-topright">
			</div>
		</div>
		<div id="box-content">
			<div id="editor">
				<h1>Announcement Editor</h1>
				<i>(Delete Annoucements 14 mo. after expiration)</i><br>
				<form method="post" action="announcements.php">
					<input type="hidden" id="id" name="id" value="new" />
					Expiration Date (MM/DD/YY):<input type="text" id="date" name="date" size=15><input type="button" onClick="showCalendar(event);" value="Select Date"><br>
					Heading: <input type="text" id="heading" name="heading" size=50><br>
					Body:<br><textarea name="body" id="body" cols=45 rows=8></textarea><br>
					<input type="submit" id="add" name="addannouncement" value="Add Announcement">
					<input type="submit" id="edit1" name="editannouncement" value="Edit Announcement">
					<input type="submit" id="edit2" name="deleteannouncement" value="Delete Announcement">
					<input type="button" onClick="clearEditor()" value="Reset Editor">
				</form>
			</div>
			<div id="announcement-list">
				<h2>Current Announcements</h2>
<?php
				$announcementResults = $db->igroupsQuery( "SELECT iID FROM News ORDER BY iID DESC" );
				while ( $row = mysql_fetch_row( $announcementResults ) ) {
					$announcement = new Announcement( $row[0], $db );
					print "<li>";
					print "<a href='#' onClick=\"loadEditor( ".$announcement->getID().", '".$announcement->getHeadingJava()."', '".$announcement->getBodyJava()."', '".$announcement->getExpirationDate()."' );\">";
					print $announcement->getHeadingHTML()."</a></li>";
				}
?>
			</div>
		</div>	
		<div id="box-bottom">
			<div id="box-bottomleft">
			</div>					
			<div id="box-bottomright">
			</div>
		</div>
	</div>
	<div id="calendarmenu">
		<table>
			<tr>
<?php
				$currentMonth = date( "n" );
				$currentYear = date( "Y" );
				for ( $i=$currentMonth; $i<$currentMonth+4; $i++ ) {
					print "<td valign='top'>";
					print "<table>";
					print "<tr><td colspan=7>".date( "F Y", mktime( 0, 0, 0, $i, 1, $currentYear ) )."</td></tr>";
					print "<tr><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>";
					$startDay = date( "w", mktime( 0, 0, 0, $i, 1, $currentYear ) );
					$endDay = date( "j", mktime( 0, 0, 0, $i+1, 0, $currentYear ) );
					if ( $startDay != 0 )
						print "<tr><td colspan=$startDay></td>";
					$weekDay = $startDay;
					for ( $j=1; $j<=$endDay; $j++ ) {
						if ( $weekDay == 0 )
							print "<tr>";
						print "<td><a href='#' onClick=\"selectDate('".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."');\">$j</a></td>";
						$weekDay++;
						if ( $weekDay == 7 ) {
							print "</tr>";
							$weekDay = 0;
						}
					}
					if ( $weekDay != 0 ) 
						print "<td colspan=".(7-$weekDay)."></td></tr>";
					print "</table>";
					print "</td>";
				}
?>
			</tr>
		</table>
	</div>
</body>
</html>
