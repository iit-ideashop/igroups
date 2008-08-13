<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/semester.php" );
	
	$db = new dbConnection();
	
ob_start();
	//-----Process Login------------------------//

	// Remember login info for 1 week
	if (isset($_POST['remember']) && isset($_POST['username']) && isset($_POST['password'])) {
                setcookie('userID', $_POST['username'], time()+60*60*24*7);
                setcookie('password', $_POST['password'], time()+60*60*24*7);
        }

	if ( isset( $_COOKIE['userID'] ) && isset($_COOKIE['password']) ) {
                if ( strpos( $_COOKIE['userID'], "@" ) === FALSE )
                        $userName = $_COOKIE['userID'] . "@iit.edu";
		else
			$userName = $_COOKIE['userID'];
                $user = $db->iknowQuery( "SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'" );
                if ( ( $row = mysql_fetch_row( $user ) ) && ( md5($_COOKIE['password']) == $row[1] ) ) {
                        $_SESSION['userID'] = $row[0];
?>
                        <script type="text/javascript">
                                parent.mainFrame.location.href="main.php";
                        </script>
<?php
                }
                else {
                        $_SESSION['loginError'] = true;
                        header( "Location: login.php" );
                }
        }
	
	else if ( isset( $_POST['login'] ) ) {
		if ( strpos( $_POST['username'], "@" ) === FALSE ) 
			$_POST['username'] .= "@iit.edu";
		$user = $db->iknowQuery( "SELECT iID,sPassword FROM People WHERE sEmail='".$_POST['username']."'" );
		if ( ( $row = mysql_fetch_row( $user ) ) && ( md5($_POST['password']) == $row[1] ) ) {
			$_SESSION['userID'] = $row[0];
?>
			<script type="text/javascript">
				parent.mainFrame.location.href="main.php";
			</script>
<?php
		}
		else {
			$_SESSION['loginError'] = true;
			header( "Location: login.php" );
		}
	}
ob_end_flush();

	function selectGroup( $string ) {
		$temp=explode( ",", $string );
		if ( count( $temp ) == 3 ) {
			$_SESSION['selectedGroup'] = $temp[0];
			$_SESSION['selectedGroupType'] = $temp[1];
			$_SESSION['selectedSemester'] = $temp[2];
		}
		unset( $_SESSION['selectedFolder'] );
		unset( $_SESSION['selectedSpecial'] );
		unset( $_SESSION['expandFolders'] );
		unset( $_SESSION['selectedCategory'] );
?>
		<script type="text/javascript">
			parent.mainFrame.location.href="grouphomepage.php";
		</script>
<?php
	}
	
	function isSelected( $group ) {
		if ( !isset( $_SESSION['selectedGroup'] ) )
			return false;
		if ( $group->getID() == $_SESSION['selectedGroup'] && $group->getType() == $_SESSION['selectedGroupType'] && $group->getSemester() == $_SESSION['selectedSemester'] )
			return true;
	}	
	
	function getLinkedName( $group ) {
		return "<a href='menu.php?selectGroup=".$group->getID().",".$group->getType().",".$group->getSemester()."'>".$group->getName()."</a>";
	}
	
	function printGroupMenu( $user, $group ) {
		print "<li><a href='files.php' target='mainFrame'>Files</a></li>";
		print "<li><a href='email.php' target='mainFrame'>Email</a></li>";
		print "<li><a href='calendar.php' target='mainFrame'>Calendar</a></li>";
		print "<li><a href='todo.php' target='mainFrame'>Todo List</a></li>";
		print "<li><a href='contactlist.php' target='mainFrame'>Contact List</a></li>";
		print "<li><a href='grouppictures.php' target='mainFrame'>Group Pictures</a></li>";
		if ( $group->getType() == 0 && !$user->isGroupGuest($group))
			print "<li><a href='logtimespent.php' target='mainFrame'>Your Timesheet</a></li>";
		if ( $user->isGroupModerator( $group ) )
			print "<li><a href='groupmanagement.php' target='mainFrame'>Manage Group</a></li>";
		if ( $group->getType() == 0 )
			print "<li><a href='viewtimesheets.php' target='mainFrame'>Time Reporting</a></li>";
		print "<li><a href='dboard/dboard.php?a=0' target='mainFrame'>Discussion Board</a></li>";
		if ( $group->getType() == 0 && !$user->isGroupGuest($group))
			print "<li><a href='budget.php' target='mainFrame'>Budget</a></li>";
	}
	
	if ( isset( $_GET['selectGroup'] ) ) {
		selectGroup( $_GET['selectGroup'] );
	}
	
	if ( !isset( $_SESSION['expandSemesters'] ) ) {
		$semester = $db->iknowQuery( "SELECT iID FROM Semesters WHERE bActiveFlag=1" );
		$row = mysql_fetch_row( $semester );
		$_SESSION['expandSemesters'] = array( $row[0] );
	}
	
	if ( isset( $_GET['toggleExpand'] ) ) {
		if ( in_array( $_GET['toggleExpand'], $_SESSION['expandSemesters'] ) ) {
			$temp = array( $_GET['toggleExpand'] );
			$_SESSION['expandSemesters'] = array_diff( $_SESSION['expandSemesters'], $temp );
		}
		else
			$_SESSION['expandSemesters'][] = $_GET['toggleExpand'];	
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Menu</title>
	<style type="text/css">
		@import url("default.css");
		
		body {
			margin:0;
		}
		
		#sidebar {
			font-family: Arial, Helvetica, sans-serif;
			font-size:10pt;
			width:200px;
			background-color:#EEEEEE;
			background-image: url('img/menu-right-border.gif');
			background-repeat: repeat-y;
			background-position: right;
			padding-top:5px;
			padding-left:5px;
		}
		
		#sidebar-bottom {
			height:33px;
			background-image: url('img/menu-bottom-border.gif');
			background-repeat: no-repeat;
			background-position: right;
		}
		
		ul.noindent {
			margin-left:0;
			padding-left:0;
		}
	</style>
</head>
<body>
<div id="sidebar">
	<div id="iprologo">
		<a href="http://ipro.iit.edu/" target="_parent"><img width=180 border="0" padding="0" margin="0" src="img/iprologo.jpg"></a>
	</div>
	<div id="igroupslogo">
		<img src="img/igroupslogo.jpg">
	</div>
<?php	
	if ( isset( $_SESSION['userID'] )) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		 die("You are not logged in.");
	
	print "Welcome, ".$currentUser->getFirstName()."<p>";
	
	$groups = $currentUser->getGroups();
	
	foreach ( $groups as $key => $group ) {
		if ( $group->getType() == 0 ) {
			$sortedIPROs[$group->getSemester()][$group->getName()] = $group;
			if ( $group->isActive() ) {
				$result = $db->iknowQuery( "SELECT * FROM ProjectSemesterMap WHERE iProjectID=".$group->getID()." AND iSemesterID!=".$group->getSemester() );
				while ( $row = mysql_fetch_array( $result ) ) {
					$newGroup = new Group( $row['iProjectID'], 0, $row['iSemesterID'], $db );
					$sortedIPROs[$newGroup->getSemester()][$newGroup->getName()] = $newGroup;
				}
			}
		}
		else
			$igroups[$group->getName()] = $group;
	}
	
	print "<ul class='noindent'><li><a href='main.php' target='mainFrame'>iGROUPS Home</a></li>";
	print "<li><a href='contactinfo.php' target='mainFrame'>My Profile</a>&nbsp;<sup><font size='-3' color='red'>NEW!</font></sup></li></ul>";
	
	@krsort( $sortedIPROs );
	
	print "Your IPROs:\n";
	print "<ul class='noindent'>\n";
	foreach ( $sortedIPROs as $key => $val ) {
		$semester = new Semester( $key, $db );
		if ( in_array( $semester->getID(), $_SESSION['expandSemesters'] ) ) {
			print "<li><a href='menu.php?toggleExpand=".$semester->getID()."'><img src='img/minus.gif' border=0></a>&nbsp;<a href='menu.php?toggleExpand=".$semester->getID()."'>".$semester->getName()."</a>";
			print "<ul>\n";
			ksort( $val );
			foreach ( $val as $useless => $group ) {
				print "<li>".getLinkedName($group);
				if ( isSelected( $group ) ) {
					print "<ul>\n";
					printGroupMenu( $currentUser, $group );
					print "</ul>\n";
				}
				print "</li>\n";
			}
			print "</ul>\n";
		}
		else
			print "<li><a href='menu.php?toggleExpand=".$semester->getID()."'><img src='img/plus.gif' border=0></a>&nbsp;<a href='menu.php?toggleExpand=".$semester->getID()."'>".$semester->getName()."</a>";
		print "</li>";
	}
	print "</ul>\n";
	
	if ( in_array( "igroups", $_SESSION['expandSemesters'] ) ) {
		print "<a href='menu.php?toggleExpand=igroups'><img src='img/minus.gif' border=0></a>&nbsp;<a href='menu.php?toggleExpand=igroups'>Your Other Groups:</a>\n";
		@ksort( $igroups );
		print "<ul>\n";
		if ( isset($igroups)) {
		foreach ( $igroups as $key => $group ) {
			print "<li>".getLinkedName( $group );
			if ( isSelected( $group ) ) {
				print "<ul>\n";
				printGroupMenu( $currentUser, $group );
				print "</ul>\n";
			}
			print "</li>\n";
		}
		}
		print "</ul>\n";
	}
	else
		print "<a href='menu.php?toggleExpand=igroups'><img src='img/plus.gif' border=0></a>&nbsp;<a href='menu.php?toggleExpand=igroups'>Your Other Groups:</a>\n";
	print "<p>";

	if ( $currentUser->isAdministrator() ) {
		if ( in_array( "admin", $_SESSION['expandSemesters'] ) ) {
			print "<a href='menu.php?toggleExpand=admin'><img src='img/minus.gif' border=0></a>&nbsp;<a href='menu.php?toggleExpand=admin'>Administrative Tools:</a>";
			print "<ul>";
			print "<li><a href='admin/group.php' target='mainFrame'>Manage Groups</a></li>";
			print "<li><a href='admin/email.php' target='mainFrame'>Email Groups</a></li>";
			print "<li><a href='admin/event.php' target='mainFrame'>Manage Calendars</a></li>";
			print "<li><a href='dboard/dboard.php?adminView=1' target='mainFrame'>Discussion Board</a></li>";
			print "<li><a href='admin/budget.php' target='mainFrame'>Manage Budgets</a></li>";
			print "<li><a href='admin/iprofiles.php' target='mainFrame'>IPRO Office Files</a></li>";
			print "<li><a href='admin/quotas.php' target='mainFrame'>Group Quotas</a></li>";
			print "<li><a href='admin/reporting.php' target='mainFrame'>Group Reporting</a></li>";
			print "<li><a href='admin/announcements.php' target='mainFrame'>Announcements</a></li>";
			print "</ul>";
		}
		else
			print "<a href='menu.php?toggleExpand=admin'><img src='img/plus.gif' border=0></a>&nbsp;<a href='menu.php?toggleExpand=admin'>Administrative tools:</a>";
		print "<p>";
	}
?>
	<ul class='noindent'>
		<li><a href='http://iknow.iit.edu' target='_top'>Visit iKNOW</a></li>
		<li><a href='http://sloth.iit.edu/~iproadmin/peerreview' target='_top'>IPRO Peer Review</a></li>
		<li><a href='UM_iGROUPS.pdf' target='_top'>iGROUPS User Manual</a></li>
		<li><a href="needhelp.php" target='mainFrame'>Need help?</a></li>
		<li><a href="login.php?logout=true">Logout</a></li>
	</ul>
	<div id="sidebar-bottom">
	</div>
</div>
</body>
</html>
