<?php 
	# This is the php code/file that will create and use the TODO list

	# start a new session
	session_start();

	# require some classes 
	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/todo.php" );
	include_once( "classes/todolist.php" );
	
	$db = new dbConnection();
	
	# check for logged in user
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
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
<title>iGroups - Group Todo List</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">
                #calendar {
                        border:solid 1px #000;
                        background-color:#fff;
                        position:absolute;
			visibility:hidden;
                        left:50px;
                        z-index:500;
                }


		table.todoList {
			margin: 0px;
			padding: 0px;
			border: 1px solid black;
			background: #FFFFFF;
		}

		table.todoList tr.todoHeaders {
			color: #000;
			text-align: center;
			font-weight: bold;
			font-size: 16px;
			background: #EEE;
		}

		table.todoList td.todoTop {
			border-bottom: 1px solid #cc0000;
		}

		a.todoSort {
			color: #000;
		}

		table.todoList td.taskNum {
			width: 60px;
		}
		
		table.todoList td.taskDesc {
			text-align: right;
			padding-right: 5px;
			width: 400px;
		}

		table.todoList td.taskDate {
			text-align: center;
			width: 80px;
		}

		table.todoList td.taskDone {
			text-align: center;
			width: 50px;
		}

		table.todoList td.taskDel {
			text-align: center;
			width: 70px;
		}

		table.todoList tr.taskColor {
			background: #EEE;
		}

		div#task_table_footer {
			font-size: 14px;
		}

		div#task_table_footer span.text{
			position: relative;
			top: -3px;
		}

        div.taskEdit {
            position: relative;
            margin-left: 5px;
            padding: 5px;
            background: #EEE;
            border: 1px dotted #444444;
            width: 600px;
        }

        div.taskEdit div.description {
            background: #ddd;
            border-bottom: 2px solid #999;
            border-top: 2px solid #BBB;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        
        span.taskNumDisplay{
            position: relative;
            float: left;
            margin-top: 5px;
            left: 8px;
            top: -5px;
            color: green;
            font-size: 24px;
            z-index: 5;
        }

        #editButton {
            border: 0px;
            width: 30px;
            background: white;
            text-decoration: underline;
            color: #cc0207;
            font-family: Arial, Helvetica, sans-serif;
            margin-left: -3px;
        }

        div.newItem{
            margin-bottom: 5px;
        }

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
	function mysubmit(id){
		obj = document.getElementById('taskFoo');
		obj.innerHTML = '<input type="hidden" name="taskNum[]" value="-'+id+'" />';
		document.myform.submit();
	}

	function editChk(){
		var obj = document.myform.getElementsByTagName('input');
		for (i = 0; i < obj.length; i++){
			var regex = new RegExp('taskEdit', "i");
			if (regex.test(obj[i].getAttribute('name')))
				obj[i].checked = true;
		}
	}

	function editUnChk(){
		var obj = document.myform.getElementsByTagName('input');
		for (i = 0; i < obj.length; i++){
			var regex = new RegExp('taskEdit', "i");
			if (regex.test(obj[i].getAttribute('name')))
				obj[i].checked = false;
		}
	}
	//-->
	</script>
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
<h3>About Todo List</h3>
<p>To add a new task, fill in the task description and complete-by date in the 'Add a new task' box and click 'Add'. To edit a task, click the checkbox associated with that task and click 'Edit'. To mark a task as complete, click the 'Done' checkbox. To delete a task, click the red X under 'Delete'.</p>
<hr /><br />
<form action="" method="post" id="myform"><fieldset>
<?php

	$bar = new TodoList($currentGroup->getID(),$currentGroup->getSemester(),$db);


	/* Start of error checking. Here we will test for any bad input
	** and if there is set error flags and do NOT populate DB 
	** This will be done only for update and Add.
	*/
	$ERROR_task = 0;
	$ERROR_date = 0;
	$ERROR_person = 0;

	if(isset($_POST['add']) || isset($_POST['update']) || isset($_POST['doe']) && !(isset($_POST['cancel']))){
		if($_POST['task'] == " " || $_POST['task'] == "")
			$ERROR_task = 1;

        if($_POST['assigned'] != -1){
            if(!$currentGroup->isGroupMember(new Person($_POST['assigned'],$db)))
                $ERROR_person = 1;
        }
			
		// we need to check for correct date format and 'legal' date (not before today for example)
		if(isset($_POST['date']) and $_POST['date'] != ""){
            $_POST['date'] = $bar->fixdate($_POST['date']);
        }
	}

    if(isset($_POST['taskEdit']) && isset($_POST['e']) && $_POST['e']=='Edit' && !(isset($_POST['cancel']))){
        $count = 0;
        print("<div id=\"editInformation\">To edit any information simple change the corresponding items. To not change anything ... don't change anything.</div>");
        foreach($_POST['taskEdit'] as $task){
            $tmp = $bar->getTask($task);
            print("<span class=\"taskNumDisplay\">#".$tmp->getTaskNum()."</span>\n<br />");
            print("<div class=\"taskEdit\">");
            print("<div class=\"description\">Task Description: <input style=\"width: 454px;\" type=\"text\" value=\"".$tmp->getTask()."\" name=\"taskEditDesc[]\" /></div>");
            print("<div class=\"description\">Who is assigned to the task: ");
            print("<select class=\"assign\" name=\"assigned".$count."\">");
            print("<option value=\"-1\">No one</option>");
            foreach($currentGroup->getGroupMembers() as $peop){
                print("<option value=\"".$peop->getID()."\"");
                if ($peop->getID() == $tmp->getAssignedID())
                    print(" selected");
                print(">".$peop->getLastName().",&nbsp;".$peop->getFirstName()."</option>");
            }
            print("</select>");
            print("</div>");
            print("<div class=\"description\">Complete By (mm/dd/yyyy): <input type=\"text\" name=\"editdate[]\" style=\"border: 1px solid black; text-align: right; width: 80px; cursor: text\" onclick=\"ds_sh(this);\" value=\"".$tmp->getDueDate()."\" /><br/>");
            print("</div>");
            print("<input type=\"hidden\" name=\"taskEditID[]\" value=\"".$tmp->getID()."\" />");
            print("<input type=\"hidden\" name=\"taskEditTID[]\" value=\"".$tmp->getTaskNum()."\" />");
            print("</div>");
            $count++;
        }
        print("<input type=\"submit\" name=\"doe\" value=\"Save\" />");
        print("<input type=\"submit\" name=\"cancel\" value=\"Cancel\" />");
    }

    if(isset($_POST['doe']) && $_POST['doe']=='Save' && !(isset($_POST['cancel']))){
        $count = 0;
        foreach($_POST['taskEditDesc'] as $desc){
            list($key,$id) = each($_POST['taskEditID']);
            list($key,$tid) = each($_POST['taskEditTID']);
            list($key,$editDate) = each($_POST['editdate']);
            $worker = $_POST['assigned'.$count];
            $editDate = $bar->fixdate($editDate);
            $bar->updateTask($id,$tid,$desc,$worker,$editDate);
            $count++;
        }
    }
	
	# add a new task
	if (isset($_POST['add']) && !$ERROR_task && !$ERROR_date && !$ERROR_person && !(isset($_POST['cancel'])))
		$bar->addTask($_POST['task'],$_POST['date'],$_POST['assigned']);
	
	# delete a task
	if(isset($_GET['di']) and $_GET['di'] != "" && isset($_GET['dt']) && $_GET['dt'] != "" && isset($_GET['d']) and $_GET['d'] == 't'){
        if($currentGroup->isGroupMember($currentUser) || $currentUser->isAdministrator()){
            $bar->deleteTask($_GET['di'], $_GET['dt']);
        }
	}

	# if the person doesn't have javascript .. they probably clicked the UPDATE button to update the complete status
	# or they are just updating the text ...
	if(isset($_POST['update'])){
	}
	
	# check to see if the given ID is in the 'taskNum' variable sent to this page
	# side note: how this actually works correctly with javascript off ... I have no idea
	# but it works so I aint touching it
	function checkTaskNum($the_id){
		if(isset($_POST['c']) && $_POST['c'] == 't'){
			if(isset($_POST['taskNum'])){
				foreach($_POST['taskNum'] as $taskit){
					# if it is in here
					if($taskit == $the_id ){
						return true;
					}
					# but if it's a delete, return flase
					# trust me ... this one here works
					else if ((-$taskit) == $the_id)
						return false;
				}
				# it wasn't either for some reason so it failed
				return false;
			} 
			# taskNum isn't set to we fail and exit happily
			else
				return false;
		}
		else
			return false;
	}

		//print("todo: ".$bar->getLength()."<br />");
	if($bar->getLength() > 0){
		$highestItem = $bar->getTaskNum();

		//figure out what to sort by
		if (isset($_GET['sort'])) {
			if ($_GET['sort'] == 'num') {
				$sort = 'iTaskNum';
				if (isset($_SESSION['sort']) && $_SESSION['sort'] == 'iTaskNum') {
					$sort = $sort . ' DESC';
					unset($_SESSION['sort']);
				}
				else
					$_SESSION['sort'] = 'iTaskNum';
			}
			else if ($_GET['sort'] == 'date') {
				$sort = 'dDueDate';
				if (isset($_SESSION['sort']) && $_SESSION['sort'] == 'dDueDate') {
                                        $sort = 'dDueDate DESC';
                                        unset($_SESSION['sort']);
				}
                                else
					$_SESSION['sort'] = 'dDueDate';
			}
			else if ($_GET['sort'] == 'task') {
				$sort = 'sTask';
				if (isset($_SESSION["sort"]) && $_SESSION["sort"] == 'sTask') {
                                        $sort = 'sTask DESC';
                                        unset($_SESSION["sort"]);
				}
                                else
					$_SESSION['sort'] = 'sTask';
			}
			else {
				$sort = 'iTaskNum';
				if (isset($_SESSION['sort']) && $_SESSION['sort'] == 'iTaskNum') {
                                        $sort = $sort . ' DESC';
                                        unset($_SESSION['sort']);
				}
                                else
					$_SESSION['sort'] = 'iTaskNum';
			}
		}
		else {
			$sort = 'iTaskNum';
			if (isset($_SESSION['sort']) && $_SESSION['sort'] == 'iTaskNum') {
                                        $sort = $sort . ' DESC';
                                        unset($_SESSION['sort']);
			}
                        else
				$_SESSION['sort'] = 'iTaskNum';
		}

		//$todoList = $bar->getList();
		$todoList = $bar->getSortedList($sort);
		print("<table class=\"todoList\">");
		print("<tr class=\"todoHeaders\"><td class=\"todoTop\">&nbsp;</td><td class=\"todoTop\"><a href='todo.php?sort=num' class='todoSort'>Task #</a></td><td class=\"todoTop\"><a href='todo.php?sort=task' class='todoSort'>Task</a></td><td class=\"todoTop\">Assigned</td><td class=\"todoTop\"><a href=\"todo.php?sort=date\" class=\"todoSort\">Due Date</a></td><td class=\"todoTop\">Done</td><td class=\"todoTop\">Delete</td></tr>");
		foreach($todoList as $foo){
			#update complete status
			# Set to complete
			if($foo->getCompleted() != '1' && checkTaskNum($foo->getTaskNum())){
				$foo->toggleComplete();
			}			
			# set to not done
			else if($foo->getCompleted() == '1' && !checkTaskNum($foo->getTaskNum()) && isset($_POST['c'])){
				$foo->toggleComplete();
			}

			if(isset($count)){
				unset($count);
				print("<tr class=\"taskColor\"");
                if($foo->getCompleted())
                    print(" style=\"color: gray\"");
                print(" onmouseover=\"this.style.background='#CDCDD4'\" onmouseout=\"this.style.background='#EEEEEE'\">");
			}
			else{
				$count = 1;
                if($foo->getCompleted())
                    print("<tr style=\"color: gray\"");
                else
                    print("<tr");
				print(" onmouseover=\"this.style.background='#CDCDD4'\" onmouseout=\"this.style.background=''\">");
			}
			if ($foo->getAssigned()) 
                $person_name = $foo->getAssignedName();
            else
                $person_name = "";
			if ($currentUser->isGroupGuest($currentGroup)) 
				$disabled = "disabled=\"disabled\"";
			print("<td class=\"taskSel\"><input type=\"checkbox\" name=\"taskEdit[]\" value=\"".$foo->getTaskNum()."\" /></td><td class=\"taskNum\">&nbsp;#". $foo->getTaskNum()."</td><td class=\"taskDesc\">".stripTags($foo->getTask())." </td><td class=\"taskAssigned\">".$person_name."</td> <td class=\"taskDate\">".$foo->getDueDate()."</td> <td class=\"taskDone\"><input type=\"checkbox\" name=\"taskNum[]\"  value=\"".$foo->getTaskNum()."\" $disabled");
			if($foo->getCompleted() == '1'){
				print(" onclick=\"mysubmit(".$foo->getTaskNum().")\" checked=\"checked\"");
			}
			else {
				print(" onclick=\"document.myform.submit()\"");
			}
				print " /></td><td class=\"taskDel\">";
			if (!$currentUser->isGroupGuest($currentGroup))
			print "<a href=\"?d=t&amp;di=".$foo->getID()."&amp;dt=".$foo->getTaskNum()."\"><img src=\"img/delete.png\" style=\"border-style: none\" alt=\"X\" title=\"Delete Task ".$foo->getTaskNum()."\" /></a>";
			print "</td></tr>\n";
		}
		print("</table>");
		print("<div id=\"taskFoo\"></div>");
		print('<input type="hidden" name="c" value="t" />');
		print("<div id=\"task_table_footer\"><img src=\"img/arrow_ltr.png\" style=\"margin-left: 5px;\" alt=\"^\" title=\"Arrow\" /><span class=\"text\">Task Selection: <a href=\"\" onclick=\"javascript: editChk();return false\">Check All</a> / <a href=\"\" onclick=\"javascript: editUnChk();return false;\">Uncheck All</a>");
		if (!$currentUser->isGroupGuest($currentGroup))
			print(" / <input type=\"submit\" name=\"e\" value=\"Edit\" id=\"editButton\" />");
		print("  </span></div>");
	}
    else{
        print("<h1>No Task in the Todo List</h1>\n");
    }


	//var_dump($_POST);	
if (!$currentUser->isGroupGuest($currentGroup)) {
?>
<br />
<div class="box" style="width: 500px">
    <p class="box-header">
        Add a new task
    </p>
	<?php
		if($ERROR_task)
			echo "enter a task name<br />";
	?>
    <div class="newItem"><label for="task">Task <span style="color: gray">[required]</span>:</label> <input type="text" id="task" name="task" style="border: 1px solid black;width: 350px;" /></div>
	<?php
		if($ERROR_date)
			echo "correct the date please<br />";
	?>
	<div class="newItem"><label for="date">Complete By (mm/dd/yyyy) <span style="color: gray">[optional]</span>:</label> <input type="text" id="date" name="date" style="border: 1px solid black; text-align: left; width: 100px; cursor: text" onclick="calwind=dhtmlwindow.open('calboxd', 'div', 'calendarmenud', 'Select date', 'width=600px,height=165px,left=300px,top=100px,resize=0,scrolling=0'); return false" /></div>
	<div class="newItem"><label for="assigned">Who is assigned to complete this task <span style="color: gray">[optional]</span>:</label><select id="assigned" name="assigned">
	<?php
		$people = $currentGroup->getGroupMembers();
        print("<option value=\"-1\">No one</option>");
		foreach($people as $temp) {
			print("<option value=\"".$temp->getID()."\">".$temp->getLastName().",&nbsp;".$temp->getFirstName()."</option>");
		}
	?>
	</select></div>
    <div class="newItem">
	<input type="submit" name="add" value="Add" />
	<!-- <input type="submit" name="update" value="Update" /> -->
    </div>
</div>
<?php } ?>
</fieldset></form>
<div id="calendarmenud" style="display: none">
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
					print "<td><a href=\"#\" onclick=\"document.getElementById('date').value='".date( "m/d/Y", mktime( 0,0,0,$i,$j,$currentYear ) )."'; calwind.close();\">$j</a></td>";
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
</div></body></html>
