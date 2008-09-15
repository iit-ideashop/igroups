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
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Home Page</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
		#recent {
			margin-top:10px;
			float:right;
			width:50%;
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
	</style>

<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>

	<script type="text/javascript">
	//<![CDATA[
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
		
		function editAnnouncement(id, heading, body) {
			document.getElementById('editid').value = id;
			document.getElementById('editheading').value = heading;
			document.getElementById('editbody').value = body;
		}
	//]]>
	</script>
</head>
<body>
<?php
	require("sidebar.php");
	if ( isset( $_POST['addannouncement'] ) ) {
		createGroupAnnouncement( $_POST['heading'], $_POST['body'], $_POST['date'], $currentGroup, $db );		
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement added.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
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
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement edited.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if ( isset( $_POST['deleteannouncement'] ) ) {
		$ann = new GroupAnnouncement( $_POST['id'], $db );
		$ann->delete();
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement deleted.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
?>

	<div id="content"><div id="topbanner">
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
		print "<table width=\"85%\" style=\"border-collapse: collapse\"><tr valign=\"top\">";
		for ( $i=0; $i<7; $i++ ) {
			print "<td width=\"14%\" class=\"calbord\">";
			print "<div class=\"prop\">&nbsp;</div>";
			print "<div class=\"dateHeading\">".date( "D n/d", mktime( 0,0,0,$month,$day+$i,$year ) )."</div>";
			$d = date( "j", mktime( 0,0,0,$month,$day+$i,$year ) );
			if ( isset( $eventArray[ $d ] ) )
				foreach ( $eventArray[ $d ] as $event ) {
					print "<a href=\"#\" onmouseover=\"showEvent(".$event->getID().", event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent(".$event->getID().");\">".$event->getName()."</a><br />";
					print "<div class=\"event\" id=\"".$event->getID()."\">".$event->getName()."<br />".$event->getDate()."<br />".$event->getDescHTML()."</div>";
				}
			print "</td>";
		}
		print "</tr></table>";
?>
	</div>
	<div id="container">
		<div id="recent">
				<div class="box">
					<span class="box-header">Last 5 Emails</span>
					<?php
					$emails = $currentGroup->getRecentEmails();
					if(count($emails) > 0) {
						print "<ul>";

						
						foreach ( $emails as $email ) {
							print "<li><a href=\"email.php?display=".$email->getID()."\">".$email->getSubject()."</a></li>";
						}
					print "</ul>";
					}
					else { print "<p>Your group does not have any emails.</p>"; }
					?>
				</div>
				<br /><br />
				<div class="box">
					<span class="box-header">Last 5 Files</span>
				<?php
				$files = $currentGroup->getRecentFiles();
				if(count($files) > 0) {
					print "<ul>";
					foreach ( $files as $file ) {
						print "<li><a href=\"download.php?id=".$file->getID()."\">".$file->getName()."</a></li>";
					}
					print "</ul>";
				}
				else { print "<p>Your group does not have any files.</p>"; }
				?>
				</div>
		</div>
		<div id="pictures">
<?php		
			$picture = $currentGroup->getRandomGroupPicture();
			if ( $picture ) {
				print "<table><tr>";
				print "<td><a href=\"grouppictures.php\"><img width=\"375\" src=\"http://igroups.iit.edu/".$picture->getRelativeName()."\" alt=\"".$picture->getRelativeName()."\" /></a></td></tr>";
				print "<tr><td><center><b>{$picture->getTitle()}</b></center></td></tr></table>";
			}
			else {
				print "Your group currently does not have any group pictures.";
				print "<br /><a href=\"grouppictures.php\">Click here to add a picture.</a><br />";
			}
?>
		</div>
		<div id="announcements">
<?php
			$announcements = $currentGroup->getGroupAnnouncements();
			print "<b><u><i>Announcements:</i></u></b><br /><br />";
			foreach ( $announcements as $announcement ) {
				print "<div class=\"announcement\">";
				if ( $currentUser->isGroupModerator( $currentGroup ) )
					print "<a href=\"#\" onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'edit-announcement', 'Edit Announcement', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1'); editAnnouncement(".$announcement->getID().", '".$announcement->getHeadingJava()."', '".$announcement->getBodyJava()."'); return false\">";
				print "<b>{$announcement->getHeading()}</b>";
				if ( $currentUser->isGroupModerator( $currentGroup ) )
					print "</a>";
				print "<br />".$announcement->getBody();
				print "</div><br />";
			}
			if ( count( $announcements ) == 0 )
				print "Your group currently does not have any announcements.<br />";
			if ( $currentUser->isGroupModerator( $currentGroup ) )
				print "<a href=\"#\" onclick=\"addwin=dhtmlwindow.open('addbox', 'div', 'add-announcement', 'Create Announcement', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Click here to add an announcement.</a>";
?>
		</div>
	</div>
		<div class="window-content" id="add-announcement" style="display: none">
			<form action="grouphomepage.php" method="post"><fieldset>
				<label for="date">Expiration Date (MM/DD/YY):</label><input type="text" id="date" name="date" size="20" /><input type="button" onclick="calwin=dhtmlwindow.open('calbox', 'div', 'calendarmenu', 'Select date', 'width=600px,height=165px,left=300px,top=100px,resize=0,scrolling=0'); return false" value="Select Date" /><br />
				<label for="heading">Heading:</label><input type="text" name="heading" id="heading" size="60" /><br />
				<label for="body">Body:</label><br />
				<textarea name="body" id="body" cols="55" rows="8"></textarea><br />
				<input type="submit" name="addannouncement" value="Add Announcement" />
			</fieldset></form>
		</div>
		<div class="window-content" id="edit-announcement" style="display: none">
			<form action="grouphomepage.php" method="post"><fieldset>
				<input type="hidden" name="id" id="editid" />
				<label for="editheading">Heading:</label><input type="text" name="heading" id="editheading" size="60" /><br />
				<label for="editbody">Body:</label><br />
				<textarea name="body" id="editbody" cols="55" rows="8"></textarea><br />
				<input type="submit" name="editannouncement" value="Edit Announcement" />
				<input type="submit" name="deleteannouncement" value="Delete Announcement" />
			</fieldset></form>
		</div>
	<div id="calendarmenu" style="display: none">
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
						print "<td><a href=\"#\" onclick=\"document.getElementById('date').value='".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."'; calwin.close();\">$j</a></td>";
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
	</div></div>
</body>
</html>
