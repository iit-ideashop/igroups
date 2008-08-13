<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/groupannouncement.php" );
	include_once( "classes/event.php" );
	include_once( "classes/email.php" );
	include_once( "classes/file.php" );
	include_once( "classes/grouppicture.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Group Home Page</title>
	<style type="text/css">
		@import url("default.css");
		
		#recent {
			margin-top:10px;
			float:right;
			width:50%;
		}
		
		#emails {
			background-color:#EEEEEE;
			background-image: url('img/menu-right-border.gif');
			background-repeat: repeat-y;	
			background-position: right;
		}
		
		#files {
			margin-top:10px;
			background-color:#EEEEEE;
			background-image: url('img/menu-right-border.gif');
			background-repeat: repeat-y;	
			background-position: right;
		}
		
		#pictures {
			margin:10px;
		}

		#announcements {
			margin:10px;
		}
		
		.dateHeading {
			background:#EEE;
			border-bottom: 1px solid #666;
		}
		
		.prop {
			float:right;
			height:100px;
			width:1px;
			overflow:hidden;
		}
			
		#calendarmenu {
			border:solid 1px #000;
			background-color:#fff;
			visibility:hidden;
			position:absolute;
			left:50px;
		}
	
		.event {
			border:solid 1px #000;
			background-color:#fff;
			position:absolute;
			visibility:hidden;
			width:200px;
			padding:5px;
			top:0;
			left:0;
			overflow:hidden;
		}
		
		.window {
			width:500px;
			background-color:#FFF;
			border: 1px solid #000;
			visibility:hidden; 
			position:absolute;
			left:20px;
			top:20px;
		}
		
		.window-topbar {
			padding-left:5px;
			font-size:14pt;
			color:#FFF;
			background-color:#C00;
		}
		
		.window-content {
			padding:5px;
		}
		
		#box-top {
			height:12px;
		}
		
		#box-topleft {
			height:12px;
			width:9px;
			float:left;
			background-image: url('img/top-left.gif');
			background-repeat: no-repeat;
		}

		#box-topright {
			height:12px;
			width:16px;
			float:right;
			background-image: url('img/top-right.gif');
			background-repeat: no-repeat;
		}
		
		#box-bottom {
			height:33px;
			background-repeat: repeat-x;	
			background-position: bottom;
			background-image: url('img/bottom-slice.gif');
		}
		
		#box-bottomleft {
			height:33px;
			width:11px;
			float:left;
			background-image: url('img/bottom-left.gif');
		}
		
		#box-bottomright {
			height:33px;
			width:24px;
			float:right;
			background-image: url('img/bottom-right.gif');
		}
		
		#box-content {
			padding-right:20px;
			padding-left:5px;
		}
		
	</style>
	<script type="text/javascript">
		function showEvent( id, x, y ) {
			document.getElementById(id).style.top=(y+20)+"px";
			if ( x > window.innerWidth/2 )
				document.getElementById(id).style.left=(x-200)+"px";
			else
				document.getElementById(id).style.left=x+"px";
			document.getElementById(id).style.visibility='visible';
		}
		
		function hideEvent( id ) {
			document.getElementById(id).style.visibility='hidden';
		}
		
		function showCalendar( event ) {
			document.getElementById('calendarmenu').style.top=(event.clientY+document.documentElement.scrollTop+25)+"px";
			document.getElementById('calendarmenu').style.visibility='visible';
		}
		
		function selectDate( date ) {
			document.getElementById('date').value=date;
			document.getElementById('calendarmenu').style.visibility='hidden';
		}
		
		function editAnnouncement(id, heading, body) {
			document.getElementById('editid').value = id;
			document.getElementById('editheading').value = heading;
			document.getElementById('editbody').value = body;
			document.getElementById('edit-announcement').style.visibility='visible';
		}
		
		
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
	</script>
</head>
<body>
<?php
	
	if ( isset( $_POST['addannouncement'] ) ) {
		createGroupAnnouncement( $_POST['heading'], $_POST['body'], $_POST['date'], $currentGroup, $db );		
?>
		<script type="text/javascript">
			showMessage("Announcement successfully added");
		</script>
<?php
	}
	
	if ( isset( $_POST['editannouncement'] ) ) {
		$ann = new GroupAnnouncement( $_POST['id'], $db );
		$ann->setHeading( $_POST['heading'] );
		$ann->setBody( $_POST['body'] );
		$ann->updateDB();	
?>
		<script type="text/javascript">
			showMessage("Announcement successfully edited");
		</script>
<?php
	}
	
	if ( isset( $_POST['deleteannouncement'] ) ) {
		$ann = new GroupAnnouncement( $_POST['id'], $db );
		$ann->delete();
?>
		<script type="text/javascript">
			showMessage("Announcement successfully deleted");
		</script>
<?php
	}
?>

	<div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
	<div id="calendar">
<?php
		$events = $currentGroup->getWeekEvents();
		$eventArray = array();
		foreach ( $events as $event ) {
			$temp = explode( "/", $event->getDate() );
			$eventArray[ intval( $temp[1] ) ][]=$event;
		}
		$month = date( "n" );
		$day = date( "j" );
		$year = date( "Y" );
		print "<table width=100% border=1><tr valign='top'>";
		for ( $i=0; $i<7; $i++ ) {
			print "<td width='14%'>";
			print "<div class='prop'>&nbsp;</div>";
			print "<div class='dateHeading'>".date( "D n/d", mktime( 0,0,0,$month,$day+$i,$year ) )."</div>";
			$d = date( "j", mktime( 0,0,0,$month,$day+$i,$year ) );
			if ( isset( $eventArray[ $d ] ) )
				foreach ( $eventArray[ $d ] as $event ) {
					print "<a href='#' onMouseOver='showEvent(".$event->getID().", event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);' onMouseOut='hideEvent(".$event->getID().");'>".$event->getName()."</a><br>";
					print "<div class='event' id='".$event->getID()."'>".$event->getName()."<br>".$event->getDate()."<br>".$event->getDescHTML()."</div>";
				}
			print "</td>";
		}
		print "</tr></table>";
?>
	</div>
	<div id="container">
		<div id="recent">
			<div id="emails">
				<div id="box-top">
					<div id="box-topleft">
					</div>					
					<div id="box-topright">
					</div>
				</div>
				<div id="box-content">
					Last 5 Emails:
					<ul>
<?php
						$emails = $currentGroup->getRecentEmails();
						foreach ( $emails as $email ) {
							print "<li><a href='displayemail.php?id=".$email->getID()."'>".$email->getSubject()."</a></li>";
						}
						
?>
					</ul>
				</div>
				<div id="box-bottom">
					<div id="box-bottomleft">
					</div>					
					<div id="box-bottomright">
					</div>
				</div>
			</div>
			<div id="files">
				<div id="box-top">
					<div id="box-topleft">
					</div>					
					<div id="box-topright">
					</div>
				</div>
				<div id="box-content">
					Last 5 Files:
					<ul>
<?php
						$files = $currentGroup->getRecentFiles();
						foreach ( $files as $file ) {
							print "<li><a href='download.php?id=".$file->getID()."'>".$file->getName()."</a></li>";
						}
?>
					</ul>
				</div>
				<div id="box-bottom">
					<div id="box-bottomleft">
					</div>					
					<div id="box-bottomright">
					</div>
				</div>
			</div>
		</div>
		<div id="pictures">
<?php		
			$picture = $currentGroup->getRandomGroupPicture();
			if ( $picture ) {
				print "<table><tr>";
				print "<td><a href='grouppictures.php'><img width=375 src='".$picture->getRelativeName()."'></a></td></tr>";
				print "<tr><td><center><b>{$picture->getTitle()}</b></center></td></tr></table>";
			}
			else {
				print "Your group currently does not have any group pictures.";
				print "<br><a href='grouppictures.php'>Click here to add a picture.</a><br>";
			}
?>
		</div>
		<div id="announcements">
<?php
			$announcements = $currentGroup->getGroupAnnouncements();
			print "<b><u><i>Announcements:</i></u></b><br><br>";
			foreach ( $announcements as $announcement ) {
				print "<div class='announcement'>";
				if ( $currentUser->isGroupModerator( $currentGroup ) )
					print "<a href='#' onClick=\"editAnnouncement(".$announcement->getID().", '".$announcement->getHeadingJava()."', '".$announcement->getBodyJava()."');\">";
				print "<b>{$announcement->getHeading()}</b>";
				if ( $currentUser->isGroupModerator( $currentGroup ) )
					print "</a>";
				print "<br>".$announcement->getBody();
				print "</div><br>";
			}
			if ( count( $announcements ) == 0 )
				print "Your group currently does not have any announcements.<br>";
			if ( $currentUser->isGroupModerator( $currentGroup ) )
				print "<a href='#' onClick=\"document=getElementById('add-announcement').style.visibility='visible';\">Click here to add an announcement.</a>";
?>
		</div>
	</div>
	<div id="add-announcement" class="window">
		<div class="window-topbar">
			Add Announcement
			<input class="close-button" type="button" onClick="document.getElementById('add-announcement').style.visibility='hidden';">
		</div>
		<div class="window-content">
			<form action="grouphomepage.php" method="post">
				Expiration Date (MM/DD/YY):<input type="text" id="date" name="date" size=20><input type="button" onClick="showCalendar(event);" value="Select Date"><br>
				Heading: <input type="text" name="heading" size=60><br>
				Body:<br>
				<textarea name="body"cols=55 rows=8></textarea><br>
				<input type="submit" name="addannouncement" value="Add Announcement">
			</form>
		</div>
	</div>
	<div id="edit-announcement" class="window">
		<div class="window-topbar">
			Edit Announcement
			<input class="close-button" type="button" onClick="document.getElementById('edit-announcement').style.visibility='hidden';">
		</div>
		<div class="window-content">
			<form action="grouphomepage.php" method="post">
				<input type="hidden" name="id" id="editid">
				Heading: <input type="text" name="heading" id="editheading" size=60><br>
				Body:<br>
				<textarea name="body" id="editbody" cols=55 rows=8></textarea><br>
				<input type="submit" name="editannouncement" value="Edit Announcement">
				<input type="submit" name="deleteannouncement" value="Delete Announcement">
			</form>
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
