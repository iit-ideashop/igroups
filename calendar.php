<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/event.php');
	include_once('classes/task.php');
	
	//------Start of Code for Form Processing-----------------------//

	if(isset($_POST['addevent']))
		createEvent($_POST['name'], $_POST['description'], $_POST['date'], $currentGroup, $db);
	
	if(isset($_POST['editevent']))
	{
		$event = new Event($_POST['id'], $db);
		$event->setName($_POST['name']);
		$event->setDesc($_POST['description']);
		$event->setDate($_POST['date']);
		$event->updateDB();
		$message = 'Your event was successfully edited';
	}
	
	if(isset($_POST['deleteevent']))
	{
		$event = new Event($_POST['id'], $db);
		$event->delete();
		$message = 'Your event was successfully deleted';
	}
	
	if(isset($_GET['month']) && isset($_GET['year']))
	{
		if(is_numeric($_GET['month']) && is_numeric($_GET['year']))
		{
			$currentMonth = $_GET['month'];
			$currentYear = $_GET['year'];
		}
	}
	else
	{
		$currentMonth = date('n');
		$currentYear = date('Y');
	}
	
	//------End Form Processing Code--------------------------------//
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/eventcal.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/eventcal.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Calendar</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript" src="DDS.js"></script>
<script type="text/javascript" src="Events.js"></script>
</head>
<body>
<?php
	/**** begin html head *****/
   require('htmlhead.php'); //starts main container
/****end html head content ****/
?>

<table class="ds_box" cellpadding="0" cellspacing="0" id="ds_conclass" style="display: none">
<tr><td id="ds_calclass">
</td></tr>
</table>

<?php
	

	$startDay = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
	$endDay = date('j', mktime(0, 0, 0, $currentMonth+1, 0, $currentYear));
	
	$eventArray = array();
	
	$events = $currentGroup->getMonthEvents($currentMonth, $currentYear);
	foreach($events as $event)
	{
		$temp = explode('/', $event->getDate());
		$eventArray[intval($temp[1])][] = $event;
	}

	$events = $currentGroup->getMonthIPROEvents($currentMonth, $currentYear);
	foreach($events as $event)
	{
		$temp = explode('/', $event->getDate());
		$eventArray[intval($temp[1])][] = $event;
	}
	
	$taskArray = array();
	$tasks = $currentGroup->getMonthTasks($currentMonth, $currentYear);
	foreach($tasks as $task)
	{
		$temp = explode('-', $task->getDue());
		$taskArray[intval($temp[2])][] = $task;
	}
	echo "<table id=\"calendartable\"> ";
	echo "<tr><td id=\"columnbanner\" align=\"center\" colspan=\"7\" class=\"calbord\"><a href=\"calendar.php?month=".date( "n", mktime( 0, 0, 0, $currentMonth-1, 1, $currentYear ) )."&amp;year=".date( "Y", mktime( 0, 0, 0, $currentMonth-1, 1, $currentYear ) )."\">&laquo;</a> ".date( "F Y", mktime( 0, 0, 0, $currentMonth, 1, $currentYear ) )." <a href=\"calendar.php?month=".date( "n", mktime( 0, 0, 0, $currentMonth+1, 1, $currentYear ) )."&amp;year=".date( "Y", mktime( 0, 0, 0, $currentMonth+1, 1, $currentYear ) )."\">&raquo;</a></td></tr>\n";
	echo "<tr><td class=\"calbord\">Sunday</td><td class=\"calbord\">Monday</td><td class=\"calbord\">Tuesday</td><td class=\"calbord\">Wednesday</td><td class=\"calbord\">Thursday</td><td class=\"calbord\">Friday</td><td class=\"calbord\">Saturday</td></tr>\n";
	if($startDay != 0)
		echo "<tr><td colspan=\"$startDay\" class=\"calbord\"></td>";
	
	$weekDay = $startDay;
	
	for($i = 1; $i <= $endDay; $i++)
	{
		if($weekDay == 0)
			echo '<tr>';
		echo "<td valign=\"top\" class=\"calbord\"><div class=\"prop\">&nbsp;</div>$i<br />\n";
		if(isset($eventArray[$i]))
		{
			foreach($eventArray[$i] as $event)
			{
				$class = ($event->isIPROEvent() ? 'iproeventlink' : 'eventlink');
				echo "<a href=\"#\" class=\"$class\" onmouseover=\"showEvent('E".$event->getID()."',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('E".$event->getID()."');\"";
				if($currentUser->isGroupModerator($event->getGroup()))
					echo " onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'event-edit', 'Edit Event', 'width=450px,height=200px,left=300px,top=100px,resize=0,scrolling=0'); editEvent( ".$event->getID().", '".htmlspecialchars($event->getName())."', '".htmlspecialchars($event->getDescAlmostJava())."', '".$event->getDate()."')\"";
				else
					echo " onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'event-view', 'View Event', 'width=350px,height=150px,left=300px,top=100px,resize=1,scrolling=1'); viewEvent('".htmlspecialchars($event->getName())."', '".htmlspecialchars($event->getDescAlmostJava())."', '".$event->getDate()."');\"";
				echo ">".$event->getName()."</a><br />\n";
				echo "<div class=\"event\" id=\"E".$event->getID()."\">".htmlspecialchars($event->getName())."<br />".$event->getDate()."<br />".htmlspecialchars($event->getDesc())."</div>\n";
			}
		}
		if(isset($taskArray[$i]))
		{
			echo 'Due:';
			foreach($taskArray[$i] as $task)
			{
				$class = 'tasklink';
				echo "<a href=\"#\" class=\"$class\" onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'task-view', 'View Task', 'width=350px,height=150px,left=300px,top=100px,resize=1,scrolling=1'); viewEvent('".htmlspecialchars($task->getName())."', '".str_replace(array("\n", "\r"), array('<br />', ''), htmlspecialchars($task->getCalDesc()))."', '".$task->getDue()."');\"";
				echo ">".$task->getName()."</a><br />";
			}
		}
		echo '</td>';
		$weekDay++;
		if($weekDay == 7)
		{
			echo '</tr>';
			$weekDay = 0;
		}
	}
	
	if($weekDay != 0)
		echo "<td colspan=\"".(7-$weekDay)."\" class=\"calbord\"></td></tr>\n";
	
	echo "</table>\n";
?>
<div id="addevent">
	<table>
	<tr>
	<td style="width:40%">
<?php
	if(!$currentUser->isGroupGuest($currentGroup))
	{
?>
		<h1>Add Event</h1>
		<form method="post" action="calendar.php"><fieldset>
			<label for="date1">Date (MM/DD/YYYY):</label><input type="text" id="date1" name="date" onclick="ds_sh(this);" style="cursor: text" /><br />
			<label for="eventname1">Event name:</label><input type="text" name="name" id="eventname1" /><br />
			<label for="eventdesc1">Event description:</label><br />
			<textarea name="description" cols="50" rows="5" id="eventdesc1"></textarea><br />
			<input type="submit" name="addevent" value="Add this Event" />
		</fieldset></form>
		</td>
<?php
		if(count($currentGroup->getMonthEvents($currentMonth, $currentYear)) > 0)
		{
?>
			<td style="width:60%" valign="top">
			<h1>Edit Event</h1>
			<form method="post" action="calendar.php"><fieldset>
			<select name="id">
<?php		
			foreach ($currentGroup->getMonthEvents($currentMonth, $currentYear) as $event)
				echo "<option value=\"{$event->getID()}\">{$event->getDate()} - {$event->getName()}</option>";
?>
	  		</select>
			<input type="submit" name="edit" value="Edit" />
			<input type="submit" name="deleteevent" value="Delete" /></fieldset>
			</form>
<?php
			if(isset($_POST['edit']) && isset($_POST['id']))
			{
				$editevent = new Event( $_POST['id'], $db);
?>
				<form method="post" action="calendar.php"><fieldset>
				<label for="date2">Date (MM/DD/YYYY):</label><input type="text" name="date" value="<?php echo "{$editevent->getDate()}"; ?>" onclick="ds_sh(this);" style="cursor: text" id="date2" /><br />
				<label for="eventname2">Event name:</label><input type="text" name="name" value="<?php echo "{$editevent->getName()}"; ?>" id="eventname2" /><br />
				<label for="eventdesc2">Event description:</label><br /><textarea name="description" cols="50" rows="5" id="eventdesc2"><?php echo "{$editevent->getDesc()}"; ?></textarea><br />
				<input type="hidden" name="id" value="<?php echo "{$_POST['id']}"; ?>" />
				<input type="submit" name="editevent" value="Edit this Event" />
				</fieldset></form>
<?php
			}
			echo "</td>\n";
		}
	}
?>
	</tr>
	</table>
	</div>
<?php
	if($currentUser->isGroupModerator($currentGroup))
	{
?>
		<div class="window-content" id="event-edit" style="display: none">
		<form method="post" action="calendar.php"><fieldset>
			<label for="editdate">Date (MM/DD/YYYY):</label><input type="text" id="editdate" name="date" /><input type="button" onclick="calwin=dhtmlwindow.open('calbox', 'div', 'calendarmenu', 'Select date', 'width=600px,height=165px,left=300px,top=100px,resize=0,scrolling=0'); return false" value="Select Date" /><br />
			<label for="editname">Event name:</label><input type="text" id="editname" name="name" /><br />
			<label for="editdesc">Event description:</label><br />
			<textarea name="description" id="editdesc" cols="50" rows="5"></textarea><br />
			<input type="hidden" name="id" id="editid" />
			<input type="submit" name="editevent" value="Edit Event" />
			<input type="submit" name="deleteevent" value="Delete Event" />
		</fieldset></form>
	</div>
<?php
	}
?>

<div class="window-content" id="event-view" style="display: none">
			<b>Date</b>: <span id="viewdate"></span><br />
			<b>Event name</b>: <span id="viewname"></span><br />
			<b>Event description</b>:<br /><span id="viewdesc"></span>
	</div>

<div class="window-content" id="task-view" style="display: none">
			<b>Due Date</b>: <span id="viewdate"></span><br />
			<b>Task name</b>: <span id="viewname"></span><br />
			<b>Task description</b>:<br /><span id="viewdesc"></span>
	</div>

<div id="calendarmenu" style="display: none"><table><tr>
<?php
	for($i = $currentMonth; $i < $currentMonth + 4; $i++)
	{
		echo "<td valign=\"top\">";
		echo "<table>";
		echo "<tr><td colspan=\"7\">".date('F Y', mktime(0, 0, 0, $i, 1, $currentYear))."</td></tr>\n";
		echo "<tr><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>\n";
		$startDay = date('w', mktime(0, 0, 0, $i, 1, $currentYear));
		$endDay = date('j', mktime(0, 0, 0, $i+1, 0, $currentYear));
		if($startDay)
			echo "<tr><td colspan=\"$startDay\"></td>";
		$weekDay = $startDay;
		for($j = 1; $j <= $endDay; $j++)
		{
			if($weekDay == 0)
				echo '<tr>';
			echo "<td><a href=\"#\" onclick=\"document.getElementById('editdate').value='".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."'; calwin.close();\">$j</a></td>";
			$weekDay++;
			if($weekDay == 7)
			{
				echo "</tr>\n";
				$weekDay = 0;
			}
		}
		if($weekDay != 0)
			echo "<td colspan=\"".(7-$weekDay)."\"></td></tr>\n";
		echo "</table></td>";
	}
?>
</tr></table></div>
<input id="calTarget" type="hidden" />
<?php
//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>	
</body></html>
