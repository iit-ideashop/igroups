<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/event.php" );
	
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Calendar</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">		
		li.calli {
			display:inline;
			padding:5px;
			border:solid 1px #000;
		}
		
		li.size3 {
			display:inline;
			padding:5px;
			border:solid 1px #000;
			font-size:8pt;
		}
		
		li.size2 {
			display:inline;
			padding:5px;
			border:solid 1px #000;
			font-size:11pt;
		}
		
		li.size1 {
			display:inline;
			padding:5px;
			border:solid 1px #000;
			font-size:14pt;
		}
		
		li.size0 {
			display:inline;
			padding:5px;
			border:solid 1px #000;
			font-size:20pt;
		}
		
		.prop {
			float:right;
			height:100px;
			width:1px;
			overflow:hidden;
		}
		
		#calendar {
			border:solid 1px #000;
			background-color:#fff;
			visibility:hidden;
			position:absolute;
			left:50px;
			z-index:500;
		}
		
		.eventlink {
			font-size:10px;
			display:block;
		}

		a.iproeventlink {
			font-size:10px;
			display:block;
			color:#000;
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
	<!--
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
		
		function editEvent( id, name, desc, date ) {
			document.getElementById('editid').value=id;
			document.getElementById('editname').value=name;
			document.getElementById('editdesc').value=desc;
			document.getElementById('editdate').value=date;
		}

		function viewEvent( name, desc, date ) {
			document.getElementById('viewname').innerHTML=name;
			var arr = desc.split(" "), desc2 = "";
			for(var i=0; i < arr.length; i++)
			{
				if(arr[i].substring(0, 7) == 'http://')
					desc2 += '<a href="' + arr[i] + '" target="_blank">' + arr[i] + '</a> ';
				else
					desc2 += arr[i] + ' ';
			}
			document.getElementById('viewdesc').innerHTML=desc2;
			document.getElementById('viewdate').innerHTML=date;
		}
		
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
	//-->
	</script>

<style type="text/css">

.ds_box {
	background-color: #FFF;
	border: 1px solid #000;
	position: absolute;
	z-index: 32767;
}

.ds_tbl {
	background-color: #FFF;
}

.ds_head {
	background-color: #C00;
	color: #FFF;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	font-weight: bold;
	text-align: center;
	letter-spacing: 2px;
}

.ds_subhead {
	background-color: #CCC;
	color: #000;
	font-size: 12px;
	font-weight: bold;
	text-align: center;
	font-family: Arial, Helvetica, sans-serif;
	width: 32px;
}

.ds_cell {
	background-color: #EEE;
	color: #000;
	font-size: 13px;
	text-align: center;
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	cursor: pointer;
}

.ds_cell:hover {
	background-color: #F3F3F3;
} /* This hover code won't work for IE */

</style>
</head>
<body>
<?php
require("sidebar.php");
?>
<div id="content">
<table class="ds_box" cellpadding="0" cellspacing="0" id="ds_conclass" style="display: none;">
<tr><td id="ds_calclass">
</td></tr>
</table>

<script type="text/javascript">
// <!-- <![CDATA[

// Project: Dynamic Date Selector (DtTvB) - 2006-03-16
// Script featured on JavaScript Kit- http://www.javascriptkit.com
// Code begin...
// Set the initial date.
var ds_i_date = new Date();
ds_c_month = ds_i_date.getMonth() + 1;
ds_c_year = ds_i_date.getFullYear();

// Get Element By Id
function ds_getel(id) {
	return document.getElementById(id);
}

// Get the left and the top of the element.
function ds_getleft(el) {
	var tmp = el.offsetLeft;
	el = el.offsetParent
	while(el) {
		tmp += el.offsetLeft;
		el = el.offsetParent;
	}
	return tmp;
}
function ds_gettop(el) {
	var tmp = el.offsetTop;
	el = el.offsetParent
	while(el) {
		tmp += el.offsetTop;
		el = el.offsetParent;
	}
	return tmp;
}

// Output Element
var ds_oe = ds_getel('ds_calclass');
// Container
var ds_ce = ds_getel('ds_conclass');

// Output Buffering
var ds_ob = ''; 
function ds_ob_clean() {
	ds_ob = '';
}
function ds_ob_flush() {
	ds_oe.innerHTML = ds_ob;
	ds_ob_clean();
}
function ds_echo(t) {
	ds_ob += t;
}

var ds_element; // Text Element...

var ds_monthnames = [
'January', 'February', 'March', 'April', 'May', 'June',
'July', 'August', 'September', 'October', 'November', 'December'
]; // You can translate it for your language.

var ds_daynames = [
'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'
]; // You can translate it for your language.

// Calendar template
function ds_template_main_above(t) {
	return '<table cellpadding="3" cellspacing="1" class="ds_tbl">'
	     + '<tr>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_py();">&lt;&lt;</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_pm();">&lt;</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_hi();" colspan="3">[Close]</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_nm();">&gt;</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_ny();">&gt;&gt;</td>'
		 + '</tr>'
	     + '<tr>'
		 + '<td colspan="7" class="ds_head">' + t + '</td>'
		 + '</tr>'
		 + '<tr>';
}

function ds_template_day_row(t) {
	return '<td class="ds_subhead">' + t + '</td>';
	// Define width in CSS, XHTML 1.0 Strict doesn't have width property for it.
}

function ds_template_new_week() {
	return '</tr><tr>';
}

function ds_template_blank_cell(colspan) {
	return '<td colspan="' + colspan + '"></td>'
}

function ds_template_day(d, m, y) {
	return '<td class="ds_cell" onclick="ds_onclick(' + d + ',' + m + ',' + y + ')">' + d + '</td>';
	// Define width the day row.
}

function ds_template_main_below() {
	return '</tr>'
	     + '</table>';
}

// This one draws calendar...
function ds_draw_calendar(m, y) {
	// First clean the output buffer.
	ds_ob_clean();
	// Here we go, do the header
	ds_echo (ds_template_main_above(ds_monthnames[m - 1] + ' ' + y));
	for (i = 0; i < 7; i ++) {
		ds_echo (ds_template_day_row(ds_daynames[i]));
	}
	// Make a date object.
	var ds_dc_date = new Date();
	ds_dc_date.setMonth(m - 1);
	ds_dc_date.setFullYear(y);
	ds_dc_date.setDate(1);
	if (m == 1 || m == 3 || m == 5 || m == 7 || m == 8 || m == 10 || m == 12) {
		days = 31;
	} else if (m == 4 || m == 6 || m == 9 || m == 11) {
		days = 30;
	} else {
		days = (y % 4 == 0) ? 29 : 28;
	}
	var first_day = ds_dc_date.getDay();
	var first_loop = 1;
	// Start the first week
	ds_echo (ds_template_new_week());
	// If sunday is not the first day of the month, make a blank cell...
	if (first_day != 0) {
		ds_echo (ds_template_blank_cell(first_day));
	}
	var j = first_day;
	for (i = 0; i < days; i ++) {
		// Today is sunday, make a new week.
		// If this sunday is the first day of the month,
		// we've made a new row for you already.
		if (j == 0 && !first_loop) {
			// New week!!
			ds_echo (ds_template_new_week());
		}
		// Make a row of that day!
		ds_echo (ds_template_day(i + 1, m, y));
		// This is not first loop anymore...
		first_loop = 0;
		// What is the next day?
		j ++;
		j %= 7;
	}
	// Do the footer
	ds_echo (ds_template_main_below());
	// And let's display..
	ds_ob_flush();
	// Scroll it into view.
	ds_ce.scrollIntoView();
}

// A function to show the calendar.
// When user click on the date, it will set the content of t.
function ds_sh(t) {
	// Set the element to set...
	ds_element = t;
	// Make a new date, and set the current month and year.
	var ds_sh_date = new Date();
	ds_c_month = ds_sh_date.getMonth() + 1;
	ds_c_year = ds_sh_date.getFullYear();
	// Draw the calendar
	ds_draw_calendar(ds_c_month, ds_c_year);
	// To change the position properly, we must show it first.
	ds_ce.style.display = '';
	// Move the calendar container!
	the_left = ds_getleft(t);
	the_top = ds_gettop(t) + t.offsetHeight;
	ds_ce.style.left = the_left + 'px';
	ds_ce.style.top = the_top + 'px';
	// Scroll it into view.
	ds_ce.scrollIntoView();
}

// Hide the calendar.
function ds_hi() {
	ds_ce.style.display = 'none';
}

// Moves to the next month...
function ds_nm() {
	// Increase the current month.
	ds_c_month ++;
	// We have passed December, let's go to the next year.
	// Increase the current year, and set the current month to January.
	if (ds_c_month > 12) {
		ds_c_month = 1; 
		ds_c_year++;
	}
	// Redraw the calendar.
	ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the previous month...
function ds_pm() {
	ds_c_month = ds_c_month - 1; // Can't use dash-dash here, it will make the page invalid.
	// We have passed January, let's go back to the previous year.
	// Decrease the current year, and set the current month to December.
	if (ds_c_month < 1) {
		ds_c_month = 12; 
		ds_c_year = ds_c_year - 1; // Can't use dash-dash here, it will make the page invalid.
	}
	// Redraw the calendar.
	ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the next year...
function ds_ny() {
	// Increase the current year.
	ds_c_year++;
	// Redraw the calendar.
	ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the previous year...
function ds_py() {
	// Decrease the current year.
	ds_c_year = ds_c_year - 1; // Can't use dash-dash here, it will make the page invalid.
	// Redraw the calendar.
	ds_draw_calendar(ds_c_month, ds_c_year);
}

// Format the date to output.
function ds_format_date(d, m, y) {
	// 2 digits month.
	m2 = '00' + m;
	m2 = m2.substr(m2.length - 2);
	// 2 digits day.
	d2 = '00' + d;
	d2 = d2.substr(d2.length - 2);
	// YYYY-MM-DD
	//return y + '-' + m2 + '-' + d2;
	// MM/DD/YYYY
	return m2 + '/' + d2 + '/' + y;
}

// When the user clicks the day.
function ds_onclick(d, m, y) {
	// Hide the calendar.
	ds_hi();
	// Set the value of it, if we can.
	if (typeof(ds_element.value) != 'undefined') {
		ds_element.value = ds_format_date(d, m, y);
	// Maybe we want to set the HTML in it.
	} else if (typeof(ds_element.innerHTML) != 'undefined') {
		ds_element.innerHTML = ds_format_date(d, m, y);
	// I don't know how should we display it, just alert it to user.
	} else {
		alert (ds_format_date(d, m, y));
	}
}

// And here is the end.

// ]]> -->
</script>
	<div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
<?php
	//------Start of Code for Form Processing-------------------------//

	if ( isset( $_POST['addevent'] ) ) {
		createEvent( $_POST['name'], $_POST['description'], $_POST['date'], $currentGroup, $db );
	}
	
	if ( isset( $_POST['editevent'] ) ) {
		$event = new Event( $_POST['id'], $db );
		$event->setName( $_POST['name'] );
		$event->setDesc( $_POST['description'] );
		$event->setDate( $_POST['date'] );
		$event->updateDB();
?>
		<script type="text/javascript">
			showMessage("Your event was successfully edited");
		</script>
<?php
	}
	
	if ( isset( $_POST['deleteevent'] ) ) {
		$event = new Event( $_POST['id'], $db );
		$event->delete();
?>
		<script type="text/javascript">
			showMessage("Your event was successfully deleted");
		</script>
<?php
	}
	
	if ( isset( $_GET['month'] ) && isset($_GET['year']) ) {
		
		if (is_numeric($_GET['month']) && is_numeric($_GET['year'])) {
			$currentMonth = $_GET['month'];
			$currentYear = $_GET['year'];
		}
	}
	else {
		$currentMonth = date( "n" );
		$currentYear = date( "Y" );
	}
	
	//------End Form Processing Code---------------------------------//
	
	$startDay = date( "w", mktime( 0, 0, 0, $currentMonth, 1, $currentYear ) );
	$endDay = date( "j", mktime( 0, 0, 0, $currentMonth+1, 0, $currentYear ) );
	
	$eventArray = array();
	
	$events = $currentGroup->getMonthEvents( $currentMonth, $currentYear );
	foreach ( $events as $event ) {
		$temp = explode( "/", $event->getDate() );
		$eventArray[ intval( $temp[1] ) ][]=$event;
	}

	$events = $currentGroup->getMonthIPROEvents( $currentMonth, $currentYear );
	foreach ( $events as $event ) {
		$temp = explode( "/", $event->getDate() );
		$eventArray[ intval( $temp[1] ) ][]=$event;
	}
	print "<table width=\"100%\" style=\"border-collapse: collapse\">" ;
	print "<tr><td id=\"columnbanner\" align=\"center\" colspan=\"7\" class=\"calbord\"><a href=\"calendar.php?month=".date( "n", mktime( 0, 0, 0, $currentMonth-1, 1, $currentYear ) )."&amp;year=".date( "Y", mktime( 0, 0, 0, $currentMonth-1, 1, $currentYear ) )."\">&laquo;</a> ".date( "F Y", mktime( 0, 0, 0, $currentMonth, 1, $currentYear ) )." <a href=\"calendar.php?month=".date( "n", mktime( 0, 0, 0, $currentMonth+1, 1, $currentYear ) )."&amp;year=".date( "Y", mktime( 0, 0, 0, $currentMonth+1, 1, $currentYear ) )."\">&raquo;</a></td></tr>";
	print "<tr><td width=\"14%\" class=\"calbord\">Sunday</td><td width=\"14%\" class=\"calbord\">Monday</td><td width=\"14%\" class=\"calbord\">Tuesday</td><td width=\"14%\" class=\"calbord\">Wednesday</td><td width=\"14%\" class=\"calbord\">Thursday</td><td width=\"14%\" class=\"calbord\">Friday</td><td width=\"14%\" class=\"calbord\">Saturday</td></tr>";
	if ( $startDay != 0 )
		print "<tr><td colspan=\"$startDay\" class=\"calbord\"></td>";
	
	$weekDay = $startDay;
	
	for ( $i=1; $i<=$endDay; $i++ ) {
		if ( $weekDay == 0 )
			print "<tr>";
		print "<td valign=\"top\" class=\"calbord\"><div class=\"prop\">&nbsp;</div>$i<br />";
		if ( isset( $eventArray[$i] ) )
		foreach ( $eventArray[$i] as $event ) {
			if ($event->isIPROEvent())
				$class = 'iproeventlink';
			else
				$class = 'eventlink';
			print "<a href=\"#\" class=\"$class\" onmouseover=\"showEvent('E".$event->getID()."',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('E".$event->getID()."');\"";
			if ( $currentUser->isGroupModerator( $event->getGroup() ) ) {
				print " onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'event-edit', 'Edit Event', 'width=450px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); editEvent( ".$event->getID().", '".$event->getNameJava()."', '".$event->getDescJava()."', '".$event->getDate()."')\"";
			}
			else {
				print " onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'event-view', 'View Event', 'width=350px,height=150px,left=300px,top=100px,resize=1,scrolling=1'); viewEvent('".$event->getNameJava()."', '".$event->getDescJava()."', '".$event->getDate()."');\"";
			}
			print ">".$event->getName()."</a><br />";
			print "<div class=\"event\" id=\"E".$event->getID()."\">".$event->getName()."<br />".$event->getDate()."<br />".$event->getDescHTML()."</div>";
		}
		print "</td>";
		$weekDay++;
		if ( $weekDay == 7 ) {
			print "</tr>";
			$weekDay = 0;
		}
	}
	
	if ( $weekDay != 0 ) 
		print "<td colspan=\"".(7-$weekDay)."\" class=\"calbord\"></td></tr>";
	
	print "</table>";
	
?>
<div id="addevent">
	<table>
	<tr>
	<td width="40%">
	<?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
	<h1>Add Event</h1>
	<form method="post" action="calendar.php">
		Date (MM/DD/YYYY): <input type="text" id="date" name="date" onclick="ds_sh(this);" style="cursor: text" /><br />
		Event name: <input type="text" name="name" /><br />
		Event description:<br />
		<textarea name="description" cols="50" rows="5"></textarea><br />
		<input type="submit" name="addevent" value="Add this Event" />
	</form>
	</td>
<?php
	if(count($currentGroup->getMonthEvents($currentMonth, $currentYear)) > 0) {
?>
	<td width="60%" valign="top">
	<h1>Edit Event</h1>
	<form method="post" action="calendar.php">
		<select name="id">
<?php		
			foreach ($currentGroup->getMonthEvents($currentMonth, $currentYear) as $event) {
				echo "<option value=\"{$event->getID()}\">{$event->getDate()} - {$event->getName()}</option>";
			}
		
?>
  		</select>
		<input type="submit" name="edit" value="Edit" />
		<input type="submit" name="deleteevent" value="Delete" />
		</form>
<?php
	if (isset($_POST['edit']) && isset($_POST['id'])) {
		$editevent = new Event( $_POST['id'], $db);
?>
	<form method="post" action="calendar.php">
		Date (MM/DD/YYYY): <input type="text" name="date" value="<?php echo "{$editevent->getDate()}"; ?>" onclick="ds_sh(this);" style="cursor: text" /><br />
		Event name: <input type="text" name="name" value="<?php echo "{$editevent->getName()}"; ?>" /><br />
		Event description: <br /><textarea name="description" cols="50" rows="5"><?php echo "{$editevent->getDesc()}"; ?></textarea><br />
		<input type="hidden" name="id" value="<?php echo "{$_POST['id']}"; ?>" />
		<input type="submit" name="editevent" value="Edit this Event" />
        </form>
<?php
}
?>
	</td>
<?php
}
}
?>
	</tr>
	</table>
</div>

<div class="window-content" id="event-edit" style="display: none">
		<form method="post" action="calendar.php">
			Date (MM/DD/YYYY): <input type="text" id="editdate" name="date" /><input type="button" onclick="calwin=dhtmlwindow.open('calbox', 'div', 'calendarmenu', 'Select date', 'width=600px,height=165px,left=300px,top=100px,resize=0,scrolling=0'); return false" value="Select Date" /><br />
			Event name: <input type="text" id="editname" name="name" /><br />
			Event description:<br />
			<textarea name="description" id="editdesc" cols="50" rows="5"></textarea><br />
			<input type="hidden" name="id" id="editid" />
			<input type="submit" name="editevent" value="Edit Event" />
			<input type="submit" name="deleteevent" value="Delete Event" />
		</form>
	</div>

<div class="window-content" id="event-view" style="display: none">
			<b>Date</b>: <span id="viewdate"></span><br />
			<b>Event name</b>: <span id="viewname"></span><br />
			<b>Event description</b>:<br /><span id="viewdesc"></span>
	</div>

<div id="calendarmenu" style="display: none">
	<table>
		<tr>
<?php
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
					print "<td><a href=\"#\" onclick=\"document.getElementById('editdate').value='".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."'; calwin.close();\">$j</a></td>";
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
<input id="calTarget" type="hidden" />
</div>
</body>
</html>
