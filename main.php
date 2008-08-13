<?php
	session_start();
	
	include_once( "classes/db.php" );
	include_once( "classes/announcement.php" );
	include_once( "classes/person.php" );
	
	$db = new dbConnection();

	if ( isset( $_SESSION['userID'] ) )
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		$currentUser = false;
	
	if ( $currentUser && $currentUser->isAdministrator() ) {	
		if ( isset( $_POST['addannouncement'] ) ) {
			createAnnouncement( $_POST['heading'], $_POST['body'], $_POST['date'], $db );
			$message = "Announcement successfully added.";
		}	
			
		if ( isset( $_POST['editannouncement'] ) ) {
			$ann = new Announcement( $_POST['id'], $db );
			$ann->setHeading( $_POST['heading'] );
			$ann->setBody( $_POST['body'] );
			$ann->updateDB();
			$message = "Announcement successfully edited.";
		}
		
		if ( isset( $_POST['deleteannouncement'] ) ) {
			$ann = new Announcement( $_POST['id'], $db );
			$ann->delete();
			$message = "Announcement successfully deleted.";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Start Page</title>
	<style type="text/css">
		@import url("default.css");
		
		#container {
		}
		
		#top {
			margin-bottom:10px;
		}
		
		#top h1 {
			color:#000000;
			border-bottom-style:solid;
			border-bottom-color:#CC0000;
			font-size:20px;
			font-weight:normal;
		}
		
		#left {
			float:left;
			width:380px;
			text-align:center;
			margin-top:20px;
			padding: 1em;
		}
		
		#right {
			margin-left:400px;
			margin-top:20px;
			padding: 1em;
		} 
		
		#bottom {
			clear: both;
			margin-top:10px;
		}
		
		#bottom h1 {
			color:#000000;
			border-bottom-style:solid;
			border-bottom-color:#CC0000;
			font-size:20px;
			font-weight:normal;
		}
		
		#announcement-container {
			text-align:center;	
			background-color:#EEEEEE;
			background-image: url('img/menu-right-border.gif');
			background-repeat: repeat-y;	
			background-position: right;
		}
		
		#announcement-heading {
			color:#cc0000;
			font-weight:bold;
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

		*html #box-topleft {
			position: relative;
			margin-left: -3px;
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
			background-image: url('img/bottom-left.gif') ;
		}

		*html #box-bottomleft {
			position: absolute;
			margin-left: -3px;
		}
		
		#box-bottomright {
			height:33px;
			width:24px;
			float:right;
			background-image: url('img/bottom-right.gif');
		}
		
		#box-content {
			text-align:left;
			padding-right:20px;
			padding-left:5px;
		}
				
		#calendarmenu {
			border:solid 1px #000;
			background-color:#fff;
			visibility:hidden;
			position:absolute;
			left:50px;
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
	</style>
	<script language="JavaScript" type="text/JavaScript">
		function addAnnouncement() {
			document.getElementById('add-announcement').style.visibility='visible';
		}
	
		function showCalendar( event ) {
			document.getElementById('calendarmenu').style.top=(event.clientY+document.documentElement.scrollTop+25)+"px";
			document.getElementById('calendarmenu').style.visibility='visible';
		}
		
		function selectDate( date ) {
			document.getElementById('date').value=date;
			document.getElementById('calendarmenu').style.visibility='hidden';
		}
		
		function announcementObj() {
			document.write( '<div id="cycleFlag" style="position:absolute; visibility:hidden; overflow:hidden; top:0px; left:0; width:0; height:0">-1</div>' );
			
			this.setCycleFlag = function( value ) {
				if ( document.getElementById( 'cycleFlag' ) ) {
					document.getElementById( 'cycleFlag' ).innerHTML = value;
				}
			}
							
			this.getCycleFlag = function() {
				if ( document.getElementById( 'cycleFlag' ) ) {
					return document.getElementById( 'cycleFlag' ).innerHTML;
				}
				return;
			}
						
			this.doCycle = function() {
				delay = this.cycleSecond * 1000;
				if ( delay ) {
					this.rotation = setTimeout( "announcements.doCycle();", delay );
				}
				if ( this.getCycleFlag() > 0 ) {
					this.navigation( 'cycle' );
				}
				if ( this.getCycleFlag() == '-1' ) 
					this.setCycleFlag( 1 );
			}
			
			this.selected = 0;
			this.cycleSecond = 10;
			this.obj = function(){};
			
			this.add = function( id, heading, body, editheading, editbody ) {
				if( ! this.story )
					this.story = new Array();
					
				i = this.story.length;
		
				this.story[i] = new this.obj;
				
				this.story[i].id = id;
				this.story[i].heading = heading;
				this.story[i].body = body;
				this.story[i].editheading = editheading;
				this.story[i].editbody = editbody;
			}
			
			this.view = function( id, cycle ) {
				if( ( id < this.story.length ) && ( id >= 0 ) ) {
					this.selected = id;
					
					document.getElementById('announcement-heading').innerHTML = this.story[id].heading;
					document.getElementById('announcement-body').innerHTML = this.story[id].body;
					
					if( ! cycle ) {
						clearTimeout( this.rotation );
						this.cycleSecond = 0;
						this.setCycleFlag( 0 );
					}
				}
			}
		
			this.navigation = function( command ) {
				if ( command == 'prev' ) {
					change = ( this.selected == 0 ) ? this.story.length-1 : this.selected - 1 ;
					this.view( change );
				} else if ( command == 'pause' ) {
					this.cycleSecond = 0;
					this.setCycleFlag( 0 );
				} else if ( command == 'next' ) {
					change = ( this.selected == this.story.length-1  ) ? 0 : this.selected + 1 ;
					this.view( change );
				} else if ( command == 'cycle' ) {
					change = ( this.selected == this.story.length-1  ) ? 0 : this.selected + 1 ;
					this.view( change, true );
				} else {
					this.view( 0 );
				}
			}
			
			this.edit = function() {
				document.getElementById('editid').value = this.story[this.selected].id;
				document.getElementById('editheading').value = this.story[this.selected].editheading;
				document.getElementById('editbody').value = this.story[this.selected].editbody;
				document.getElementById('edit-announcement').style.visibility='visible';
			}
		}
		
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
		
		announcements = new announcementObj();
		announcements.doCycle();
		
<?php
		$announcementResults = $db->igroupsQuery( "SELECT iID FROM News ORDER BY iID DESC" );
		while ( $row = mysql_fetch_row( $announcementResults ) ) {
			$announcement = new Announcement( $row[0], $db );
			if ( $announcement && !$announcement->isExpired()) {
				print "announcements.add(".$announcement->getID().", '".$announcement->getHeadingHTML()."', '".$announcement->getBodyHTML()."', '".$announcement->getHeadingJava()."', '".$announcement->getBodyJava()."' );\n";
				if ( !isset( $firstAnnouncement ) )
					$firstAnnouncement = $announcement;
			}
		}
		if (!isset($firstAnnouncement))
			$firstAnnouncement = new Announcement($row[0], $db);
?>	
	</script>
</head>

<body>
<?php
	if ( isset( $message ) )
		print "<script type='text/javascript'>showMessage(\"$message\");</script>";
?>
	<div id="container">
		<div id="top">
			<h1>Welcome to iGROUPS!</h1>
			The iGROUPS system is designed to support all communication, scheduling and collaboration activities of IIT & IPRO teams. Through the use of iGROUPS you can send/receive e-mail messages, store/retrieve files, access/update a team calendar and view a complete history of a team's activities since the creation of the team in iGROUPS. Welcome to iGROUPS, an IIT student created IPRO team management tool.
		</div>
		<div id="left">
<?php
			$random=rand(1,264);
			print( "<img src='http://ipro.iit.edu/home/images/students1/photos/$random.jpg'>" );
			print( "<img src='http://ipro.iit.edu/home/images/students1/pull_quotes/$random.jpg'>" );
?>
		</div>
		<div id="right">
			<div id="announcement-container">
				<div id="box-top">
					<div id="box-topleft">
					</div>					
					<div id="box-topright">
					</div>
				</div>
				Announcements
				<div id="box-content">
					<div id="announcement-heading">
<?php
						print $firstAnnouncement->getHeadingHTML();
?>
					</div>
					<div id="announcement-body">				
<?php
						print $firstAnnouncement->getBodyHTML();
?>
					</div>
				</div>
				<a href="javascript:announcements.navigation('prev')">Prev</a> 
				<a href="javascript:announcements.navigation('pause')">Pause</a> 
				<a href="javascript:announcements.navigation('next')">Next</a>
<?php
				if ( $currentUser && $currentUser->isAdministrator() ) {
					print "<a href='javascript:addAnnouncement()'>Add</a> ";
					print "<a href='javascript:announcements.edit()'>Edit</a>";
				}
?>
			</div>
			<div id="box-bottom">
				<div id="box-bottomleft">
				</div>					
				<div id="box-bottomright">
				</div>
			</div>
		</div>
		<div id="bottom">
			<h1>To use iGROUPS</h1>
			To use iGROUPS simply enter your name and password in the login frame. Your initial name is the first part of your IIT e-mail address, or your entire e-mail address if you do not have or use an IIT e-mail. Your initial password is the first part of you email address[text appearing before the @]. Please change your password upon entry to iGROUPS.
		</div>
	</div>
	<div id="add-announcement" class="window">
		<div class="window-topbar">
			Add Announcement
			<input class="close-button" type="button" onClick="document.getElementById('add-announcement').style.visibility='hidden';">
		</div>
		<div class="window-content">
			<form action="main.php" method="post">
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
			<form action="main.php" method="post">
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
	</div><br>
<div style="font-size:85%; clear:both; color:#666; text-align:center;">Copyright &copy; 2007 Illinois Institute of Technology
Interprofessional Projects Program. All Rights Reserved.</div>
</body>
</html>
