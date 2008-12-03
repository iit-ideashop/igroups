<?php
	session_start();

	include_once("globals.php");
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

	if ( isset( $_POST['logform'] ) ) {
		if ( strpos( $_POST['username'], "@" ) === FALSE ) 
			$_POST['username'] .= "@iit.edu";
		$user = $db->iknowQuery( "SELECT iID,sPassword FROM People WHERE sEmail='".$_POST['username']."'" );
		if ( ( $row = mysql_fetch_row( $user ) ) && ( md5($_POST['password']) == $row[1] ) ) {
			$_SESSION['userID'] = $row[0];
			if(isset($_GET['loggingin'])) {
?>
			<script type="text/javascript">
		<!--
			window.location.href="index.php";
		//-->
		</script>
<?php
		} }
		else if(!$_SESSION['loginError']) {
			$_SESSION['loginError'] = true;
?>
		<script type="text/javascript">
		<!--
			window.location.href="index.php";
		//-->
		</script>
<?php
		}
	}
	else if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if ( isset( $_COOKIE['userID'] ) && isset($_COOKIE['password']) ) {
		if ( strpos( $_COOKIE['userID'], "@" ) === FALSE )
			$userName = $_COOKIE['userID'] . "@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery( "SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'" );
		if ( ( $row = mysql_fetch_row( $user ) ) && ( md5($_COOKIE['password']) == $row[1] )) {
			$_SESSION['userID'] = $row[0];
			if(isset($_GET['loggingin'])) {
?>
		    <script type="text/javascript">
		<!--
			window.location.href="index.php";
		//-->
		</script>
<?php
		} }
		else if(!$_SESSION['loginError']) {
			$_SESSION['loginError'] = true;
?>
		<script type="text/javascript">
		<!--
			window.location.href="index.php";
		//-->
		</script>
<?php
		}
	}

	function selectGroup( $string ) {
		$temp=explode( ",", $string );
		if ( count( $temp ) == 3 ) {
			$_SESSION['selectedGroup'] = $temp[0];
			$_SESSION['selectedGroupType'] = $temp[1];
			$_SESSION['selectedSemester'] = $temp[2];
			setcookie('selectedGroup', $string, time()+60*60*24*365);
		}
		unset( $_SESSION['selectedFolder'] );
		unset( $_SESSION['selectedSpecial'] );
		unset( $_SESSION['expandFolders'] );
		unset( $_SESSION['selectedCategory'] );
		header("Location: grouphomepage.php");
	}
	
	if ( isset( $_GET['selectGroup'] ) ) {
		selectGroup( $_GET['selectGroup'] );
	}
	ob_end_flush();
	
	function isSelected( $group ) {
		if ( !isset( $_SESSION['selectedGroup'] ) )
			return false;
		if ( $group->getID() == $_SESSION['selectedGroup'] && $group->getType() == $_SESSION['selectedGroupType'] && $group->getSemester() == $_SESSION['selectedSemester'] )
			return true;
	}	
	
	function getLinkedName( $group ) {
		return "<a href=\"menu.php?selectGroup=".$group->getID().",".$group->getType().",".$group->getSemester()."\">".$group->getName()."</a>";
	}
	
	function printGroupMenu( $user, $group ) {
		echo "<li><a href=\"files.php\">Files</a></li>";
		echo "<li><a href=\"email.php\">Email</a></li>";
		echo "<li><a href=\"calendar.php\">Calendar</a></li>";
		echo "<li><a href=\"todo.php\">Todo List</a></li>";
		echo "<li><a href=\"contactlist.php\">Contact List</a></li>";
		echo "<li><a href=\"grouppictures.php\">Group Pictures</a></li>";
		if ( $group->getType() == 0 && !$user->isGroupGuest($group))
			echo "<li><a href=\"logtimespent.php\">Your Timesheet</a></li>";
		if ( $user->isGroupModerator( $group ) )
			echo "<li><a href=\"groupmanagement.php\">Manage Group</a></li>";
		if ( $group->getType() == 0 )
			echo "<li><a href=\"viewtimesheets.php\">Time Reporting</a></li>";
		echo "<li><a href=\"dboard/dboard.php?a=0\">Discussion Board</a></li>";
		if ( $group->getType() == 0 && !$user->isGroupGuest($group))
			echo "<li><a href=\"budget.php\">Budget</a></li>";
		echo "<li><a href=\"nuggets.php\">iKnow Nuggets</a></li>";
		echo "<li><a href=\"bookmarks.php\">Bookmarks</a></li>";
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
		else {
			$_SESSION['expandSemesters'][] = $_GET['toggleExpand'];	
		}
	}

?>
<div id="sidebar">
	<div id="igroupslogo">
		<img src="skins/<?php echo $skin; ?>/img/iGroupslogo.png" alt="<?php echo $appname; ?>" title="<?php echo $appname; ?>" />
	</div>
<?php	
	if ( isset( $_SESSION['userID'] )) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
	
	echo "Welcome, ".$currentUser->getFirstName();
	
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
	
	echo "<ul class=\"noindent\"><li><a href=\"index.php\">$appname Home</a></li>\n";
	echo "<li><a href=\"contactinfo.php\">My Profile</a></li>\n";
	echo "<li><a href=\"iknow/main.php\">Browse Nuggets Library</a>&nbsp;</li>\n";
	echo "<li><a href=\"usernuggets.php\">Your Groups' Nuggets</a></li></ul>\n";

	@krsort( $sortedIPROs );
	
	if(count($sortedIPROs) > 0)
	{
		echo "Your IPROs:\n";
		echo "<ul class=\"noindent\">\n";
		foreach ( $sortedIPROs as $key => $val ) {
			$semester = new Semester( $key, $db );
			if ( in_array( $semester->getID(), $_SESSION['expandSemesters'] ) ) {
				echo "<li><a href=\"?toggleExpand=".$semester->getID()."\"><img src=\"skins/$skin/img/minus.png\" style=\"border-style: none\" alt=\"-\" /></a>&nbsp;<a href=\"?toggleExpand=".$semester->getID()."\">".$semester->getName()."</a>";
				echo "<ul>\n";
				ksort( $val );
				foreach ( $val as $useless => $group ) {
					echo "<li>".getLinkedName($group);
					if ( isSelected( $group ) ) {
						echo "<ul>\n";
						printGroupMenu( $currentUser, $group );
						echo "</ul>\n";
					}
					echo "</li>\n";
				}
				echo "</ul>\n";
			}
			else
				echo "<li><a href=\"?toggleExpand=".$semester->getID()."\"><img src=\"skins/$skin/img/plus.png\" style=\"border-style: none\" alt=\"+\" /></a>&nbsp;<a href=\"?toggleExpand=".$semester->getID()."\">".$semester->getName()."</a>";
			echo "</li>";
		}
		echo "</ul>\n";
	}
	
	if ( in_array( "igroups", $_SESSION['expandSemesters'] ) ) {
		echo "<a href=\"?toggleExpand=igroups\"><img src=\"skins/$skin/img/minus.png\" style=\"border-style: none\" alt=\"-\" /></a>&nbsp;<a href=\"?toggleExpand=igroups\">Your Other Groups:</a>\n";
		@ksort( $igroups );
		echo "<ul>\n";
		if ( isset($igroups)) {
		foreach ( $igroups as $key => $group ) {
			echo "<li>".getLinkedName( $group );
			if ( isSelected( $group ) ) {
				echo "<ul>\n";
				printGroupMenu( $currentUser, $group );
				echo "</ul>\n";
			}
			echo "</li>\n";
		}
		}
		echo "</ul>\n";
	}
	else
		echo "<a href=\"?toggleExpand=igroups\"><img src=\"skins/$skin/img/plus.png\" style=\"border-style: none\" alt=\"+\" /></a>&nbsp;<a href=\"?toggleExpand=igroups\">Your Other Groups:</a><br /><br />\n";

	if ( $currentUser->isAdministrator() ) {
		if ( in_array( "admin", $_SESSION['expandSemesters'] ) ) {
			echo "<a href=\"?toggleExpand=admin\"><img src=\"skins/$skin/img/minus.png\" style=\"border-style: none\" alt=\"-\" /></a>&nbsp;<a href=\"?toggleExpand=admin\">Administrative Tools:</a>";
			echo "<ul>";
			echo "<li><a href=\"admin/group.php\">Manage Groups</a></li>";
			echo "<li><a href=\"admin/email.php\">Email Groups</a></li>";
			echo "<li><a href=\"admin/nuggets.php\">Manage Nuggets</a></li>";
			echo "<li><a href=\"admin/event.php\">Manage Calendars</a></li>";
			echo "<li><a href=\"dboard/dboard.php?adminView=1\">Discussion Board</a></li>";
			echo "<li><a href=\"admin/budget.php\">Manage Budgets</a></li>";
			echo "<li><a href=\"admin/iprofiles.php\">IPRO Office Files</a></li>";
			echo "<li><a href=\"admin/quotas.php\">Group Quotas</a></li>";
			echo "<li><a href=\"admin/reporting.php\">Group Reporting</a></li>";
			echo "<li><a href=\"admin/people.php\">View Person</a></li>";
			echo "<li><a href=\"admin/announcements.php\">Announcements</a></li>";
			echo "<li><a href=\"admin/skins.php\">Skins</a></li>";
			//echo "<li><a href=\"admin/appearance.php\">Appearance</a></li>";
			echo "</ul>";
		}
		else
			echo "<a href=\"?toggleExpand=admin\"><img src=\"skins/$skin/img/plus.png\" style=\"border-style: none\" alt=\"+\" /></a>&nbsp;<a href=\"?toggleExpand=admin\">Administrative tools:</a>";
	}
?>
	<ul class="noindent">
		<li><a href="http://sloth.iit.edu/~iproadmin/peerreview" title="Peer Review">IPRO Peer Review</a></li>
		<li><a href="UM_iGROUPS.pdf" title="User Manual"><?php echo $appname; ?> User Manual</a></li>
		<li><a href="needhelp.php" title="Help">Need help?</a></li>
		<li><a href="login.php?logout=true" title="Logout">Logout</a></li>
	</ul>
	<div id="sidebar-bottom">
	</div>
</div>
