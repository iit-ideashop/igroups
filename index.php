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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups 2.2</title>
<link rel="stylesheet" href="default.css" type="text/css" />
<style type="text/css">		
	#container {
		height: 100%;
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
	
	.right {
		margin-left:400px;
		margin-top:20px;
		padding: 1em;
	} 
	
	#bottom {
		margin-top:10px;
	}
	
	#bottom h1 {
		color:#000000;
		border-bottom-style:solid;
		border-bottom-color:#CC0000;
		font-size:20px;
		font-weight:normal;
		margin-left: 2em;
	}
	
	.announcement-heading {
		color:#cc0000;
		font-weight:bold;
	}
			
	#calendarmenu {
		border:solid 1px #000;
		background-color:#fff;
		visibility:hidden;
		position:absolute;
		left:50px;
	}
</style>
<?php
if ( $currentUser && $currentUser->isAdministrator() )
{
?>
<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<?php
}
?>
<script type="text/javascript">
<!--
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
					
					document.getElementById('announcehead').innerHTML = this.story[id].heading;
					document.getElementById('announcebody').innerHTML = this.story[id].body;
					
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
	if ( isset( $message ) )
		print "showMessage(\"$message\");";
?>
//-->
</script></head><body>
<?php
	require("sidebar.php");
	print "<div id=\"content\">";
	if ( $currentUser && $currentUser->isAdministrator() )
	{
?>
	<div id="add-announcement" class="window-content" style="display: none">
		<form action="index.php" method="post"><fieldset>
			<label for="date">Expiration Date (MM/DD/YY):</label><input type="text" id="date" name="date" size="20" /><input type="button" onclick="calwin=dhtmlwindow.open('calbox', 'div', 'calendarmenu', 'Select date', 'width=600px,height=165px,left=300px,top=100px,resize=0,scrolling=0'); return false" value="Select Date" /><br />
			<label for="heading">Heading:</label><input type="text" name="heading" id="heading" size="60" /><br />
			<label for="body">Body:</label><br />
			<textarea name="body" id="body" cols="55" rows="8"></textarea><br />
			<input type="submit" name="addannouncement" value="Add Announcement" />
		</fieldset></form>
	</div>
	<div id="edit-announcement" class="window-content" style="display: none">
		<form action="index.php" method="post"><fieldset>
			<input type="hidden" name="id" id="editid" />
			<label for="editheading">Heading:</label><input type="text" name="heading" id="editheading" size="60" /><br />
			<label for="editbody">Body:</label><br />
			<textarea name="body" id="editbody" cols="55" rows="8"></textarea><br />
			<input type="submit" name="editannouncement" value="Edit Announcement" />
			<input type="submit" name="deleteannouncement" value="Delete Announcement" />
		</fieldset></form>
	</div>
<?php
	}
?>
	<div id="container">
		<div id="top">
			<h1>Welcome to iGROUPS!</h1>
			<p>The iGROUPS system is designed to support all communication, scheduling, and collaboration activities of IPRO teams. Through the use of iGROUPS you can send/receive e-mail messages, store/retrieve files, access/update a team calendar, and view a complete history of a team's activities since the creation of the team in iGROUPS. Welcome to iGROUPS, a team management tool developed by IIT students.</p>
<?php
	if(!$currentUser)
		print "<p>To use iGROUPS simply enter your username and password in the login pane to the left. Your initial username is the first part of your IIT email address, or your entire email address if you do not have or use an IIT email. Your initial password is the first part of your email address (text appearing before the @). If you are a first-time user, please change your password upon entry to iGROUPS (from the My Profile page).</p>";
?>
		</div>
		<div id="left">
<?php
			$random=rand(1,264);
			print( "<img src=\"http://ipro.iit.edu/home/images/students1/photos/$random.jpg\" alt=\"Random photo\" />" );
			print( "<img src=\"http://ipro.iit.edu/home/images/students1/pull_quotes/$random.jpg\" alt=\"Random quote\" />" );
?>
		</div>
		<div class="right">
			<div class="box">
				<span class="box-header">Announcements</span>
					<div class="announcement-heading" id="announcehead">
<?php
						print $firstAnnouncement->getHeadingHTML();
?>
					</div>
					<div class="announcement-body" id="announcebody">				
<?php
						print $firstAnnouncement->getBodyHTML();
?>
					</div>
				<a href="javascript:announcements.navigation('prev')">Prev</a> 
				<a href="javascript:announcements.navigation('pause')">Pause</a> 
				<a href="javascript:announcements.navigation('next')">Next</a>
<?php
				if ( $currentUser && $currentUser->isAdministrator() ) {
					print "<a href=\"#\" onclick=\"addwin=dhtmlwindow.open('addbox', 'div', 'add-announcement', 'Add Announcement', 'width=600px,height=250px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Add</a> ";
					print "<a href=\"#\" onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'edit-announcement', 'Edit Announcement', 'width=600px,height=250px,left=300px,top=100px,resize=0,scrolling=0'); announcements.edit(); return false\">Edit</a>";
				}
?>
			</div>
		</div>
		
                <div class="right">
                        <div class="box">
                                <span class="box-header">iGROUPS Knowledge Management</span>
                                        <div class="announcement-heading">
                                                Guest to iGROUPS?
                                        </div>
                                        <div class="announcement-body">
                                                You can <a href="iknow/main.php">search and browse</a> IPRO team deliverables.
                                        </div>
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
	</div><br />
<p style="font-size:85%; clear:both; color:#666; text-align:center;">Copyright &copy; 2008 Illinois Institute of Technology Interprofessional Projects Program. All Rights Reserved.</p>
</div></body></html>
