<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/group.php');
	include_once('../classes/event.php');
	include_once('../classes/semester.php');

	function groupSort($array)
	{
		$newArray = array();
		foreach($array as $group)
		{
			if($group)
				$newArray[$group->getName()] = $group;
		}
		ksort($newArray);
		return $newArray;
	}
	
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}

	if(isset($_GET['selectSemester']))
	{
		$_SESSION['selectedIPROSemester'] = $_GET['semester'];
		unset( $_SESSION['selectedIPROGroup'] );
	}
	
	if(isset( $_GET['selectAGroup'] ) && $_GET['selectAGroup'] != '')
		$_SESSION['selectedIPROGroup'] = $_GET['group'];
	
	if(!isset($_SESSION['selectedIPROSemester']))
	{
		$semester = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($semester);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}
	
	if(isset($_SESSION['selectedIPROSemester']) && $_SESSION['selectedIPROSemester'] != 0)
		$currentSemester = new Semester($_SESSION['selectedIPROSemester'], $db);
	else
		$currentSemester = 0;

	if(isset($_SESSION['selectedIPROGroup'] ) && $_SESSION['selectedIPROGroup'] != '')
	{
		if($currentSemester)
			$currentGroup = new Group($_SESSION['selectedIPROGroup'], 0, $currentSemester->getID(), $db);
		else
			$currentGroup = new Group($_SESSION['selectedIPROGroup'], 1, 0, $db);
	}
	else
		$currentGroup = false;
	
	if(isset($_POST['addevent']))
	{
		if(!isset($_POST['global']))
			createEvent($_POST['name'], $_POST['description'], $_POST['date'], $currentGroup, $db);
		else
			createIPROEvent($_POST['name'], $_POST['description'], $_POST['date'], $currentSemester, $db);
	}
	
	if(isset($_POST['editevent']))
	{
		$event = new Event($_POST['id'], $db);
		$event->setName($_POST['name'] );
		$event->setDesc($_POST['description']);
		$event->setDate($_POST['date']);
		$event->updateDB();
	}
	
	if(isset($_POST['deleteevent']))
	{
		$event = new Event($_POST['id'], $db);
		$event->delete();
	}
	
	if(isset($_GET['monthyear']))
	{
		$temp = explode('/', $_GET['monthyear']);
		$currentMonth = $temp[0];
		$currentYear = $temp[1];
	}
	else
	{
		$currentMonth = date('n');
		$currentYear = date('Y');
	}
	
	//-----Start XHTML Output---------------------------------------//
	
	require('../doctype.php');
	require("../iknow/appearance.php");
	
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/calendar.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/calendar.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - IPRO Event Management</title>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript" src="../DDS.js"></script>
<script type="text/javascript">
function setCalendarTarget(name)
{
	document.getElementById('calTarget').value=name;
}

function showCalendar(event)
{
	document.getElementById('calendar').style.top=(event.clientY+document.documentElement.scrollTop+25)+"px";
	document.getElementById('calendar').style.visibility='visible';
}

function selectDate(date)
{
	document.getElementById(document.getElementById('calTarget').value).value=date;
	document.getElementById('calendar').style.visibility='hidden';
}

function showEvent(id, x, y)
{
	document.getElementById(id).style.top=(y+20)+"px";
	if(x > window.innerWidth / 2)
		document.getElementById(id).style.left=(x-200)+"px";
	else
		document.getElementById(id).style.left=x+"px";
	document.getElementById(id).style.visibility='visible';
}

function hideEvent(id)
{
	document.getElementById(id).style.visibility='hidden';
}

function editEvent(id, name, desc, date)
{
	document.getElementById('editid').value=id;
	document.getElementById('editname').value=name;
	document.getElementById('editdesc').value=desc;
	document.getElementById('editdate').value=date;
}
</script>
</head><body>
<?php
	 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
?>

<table class="ds_box" cellpadding="0" cellspacing="0" id="ds_conclass" style="display: none;">
<tr><td id="ds_calclass">
</td></tr>
</table>
	<div id="topbanner">
<?php
	if($currentSemester)
		echo $currentSemester->getName();
	else
		echo 'All iGROUPS';
	
	if($currentGroup)
		echo " - ".$currentGroup->getName();
?>
	</div>
	<div id="semesterSelect">
		<form method="get" action="event.php"><fieldset>
			<select name="semester">
<?php
	$semesters = $db->query('SELECT iID FROM Semesters ORDER BY iID DESC');
	while($row = mysql_fetch_row($semesters))
	{
		$semester = new Semester( $row[0], $db );
		if($currentSemester && $semester->getID() == $currentSemester->getID())
			echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>\n";
		else
			echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>\n";

	}
	
	if(!$currentSemester)
		echo "<option value=\"0\" selected=\"selected\">All iGROUPS</option>\n";
	else
		echo "<option value=\"0\">All iGROUPS</option>\n";
?>
			</select>
			<input type="submit" name="selectSemester" value="Select Semester" />
		</fieldset></form>
	</div>
<?php
	if($currentSemester)
		$groups = $currentSemester->getGroups();
	else
	{
		$groupResults = $db->query('SELECT iID FROM Groups');
		$groups = array();
		while($row = mysql_fetch_row($groupResults))
			$groups[] = new Group($row[0], 1, 0, $db);
	}
?>
	<div id="groupSelect">
		<form method="get" action="event.php"><fieldset>
			<select name="group">
			<option value=''>Select a Group</option>
<?php
	$groups = groupSort($groups);
	foreach($groups as $group)
	{
		if($currentGroup && $currentGroup->getID() == $group->getID())
			echo "<option value=\"".$group->getID()."\" selected=\"selected\">".$group->getName()."</option>\n";
		else
			echo "<option value=\"".$group->getID()."\">".$group->getName()."</option>\n";
	}
?>
			</select>
			<input type="submit" name="selectAGroup" value="Select Group" />
		</fieldset></form>
	</div>
<?php
	if($currentGroup)
	{
		$startDay = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
		$endDay = date('j', mktime(0, 0, 0, $currentMonth+1, 0, $currentYear));
		
		$eventArray = array();
		
		$events = $currentGroup->getMonthEvents($currentMonth, $currentYear);
		foreach($events as $event)
		{
			$temp = explode('/', $event->getDate());
			$eventArray[intval($temp[1])][]=$event;
		}
	
		$events = $currentGroup->getMonthIPROEvents( $currentMonth, $currentYear );
		foreach($events as $event)
		{
			$temp = explode('/', $event->getDate());
			$eventArray[ intval( $temp[1] ) ][]=$event;
		}

		echo "<p>Calendar events can now accept links. Use standard HTML: '&lt;a href=\"$appurl\"&gt;Click here for $appname&lt;/a&gt;' as an example.</p>\n";
		echo "<table class="calendarTable" width=\"100%\" >" ;
		echo "<tr><td id=\"columnbanner\" align=\"center\" colspan=\"7\" class=\"calbord\"><a href=\"event.php?monthyear=".date( "n/Y", mktime( 0, 0, 0, $currentMonth-1, 1, $currentYear ) )."\">&laquo;</a> ".date( "F Y", mktime( 0, 0, 0, $currentMonth, 1, $currentYear ) )." <a href=\"event.php?monthyear=".date( "n/Y", mktime( 0, 0, 0, $currentMonth+1, 1, $currentYear ) )."\">&raquo;</a></td></tr>";
		echo "<tr><td class=\"calbord\">Sunday</td><td class=\"calbord\">Monday</td><td class=\"calbord\">Tuesday</td><td class=\"calbord\">Wednesday</td><td class=\"calbord\">Thursday</td><td class=\"calbord\">Friday</td><td class=\"calbord\">Saturday</td></tr>";
		if ( $startDay != 0 )
			echo "<tr><td colspan=\"$startDay\" class=\"calbord\"></td>";
		
		$weekDay = $startDay;
		
		for($i = 1; $i <= $endDay; $i++ )
		{
			if($weekDay == 0)
				echo '<tr>';
			echo "<td valign=\"top\" class=\"calbord\"><div class=\"prop\">&nbsp;</div>$i<br />";
			if(isset($eventArray[$i]))
			foreach($eventArray[$i] as $event)
			{
				echo "<a href=\"#\" class=\"eventlink\" onmouseover=\"showEvent('E".$event->getID()."',event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent('E".$event->getID()."');\"";
				echo " onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'event-edit', 'Edit Event', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1'); editEvent( ".$event->getID().", '".htmlspecialchars($event->getName())."', '".str_replace("\n", "<br />", htmlspecialchars($event->getDescJava()))."', '".$event->getDate()."')\"";
				echo ">".$event->getName()."</a><br />";
				echo "<div class=\"event\" id=\"E".$event->getID()."\">".htmlspecialchars($event->getName())."<br />".$event->getDate()."<br />".htmlspecialchars($event->getDesc())."</div>";
			}
			echo "</td>";
			$weekDay++;
			if($weekDay == 7)
			{
				echo "</tr>\n";
				$weekDay = 0;
			}
		}
		
		if($weekDay != 0) 
			echo "<td colspan=\"".(7-$weekDay)."\" class=\"calbord\"></td></tr>";
		
		echo "</table>\n";
?>
		<div id="addevent">
		<table width="85%">
		<tr>
		<td style="width:40%">
			<form method="post" action="event.php"><fieldset><legend>Add Event</legend>
				<label for="adddate">Date (MM/DD/YYYY):</label><input type="text" id="adddate" name="date" onclick="ds_sh(this);" style="cursor: text" /><br />
				<label for="addname">Event name:</label><input type="text" id="addname" name="name" /><br />
				<input type="checkbox" name="global" id="global" /> <label for="global">Global event? (If checked, will be added to all IPRO teams.)</label> <br />
				<label for="adddesc">Event description:</label><br />
				<textarea name="description" id="adddesc" cols="50" rows="5"></textarea><br />
				<input type="submit" name="addevent" value="Add Event" />
			</fieldset></form>
		</td>
		<td style="width:60%" valign="top">
<?php
		if(count($currentGroup->getMonthIPROEvents($currentMonth, $currentYear)) > 0)
		{
			echo "<form method=\"post\" action=\"event.php\"><fieldset><legend>Edit Event</legend>";
			echo "<select name=\"id\">";
			foreach($currentGroup->getMonthIPROEvents($currentMonth, $currentYear) as $event)
				echo "<option value='{$event->getID()}'>{$event->getDate()} - {$event->getName()}</option>";
			echo "</select>";

			echo "<input type=\"submit\" name=\"edit\" value=\"Edit\" />";
			echo "<input type=\"submit\" name=\"deleteevent\" value=\"Delete\" /></fieldset></form>";
		}
		if(isset($_POST['edit']) && isset($_POST['id']))
		{
			$editevent = new Event( $_POST['id'], $db);
?>
			<form method="post" action="event.php"><fieldset>
			<label for="editdate2">Date (MM/DD/YYYY):</label><input type="text" name="date" id="editdate2" value="<?php echo "{$editevent->getDate()}"; ?>" onclick="ds_sh(this);" style="cursor: text" /><br />
			<label for="editname2">Event name:</label><input type="text" name="name" id="editname2" value="<?php echo "{$editevent->getName()}"; ?>" /><br />
			<label for="editdesc2">Event description:</label><br /><textarea id="editdesc2" name="description" cols="50" rows="5"><?php echo "{$editevent->getDesc()}"; ?></textarea><br />
			<input type="hidden" name="id" value="<?php echo "{$_POST['id']}"; ?>" />
			<input type="submit" name="editevent" value="Edit this Event" />
			</fieldset></form>
<?php
		}
?>
		</td>
		</tr>
		</table>
		</div>
		<div id="calendar">
			<table>
				<tr valign="top">
<?php
		for($i = $currentMonth; $i<$currentMonth+4; $i++ )
		{
			echo "<td>";
			echo "<table>";
			echo "<tr><td colspan=\"7\">".date('F Y', mktime(0, 0, 0, $i, 1, $currentYear))."</td></tr>\n";
			echo "<tr><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>\n";
			$startDay = date('w', mktime(0, 0, 0, $i, 1, $currentYear));
			$endDay = date('j', mktime(0, 0, 0, $i+1, 0, $currentYear));
			if($startDay != 0)
				echo "<tr><td colspan=\"$startDay\"></td>";
			$weekDay = $startDay;
			for($j = 1; $j<=$endDay; $j++)
			{
				if($weekDay == 0)
					echo "<tr>";
				echo "<td><a href=\"#\" onclick=\"selectDate('".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."');\">$j</a></td>";
				$weekDay++;
				if($weekDay == 7)
				{
					echo "</tr>\n";
					$weekDay = 0;
				}
			}
			if($weekDay != 0)
				echo "<td colspan=\"".(7-$weekDay)."\"></td></tr>";
			echo "</table>";
			echo "</td>";
		}
?>
				</tr>
			</table>
		</div>
			<div class="window-content" id="event-edit" style="display: none">
				<form method="post" action="event.php"><fieldset>
					<label for="editdate">Date (MM/DD/YYYY):</label><input type="text" id="editdate" name="date" onclick="ds_sh(this);" style="cursor: text" /><br />
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
<input id="calTarget" type="hidden" />

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>
