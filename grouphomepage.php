<?php
	include_once('globals.php');
	include_once('checklogin.php');
	include_once('classes/groupannouncement.php');
	include_once('classes/event.php');
	include_once('classes/email.php');
	include_once('classes/file.php');
	include_once('classes/grouppicture.php');
	include_once('classes/task.php');
	
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/grouphomepage.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/grouphomepage.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - <?php echo $currentGroup->getName(); ?></title>
<script type="text/javascript" src="ChangeLocation.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript">
//<![CDATA[
	function showEvent(id, x, y)
	{
		document.getElementById(id).style.top=(y + 20)+"px";
		if(x > window.innerWidth / 2)
			document.getElementById(id).style.left=(x - 200)+"px";
		else
			document.getElementById(id).style.left=x+"px";
		document.getElementById(id).style.visibility='visible';
	}
	
	function hideEvent(id)
	{
		document.getElementById(id).style.visibility='hidden';
	}
	
	function editAnnouncement(id, heading, body)
	{
		document.getElementById('editid').value = id;
		document.getElementById('editheading').value = heading;
		document.getElementById('editbody').value = body;
	}
	
	function dehtml(desc)
	{
		desc.replace("&lt;a href", "<a onclick=\"window.open(this.href); return false;\" href");
		desc.replace("&lt;A HREF", "<a onclick=\"window.open(this.href); return false;\" href");
		desc.replace("<a href", "<a onclick=\"window.open(this.href); return false;\" href");
		desc.replace("<A HREF", "<a onclick=\"window.open(this.href); return false;\" href");
		desc.replace("&lt;/a", "</a");
		desc.replace("&gt;", ">");
		desc.replace("&amp;quot;", "\"");
		desc.replace("&quot;", "\"");
		return desc;
	}
	
	function viewEvent(name, desc, date)
	{
		document.getElementById('viewname').innerHTML = name;
		desc = dehtml(desc);
		document.getElementById('viewdesc').innerHTML = desc;
		document.getElementById('viewdate').innerHTML = date;
	}
	
	function viewTask(name, desc, date)
	{
		document.getElementById('taskname').innerHTML = name;
		desc = dehtml(desc);
		document.getElementById('taskdesc').innerHTML = desc;
		document.getElementById('taskdate').innerHTML = date;
	}
//]]>
</script>
</head>
<body>
<?php 
 /**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/	

	if(isset($_POST['addannouncement']))
	{
		if(createGroupAnnouncement($_POST['heading'], $_POST['body'], $_POST['date'], $currentGroup, $db))
		{
?>
			<script type="text/javascript">
				var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement added.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
			</script>
<?php
		}
		else
		{
		?>
			<script type="text/javascript">
				var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement was not added. Please ensure that you have filled out all fields, and try again.</p>', 'Error', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
			</script>
		<?php
		}
	}
	
	if(isset($_POST['editannouncement']))
	{
		$ann = new GroupAnnouncement($_POST['id'], $db);
		$ann->setHeading($_POST['heading']);
		$ann->setBody($_POST['body']);
		$ann->updateDB();	
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement edited.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if(isset( $_POST['deleteannouncement']))
	{
		$ann = new GroupAnnouncement($_POST['id'], $db);
		$ann->delete();
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Announcement deleted.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if(isset($_POST['scratch']) && !$currentUser->isGroupGuest($currentGroup))
	{
		$currentGroup->setScratch($_POST['scratchpad'], $currentUser->getID());
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Scratchpad updated.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	if($currentGroup->getScratchUpdater())
	{
		$by = new Person($currentGroup->getScratchUpdater(), $db);
		$scratchBlurb = "<p style=\"font-size: smaller\">Scratchpad last updated by {$by->getFullName()} at {$currentGroup->getScratchUpdated()}</p>\n";
	}
	else
		$scratchBlurb = '';
?>
	<div id="calendar">
<?php
		$events = $currentGroup->getWeekEvents();
		$eventArray = array();
		foreach($events as $event)
		{
			$temp = explode('/', $event->getDate());
			$eventArray[intval($temp[1])][] = $event;
		}
		
		$tasks = $currentGroup->getWeekTasks();
		$taskArray = array();
		foreach($tasks as $task)
		{
			$temp = explode('-', $task->getDue());
			$taskArray[intval($temp[2])][]=$task;
		}
		$month = date('n');
		$day = date('j');
		$year = date('Y');
		echo "<table width=\"85%\" style=\"border-collapse: collapse\"><tr valign=\"top\">\n";
		for($i = 0; $i < 7; $i++)
		{
			echo "<td style=\"width: 14%\" class=\"calbord\">";
			echo "<div class=\"prop\">&nbsp;</div>";
			echo "<div class=\"dateHeading\">".date( "D n/d", mktime( 0,0,0,$month,$day+$i,$year ) )."</div>";
			$d = date('j', mktime(0, 0, 0, $month, $day+$i, $year));
			if(isset($eventArray[$d]))
			{
				foreach($eventArray[$d] as $event)
				{
					echo "<a href=\"#\" onmouseover=\"showEvent(".$event->getID().", event.clientX+document.documentElement.scrollLeft, event.clientY+document.documentElement.scrollTop);\" onmouseout=\"hideEvent(".$event->getID().");\" onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'event-view', 'View Event', 'width=350px,height=150px,left=300px,top=100px,resize=1,scrolling=1'); viewEvent('".htmlspecialchars($event->getName())."', '".htmlspecialchars($event->getDescAlmostJava())."', '".$event->getDate()."');\">".$event->getName()."</a><br />\n";
					echo "<div class=\"event\" id=\"".$event->getID()."\">".$event->getName()."<br />".$event->getDate()."<br />".$event->getDescHTML()."</div>\n";
				}
			}
			if(isset($taskArray[$d]))
			{
				echo 'Due:';
				foreach($taskArray[$d] as $task)
					echo "<a href=\"#\" onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'task-view', 'View Task', 'width=350px,height=150px,left=300px,top=100px,resize=1,scrolling=1'); viewTask('".htmlspecialchars($task->getName())."', '".htmlspecialchars($task->getCalDesc())."', '".$task->getDue()."');\">".$task->getName()."</a><br />";
			}
			echo "</td>";
		}
		echo "</tr></table>";
?>
	</div>
	<div id="container">
<div id="announcements">
<?php
	$announcements = $currentGroup->getGroupAnnouncements();
	echo "<h1>Announcements:</h1>\n";
	foreach($announcements as $announcement)
	{
		echo "<div class=\"announcement\">\n";
		if($currentUser->isGroupModerator($currentGroup))
			echo "<a href=\"#\" onclick=\"editwin=dhtmlwindow.open('editbox', 'div', 'edit-announcement', 'Edit Announcement', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1'); editAnnouncement(".$announcement->getID().", '".$announcement->getHeadingJava()."', '".$announcement->getBodyJava()."'); return false\">";
		echo "<b>{$announcement->getHeading()}</b>";
		if($currentUser->isGroupModerator($currentGroup))
			echo "</a>";
		echo "<br />".$announcement->getBody();
		echo "</div><br />\n";
	}
	if(!count($announcements))
		echo "Your group currently does not have any announcements.<br />";
	if($currentUser->isGroupModerator($currentGroup))
		echo "<a href=\"#\" onclick=\"addwin=dhtmlwindow.open('addbox', 'div', 'add-announcement', 'Create Announcement', 'width=500px,height=300px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Click here to add an announcement.</a>";
?>
	</div>
	<div id="scratchpad">
	<h1>Group Scratchpad</h1>
	<div id="scratchpadtext">
	<p>
	<?php
	    echo htmlspecialchars($currentGroup->getScratch());
	?>
	</p>
	<?php 
		echo $scratchBlurb;
	?>
	<button id="scratchpadedit">Edit</button>
	</div>
	<form method="post" id="scratchpadform" action="grouphomepage.php"><fieldset>
<?php
	if(!$currentUser->isGroupGuest($currentGroup))
	{
		echo "<textarea rows=\"10\" cols=\"40\" name=\"scratchpad\">".htmlspecialchars($currentGroup->getScratch())."</textarea><br />\n";
		echo "<input type=\"submit\" name=\"scratch\" value=\"Update Scratchpad\" /> <input type=\"reset\" />\n";
	}
?>
	</fieldset></form>
	</div>
<div id="recent">
	<div class="box" id="email"><span class="box-header">Last 5 Emails</span>
<?php
	$emails = $currentGroup->getRecentEmails();
	if(count($emails) > 0)
	{
		echo "<ul>\n";
		foreach($emails as $email)
			echo "\t<li><a href=\"email.php?display=".$email->getID()."\">".htmlspecialchars($email->getSubject())."</a> <span class=\"timeago\">".$email->getShortDateTime()." ago</span></li>\n";
		echo "</ul>\n";
	}
	else
		echo "<p>Your group does not have any emails.</p>\n";
?>
	</div>
	<div class="box" id="files">
	<span class="box-header">Last 5 Files</span>
<?php
	$files = $currentGroup->getRecentFiles();
	if(count($files) > 0)
	{
		echo "<ul>\n";
		foreach($files as $file)
			echo "\t<li><a href=\"download.php?id=".$file->getID()."\">".htmlspecialchars($file->getName())."</a> <span class=\"timeago\">".$file->getShortDateTime()." ago</span></li>\n";
		echo "</ul>\n";
	}
	else
		echo "<p>Your group does not have any files.</p>\n";
?>
	</div>
<br class="clearboth" />
</div>  
	</div>
<?php
	if($currentUser->isGroupModerator($currentGroup))
	{
?>
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
<?php
	}
?>
	<div class="window-content" id="event-view" style="display: none">
	<b>Date</b>: <span id="viewdate"></span><br />
	<b>Event name</b>: <span id="viewname"></span><br />
	<b>Event description</b>:<br /><span id="viewdesc"></span>
	</div>
	
	<div class="window-content" id="task-view" style="display: none">
	<b>Due Date</b>: <span id="taskdate"></span><br />
	<b>Task name</b>: <span id="taskname"></span><br />
	<b>Task description</b>:<br /><span id="taskdesc"></span>
	</div>
	
	<div id="calendarmenu" style="display: none">
	<table>
	<tr>
<?php
	$currentMonth = date('n');
	$currentYear = date('Y');
	for($i = $currentMonth; $i < $currentMonth+4; $i++)
	{
		echo "<td valign=\"top\">";
		echo "<table>";
		echo "<tr><td colspan=\"7\">".date( "F Y", mktime( 0, 0, 0, $i, 1, $currentYear ) )."</td></tr>";
		echo "<tr><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>";
		$startDay = date( "w", mktime( 0, 0, 0, $i, 1, $currentYear ) );
		$endDay = date( "j", mktime( 0, 0, 0, $i+1, 0, $currentYear ) );
		if($startDay)
			echo "<tr><td colspan=\"$startDay\"></td>";
		$weekDay = $startDay;
		for($j = 1; $j <= $endDay; $j++)
		{
			if($weekDay == 0)
				echo '<tr>';
			echo "<td><a href=\"#\" onclick=\"document.getElementById('date').value='".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."'; calwin.close();\">$j</a></td>";
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
<?php
//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>	
</body>
<script type="text/javascript">
$(document).ready(function(){
	$("#scratchpadedit").click(function(){
		$("#scratchpadtext").slideUp("slow"); 
	});
});
</script></html>
