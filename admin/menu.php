<?php
	session_start();

	include_once('../globals.php');
	include_once('../classes/db.php');
	include_once('../classes/person.php');
	include_once('../classes/group.php');
	include_once('../classes/semester.php');
	
	$db = new dbConnection();
	
	ob_start();
	//-----Process Login------------------------//

	// Remember login info for 1 week
	if(isset($_POST['remember']) && isset($_POST['username']) && isset($_POST['password']))
	{
		setcookie('userID', $_POST['username'], time()+60*60*24*7);
		setcookie('password', $_POST['password'], time()+60*60*24*7);
	}

	if(isset($_POST['logform']))
	{
		$username = mysql_real_escape_string(stripslashes($_POST['username']));
		if(strpos($username, '@') === false)
			$username .= '@iit.edu';
		$user = $db->query("SELECT iID,sPassword FROM People WHERE sEmail='$username'");
		if(($row = mysql_fetch_row($user)) && md5($_POST['password']) == $row[1])
		{
			$_SESSION['userID'] = $row[0];
			if(isset($_GET['loggingin'])){
				header('Location: ../index.php');
      }
		}
		else if(!$_SESSION['loginError'])
		{
			$_SESSION['loginError'] = true;
			header('Location: ../index.php');
		}
	}
	else if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']))
	{
		if(strpos($_COOKIE['userID'], '@') === false)
			$userName = $_COOKIE['userID'] . '@iit.edu';
		else
			$userName = $_COOKIE['userID'];
		$user = $db->query("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && md5($_COOKIE['password']) == $row[1])
		{
			$_SESSION['userID'] = $row[0];
			if(isset($_GET['loggingin']))
			{
?>
				<script type="text/javascript">
				<!--
					window.location.href="../index.php";
				//-->
				</script>
<?php
			}
		}
		else if(!$_SESSION['loginError'])
		{
			$_SESSION['loginError'] = true;
?>
			<script type="text/javascript">
			<!--
				window.location.href="../index.php";
			//-->
			</script>
<?php
		}
	}

  // takes a particlular groups parameters and goes to that gropus page
  // this function is called from ???
	function selectGroup($string)
	{
		$temp = explode(',', $string);
		if(count($temp) == 3)
		{
			$_SESSION['selectionMade'] = 1;
			$_SESSION['selectedGroup'] = $temp[0];
			$_SESSION['selectedGroupType'] = $temp[1];
			$_SESSION['selectedSemester'] = $temp[2];
			setcookie('selectedGroup', $string, time()+60*60*24*365);
		}
		unset($_SESSION['selectedFolder']);
		unset($_SESSION['selectedSpecial']);
		unset($_SESSION['expandFolders']);
		unset($_SESSION['selectedCategory']);
		header('Location: ../grouphomepage.php');
	}
	
  //calls the function above depending on whether the group is known or not
	if(isset($_GET['selectGroup']))
	{
		selectGroup($_GET['selectGroup']);
  }
	else 
	{
			if (!isset($_SESSION['selectionMade']))
			{	
					$activegroups = $currentUser->getActiveGroups();

					if(!empty($activegroups))
					{
						$defaultactivegroup = $activegroups[0];

						$_SESSION['activateDefaultMenu'] = 1;
						$_SESSION['selectedGroup'] = $defaultactivegroup->getID();
						$_SESSION['selectedGroupType'] = $defaultactivegroup->getType();
						$_SESSION['selectedSemester'] = $defaultactivegroup->getSemester();
					}
			}
			ob_end_flush();
	}
	
  
	function isSelected($group)
	{
		if(!isset($_SESSION['selectedGroup']))
			return false;
		if($group->getID() == $_SESSION['selectedGroup'] && $group->getType() == $_SESSION['selectedGroupType'] && $group->getSemester() == $_SESSION['selectedSemester'])
			return true;
	}	
	
	function getLinkedName($group)
	{
		return "<a href=\"menu.php?selectGroup=".$group->getID().",".$group->getType().",".$group->getSemester()."\">".$group->getName()."</a>";
	}
	
	function getLink($group){
		return "menu.php?selectGroup=".$group->getID().",".$group->getType().",".$group->getSemester();
	}

  /* Prints the menu links for a given group */
  /* TODO: It is best to include a <ul> tag here */
	function printGroupMenu($user, $group)
	{
    /* print the groups sub navigation menu */
    /* TODO: move this so that it can be separated into a sub navigation menu */ 
		/* start sub navigation list */		
		echo "<ul id=\"userMenu\" class=\"subnavigation\">\n";
		echo "<li><a href=\"../files.php\">Files</a></li>\n";
		echo "<li><a href=\"../email.php\">Email</a></li>\n";
		echo "<li><a href=\"../tasks.php\">Tasks</a></li>\n";
		echo "<li><a href=\"../calendar.php\">Calendar</a></li>\n";
		echo "<li><a href=\"../contactlist.php\">Contact List</a></li>\n";
		echo "<li><a href=\"../grouppictures.php\">Group Pictures</a></li>\n";
		echo "<li><a href=\"../dboard/dboard.php?a=0\">Discussion Board</a></li>\n";
		
   if($group->getType() == 0 && !$user->isGroupGuest($group))
			echo "<li><a href=\"budget.php\">Budget</a></li>\n";
		echo "<li><a href=\"../nuggets.php\">iKnow Nuggets</a></li>\n";
		echo "<li><a href=\"../bookmarks.php\">Bookmarks</a></li>\n";

    /* If user is a moderator, add a link to the group management page */
    if($user->isGroupModerator($group))
			echo "<li><a href=\"../groupmanagement.php\">Manage Group</a></li>\n";
		
		echo "</ul>\n";
	 /* end sub navigation list */
	}

	if(!isset($_SESSION['expandSemesters']))
	{
		$semester = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($semester);
		$_SESSION['expandSemesters'] = array($row[0]);
	}

	
	if(isset($_GET['toggleExpand']))
	{
		if(in_array($_GET['toggleExpand'], $_SESSION['expandSemesters']))
		{
			$temp = array($_GET['toggleExpand']);
			$_SESSION['expandSemesters'] = array_diff($_SESSION['expandSemesters'], $temp);
		}
		else
			$_SESSION['expandSemesters'][] = $_GET['toggleExpand'];	
	}


	if(isset($_SESSION['userID'])){
		$currentUser = new Person($_SESSION['userID'], $db);
	}
	else
		die('You are not logged in.');
	
	echo "<p id=\"userWelcomeMsg\" class=\"sideBarText\">Welcome, {$currentUser->getFirstName()} </p>";
	
	$groups = $currentUser->getGroups();
	
	foreach($groups as $key => $group)
	{
	
			$sortedIPROs[$group->getSemester()][$group->getName()] = $group;
			if($group->isActive())
			{
				$result = $db->query( "SELECT * FROM ProjectSemesterMap WHERE iProjectID=".$group->getID()." AND iSemesterID!=".$group->getSemester());
				while($row = mysql_fetch_array($result))
				{
					$newGroup = new Group($row['iProjectID'], 0, $row['iSemesterID'], $db);
					$sortedIPROs[$newGroup->getSemester()][$newGroup->getName()] = $newGroup;
				}
			}
	}

  //Sort IPRO array by key in reverse order
	@krsort($sortedIPROs);
	
	if(count($sortedIPROs) > 0)
	{
 		/********************* generate semester selection list ********************************/
    echo "<select id=\"semesterlist\" onChange=\"gotoSemesterUrl()\">";
    echo "<option value=\"\"> Select IPRO </option>\n";
		
	foreach($sortedIPROs as $key => $val)
		{
			$semester = new Semester($key, $db);
			ksort($val);
				
			foreach($val as $useless => $group)
			{				
				 echo "<option value=\"".getLink($group)."\">".$group->getName().", ".$semester->getName()."</option>\n";
 				}
    }

		if(isset($igroups))
		{
				@ksort($igroups);
				foreach($igroups as $key => $group)
				{
        	echo "<option value=\"".getLink($group)."\">".$group->getName().", ".$semester->getName()."</option>\n";
				}
		}

     echo "</select><br />";
    /**************************** end selection list generation *****************************/

		//echo "<span>Your IPROs:</span>\n";
    // start of list 
		//echo "<ul class=\"noindent\">\n";

		/******************************** generate list of ipros*********************************/
		foreach($sortedIPROs as $key => $val)
		{  
			
			$semester = new Semester($key, $db);
			if(in_array($semester->getID(), $_SESSION['expandSemesters']) || $semester->getID() == $_SESSION['selectedSemester'])
			{
				if(($semester->getID() == $_SESSION['selectedSemester']) || (isset($_SESSION['activateDefaultMenu']) && $_SESSION['activateDefaultMenu'] == 1 && $semester->isActive()))
				echo "<p class=\"menuTitle\">".$semester->getName()."</p>";
				echo "\n<ul>\n";
				
				ksort($val);
				
				foreach($val as $useless => $group)
				{
		
					/* check if group was the one selected */
					if(isSelected($group))
					{
							echo "<li><p id=\"semesterIgroup\">".$group->getName()."</p>\n";
							printGroupMenu( $currentUser, $group );
							echo "</li>\n";
					}
					else if(isset($_SESSION['activateDefaultMenu']) && $_SESSION['activateDefaultMenu'] == 1 && $group->isActive())
					{
  						$_SESSION['activateDefaultMenu'] = 0 ;
							echo "<li><p id=\"semesterIgroup\">".$group->getName()."</p>\n";
							printGroupMenu( $currentUser, $group );
							echo "</li>\n";
					}
						
					
				} // end for 
				echo "</ul>\n";
			} // end if
       
		} // end for loop
		/********************* end generation of ipro list generation ******************/

	}// end if statement

	if($currentUser->isAdministrator())
	{
				if(in_array('admin', $_SESSION['expandSemesters']))
		{ 
      echo "<ul><li><a id=\"adminTools\" href=\"?toggleExpand=admin\"><p>Admin Tools</p></a>\n";
			echo "<ul id=\"adminnavigation\"class=\"subnavigation\">\n";
			echo "<li><a href=\"group.php\">Manage Groups</a></li>\n";
			echo "<li><a href=\"semesters.php\">Manage Semesters</a></li>\n";
			echo "<li><a href=\"ods_update.php\">Load Cognos/ODS File</a></li>\n";
			echo "<li><a href=\"email.php\">Email Groups</a></li>\n";
			echo "<li><a href=\"nuggets.php\">Manage Nuggets</a></li>\n";
			echo "<li><a href=\"event.php\">Manage Calendars</a></li>\n";
			echo "<li><a href=\"../dboard/dboard.php?adminView=1\">Discussion Board</a></li>\n";
			echo "<li><a href=\"../budget.php\">Manage Budgets</a></li>\n";
			echo "<li><a href=\"iprofiles.php\">IPRO Office Files</a></li>\n";
			echo "<li><a href=\"quotas.php\">Group Quotas</a></li>\n";
			echo "<li><a href=\"reporting.php\">Group Reporting</a></li>\n";
			echo "<li><a href=\"people.php\">View Person</a></li>\n";
			echo "<li><a href=\"announcements.php\">Announcements</a></li>\n";
			echo "<li><a href=\"bookmarks.php\">Bookmarks</a></li>\n";
			echo "<li><a href=\"skins.php\">Skins</a></li>\n";
			echo "<li><a href=\"appear.php\">Appearance</a></li>\n";
			echo "<li><a href=\"help.php\">Manage Help Center</a></li>\n";
			echo "</ul>\n";
      echo "</li>\n</ul>\n";
		}
		else
			echo "<a href=\"?toggleExpand=admin\"><img src=\"skins/$skin/img/plus.png\" alt=\"+\" /></a>&nbsp;<a href=\"?toggleExpand=admin\">Administrative tools:</a>";
	}
?>	
