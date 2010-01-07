<?php
	include_once("globals.php");
	include_once("checklogin.php");
	include_once( "classes/timelog.php" );

	function getWeekID ($date, $db) {
		$query = $db->igroupsQuery("SELECT iID FROM Weeks where dStartDate <= \"$date\" and dEndDate >= \"$date\"");
		$result = mysql_fetch_row($query);
		return $result[0];
	}

	if (isset($_GET['week'])) {
		$currentWeek = $_GET['week'];
		$_SESSION['week'] = $_GET['week'];
	}
	else if (isset($_SESSION['week']))
		$currentWeek = $_SESSION['week'];
	else
		$currentWeek = 0;
		
	if (isset($_GET['taskWeek'])) {
		$currentTaskWeek = $_GET['taskWeek'];
		$_SESSION['taskWeek'] = $_GET['taskWeek'];
	}
	else if (isset($_SESSION['taskWeek']))
		$currentTaskWeek = $_SESSION['taskWeek'];
	else
		$currentTaskWeek = 0;
	
	if (isset($_GET['editTask'])) {
		$editTask = new TimeEntry($_GET['editTask'], $db);
		if ($editTask->getUserID() != $currentUser->getID())
			unset($editTask);
	}
	if (isset($_GET['editTime'])) {
		$editTime = new TimeEntry($_GET['editTime'], $db);
		if ($editTime->getUserID() != $currentUser->getID())
			unset($editTime);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<?php
require("appearance.php");
echo "<link rel=\"stylesheet\" href=\"skins/$skin/calendar.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/calendar.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Your Timesheet</title>
</head>
<body>
<?php
	$empty = 0;
	if ( isset( $_POST['addtime'] ) ) {
		if($_POST['description'] != "") {
			$newEntry = createTimeEntry( $currentUser->getID(), $currentGroup->getID(), $currentGroup->getSemester(), $_POST['date'], $_POST['hours'], $_POST['description'], $db );
			$message = "Timesheet entry successfully added";
		}
		else
			$empty = 1;
	}
	else if ( isset( $_POST['addProjTask'] ) ) {
		if($_POST['taskDescription'] != "") {
			$newEntry = createProjTask( $currentUser->getID(), $currentGroup->getID(), $currentGroup->getSemester(), $_POST['taskDate'], $_POST['taskHours'], $_POST['taskDescription'], $db );
			$message = "Projected task successfully added";
		}
		else
			$empty = 1;
	}
	else if (isset($_POST['edittime'])) {
		if($_POST['description'] != "") {
			$editEntry = new TimeEntry($_POST['entryID'], $db);
			$editEntry->delete();
			$newEntry = createTimeEntry( $currentUser->getID(), $currentGroup->getID(), $currentGroup->getSemester(), $_POST['date'], $_POST['hours'], $_POST['description'], $db );
			$message = "Timesheet entry successfully edited";
		}
		else
			$empty = 1;
	}
	else if (isset($_POST['editProjTask'])) {
		if($_POST['taskDescription'] != "") {
			$editEntry = new TimeEntry($_POST['entryID'], $db);
			$editEntry->delete();
			$newEntry = createProjTask( $currentUser->getID(), $currentGroup->getID(), $currentGroup->getSemester(), $_POST['taskDate'], $_POST['taskHours'], $_POST['taskDescription'], $db );
			$message = "Projected task successfully edited";
		}
		else
			$empty = 1;
	}
	else if (isset($_POST['deltime'])) {
		$editEntry = new TimeEntry($_POST['entryID'], $db);
		$editEntry->delete();
		$message = "Timesheet entry successfully deleted";
	}
	else if (isset($_POST['delProjTask'])) {
		$editEntry = new TimeEntry($_POST['entryID'], $db);
		$editEntry->delete();
		$message = "Projected task successfully deleted";
	}
	if($empty == 1)
		$message = "Error: Description cannot be blank";
	
?>
<?php
/**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/
?>
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
	<h3>About Legacy Timesheets</h3>
	<p>This is the <b>legacy timesheets</b> feature--it has been superseded by the Tasks feature, and is currently deprecated: This feature may be buggy or not work correctly. It is provided for the Fall 2009 semester for groups whose professors who prefer the old feature. <b style="text-transform: uppercase">Do not use the legacy timesheets unless your professor has instructed you to do so.</b> Instead, enter your time through the Tasks feature.</p>
	<p>To add a new timesheet entry or projected task, first enter the date in MM/DD/YYYY format or select it from a calendar by clicking 'Select Date'. Then, enter the number of hours worked (use .5 for half hours) and briefly describe the tasks performed. Add the new entry or task by clicking 'Add'. You can edit or delete any entry by clicking on it.</p>
	<p>To view a report of your recorded timesheets, select the week you wish to view from the drop-down menu and click 'View by Week'. To view a report of all your entries, select 'All Weeks' from the menu. All weeks are shown by default.</p>
	<hr />
<table width="85%" style="border-style: none">
<tr valign="top"><td>
	<form method="post" action="logtimespent.php"><fieldset>
<?php 
	if (!isset($editTime))
		print "<legend>Add New Timesheet Entry</legend>";
	else
		print "<legend>Edit Timesheet Entry</legend>";
?>
		<label for="date">Date (MM/DD/YYYY):</label><input type="text" id="date" name="date" onclick="ds_sh(this);" style="cursor: text" value='<?php if (isset($editTime)) print "{$editTime->getDate()}"; ?>' /><br />
		<label for="hours">Hours Spent:</label><input type="text" name="hours" id="hours" value='<?php if (isset($editTime)) print "{$editTime->getHoursSpent()}"; ?>' /><br />
		<label for="description">Tasks Worked On:</label><br />
		<textarea name="description" id="description" cols="50" rows="5"><?php if (isset($editTime)) print(str_replace("&lt;br /&gt;", "\r\n", htmlspecialchars($editTime->getTaskDescription()))); ?></textarea><br />
<?php
	if (!isset($editTime))
		print "<input type=\"submit\" name=\"addtime\" value=\"Add Entry\" />";
	else 
		print "<input type=\"hidden\" name=\"entryID\" value=\"{$editTime->getID()}\" /><input type=\"submit\" name=\"edittime\" value=\"Edit Entry\" />&nbsp;<input type=\"submit\" name=\"deltime\" value=\"Delete Entry\" />";
?>
	</fieldset></form>
</td><td>
	<form method="post" action="logtimespent.php"><fieldset>
<?php
	if (!isset($editTask))	
		print "<legend>Add New Projected Task</legend>";
	else
		print "<legend>Edit Projected Task</legend>";
?>
		<label for="taskDate">Date (MM/DD/YYYY):</label><input type="text" id="taskDate" name="taskDate" onclick="ds_sh(this);" style="cursor: text" value="<?php if (isset($editTask)) print "{$editTask->getDate()}"; ?>" /><br />
		<label for="taskHours">Estimated Hours:</label><input type="text" name="taskHours" id="taskHours" value="<?php if (isset($editTask)) print "{$editTask->getHoursSpent()}"; ?>" /><br />
		<label for="taskDescription">Tasks:</label><br />
		<textarea name="taskDescription" id="taskDescription" cols="50" rows="5"><?php if (isset($editTask)) print(str_replace("&lt;br /&gt;", "\r\n", htmlspecialchars($editTask->getTaskDescription()))); ?></textarea><br />
<?php
	if (!isset($editTask))
		print "<input type=\"submit\" name=\"addProjTask\" value=\"Add Task\" />";
	else
		print "<input type=\"hidden\" name=\"entryID\" value=\"{$editTask->getID()}\" /><input type=\"submit\" name=\"editProjTask\" value=\"Edit Task\" />&nbsp;<input type=\"submit\" name=\"delProjTask\" value=\"Delete Task\" />";
?>
	</fieldset></form>
</td></tr></table>
<br />
<table style="border-style: none" width="85%"><tbody>
<tr><td valign="top">
	<form method="get" action="logtimespent.php"><fieldset><legend>Your Current Timesheet Entries</legend>
	<select name="week">
<?php
	$timeLog = $currentGroup->getTimeLog();
	$weeks = $timeLog->getWeeks($currentUser->getID());
	foreach ($weeks as $week) {
		$temp1 = explode( "-", $week['dStartDate'] );
		$temp2 = explode( "-", $week['dEndDate'] );
		$startDate = date( "m/d/Y", mktime( 0, 0, 0, $temp1[1], $temp1[2], $temp1[0] ) );
		$endDate = date( "m/d/Y", mktime( 0, 0, 0, $temp2[1], $temp2[2], $temp2[0] ) );
		if ($week['iID'] == $currentWeek)
			print "<option value=\"{$week['iID']}\" selected=\"selected\">{$startDate} - {$endDate}</option>";
		else
			print "<option value=\"{$week['iID']}\">{$startDate} - {$endDate}</option>";
	}
	if ($currentWeek == 0)
		print "<option value=\"0\" selected=\"selected\">All Weeks</option>";
	else
		print "<option value=\"0\">All Weeks</option>";
?>
	</select>
	<input type="submit" name="submitWeek" value="View by Week" />
	</fieldset></form>
	<br />
	<table class="list">
		<thead>
		<tr><td>Date</td><td>Hours Spent</td><td>Task</td></tr>
		</thead><tfoot><tr><td colspan="3">Total Hours Spent:
<?php
	if ($currentWeek)
		echo "<b>{$timeLog->getHoursSpentByUserAndWeek($currentUser->getID(), $currentWeek)}</b>";
	else
		echo "<b>{$timeLog->getHoursSpentByUser($currentUser->getID())}</b>";
?>
</td></tr></tfoot><tbody>
<?php
		if ( $timeLog ) {
			if ($currentWeek)
				$entries = $timeLog->getEntriesByUserAndWeek($currentUser->getID(), $currentWeek);
			else
				$entries = $timeLog->getEntriesByUser( $currentUser->getID() );

			if ($entries == null)
				print "<tr><td colspan=\"3\">No Entries to display</td></tr>";
			foreach ( $entries as $entry ) {
				print "<tr><td valign=\"top\">".$entry->getDate()."</td><td valign=\"top\" align=\"center\">".$entry->getHoursSpent()."</td><td><a href=\"logtimespent.php?editTime={$entry->getID()}\">".str_replace("&lt;br /&gt;", "<br />", htmlspecialchars($entry->getTaskDescription()))."</a></td></tr>";
			}
		}
?>
	</tbody></table>
</td><td valign="top">
	<form method="get" action="logtimespent.php"><fieldset><legend>Your Projected Tasks</legend>
	<select name="taskWeek">
<?php
	$timeLog = $currentGroup->getTimeLog();
	$weeks = $timeLog->getTaskWeeks($currentUser->getID());
	foreach ($weeks as $week) {
		$temp1 = explode( "-", $week['dStartDate'] );
		$temp2 = explode( "-", $week['dEndDate'] );
		$startDate = date( "m/d/Y", mktime( 0, 0, 0, $temp1[1], $temp1[2], $temp1[0] ) );
		$endDate = date( "m/d/Y", mktime( 0, 0, 0, $temp2[1], $temp2[2], $temp2[0] ) );
		if ($week['iID'] == $currentTaskWeek)
			print "<option value=\"{$week['iID']}\" selected>{$startDate} - {$endDate}</option>";
		else
			print "<option value=\"{$week['iID']}\">{$startDate} - {$endDate}</option>";
	}
	if ($currentTaskWeek == 0)
		print "<option value=\"0\" selected=\"selected\">All Weeks</option>";
	else
		print "<option value=\"0\">All Weeks</option>";
?>
	</select>
	<input type="submit" name="submitWeek" value="View by Week" />
	</fieldset></form>
	<br />
	<table class="list">
		<thead>
		<tr><td>Date</td><td>Hours</td><td>Task</td></tr>
		</thead><tfoot><tr><td colspan="3">Total Hours:
<?php
	if ($currentWeek)
		echo "<b>{$timeLog->getTaskHoursSpentByUserAndWeek($currentUser->getID(), $currentWeek)}</b>";
	else
		echo "<b>{$timeLog->getTaskHoursSpentByUser($currentUser->getID())}</b>";
?>

</td></tr></tfoot><tbody>
<?php
		if ( $timeLog ) {
			if ($currentTaskWeek)
				$entries = $timeLog->getTasksByUserAndWeek($currentUser->getID(), $currentTaskWeek);
			else
				$entries = $timeLog->getTasksByUser( $currentUser->getID() );

			if ($entries == null)
				print "<tr><td colspan=\"3\">No Entries to display</td></tr>";
			foreach ( $entries as $entry ) {
				print "<tr><td valign=\"top\">".$entry->getDate()."</td><td valign=\"top\" align=\"center\">".$entry->getHoursSpent()."</td><td><a href=\"logtimespent.php?editTask={$entry->getID()}\">".str_replace("&lt;br /&gt;", "<br />", htmlspecialchars($entry->getTaskDescription()))."</a></td></tr>";
			}
		}
?>
	</tbody>
</table></td></tr></tbody></table>


<div id="calendar">
	<table>
		<tr>
<?php
			$currentMonth = date( "n" );
			$currentYear = date( "Y" );
			for ( $i=$currentMonth-3; $i<$currentMonth+1; $i++ ) {
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
					print "<td><a href=\"#\" onclick=\"selectDate('".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."');\">$j</a></td>";
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
<div id="taskCalendar">
	<table>
		<tr>
<?php
			$currentMonth = date( "n" );
			$currentYear = date( "Y" );
			for ( $i=$currentMonth-3; $i<$currentMonth+1; $i++ ) {
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
					print "<td><a href=\"#\" onclick=\"selectTaskDate('".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."');\">$j</a></td>";
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
<input id="calTarget" type="hidden" value="date" />
<input id="calTaskTarget" type="hidden" value="taskDate" />
<?php
  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>
</body>
</html>
