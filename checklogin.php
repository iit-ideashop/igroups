<?php	
	include_once( "checklogingroupless.php" );
	include_once( "classes/group.php" );

	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
	{
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
		if(!$currentGroup->isGroupMember($currentUser))
		{
			unset($_SESSION['selectedGroup']);
			unset($_SESSION['selectedGroupType']);
			unset($_SESSION['selectedSemester']);
			setcookie('selectedGroup', '', time()-60);
			die("You are not a member of this group.");
		}
	}
	else if(isset($_COOKIE['selectedGroup']))
	{
		$group = explode(",", $_COOKIE['selectedGroup']);
		$currentGroup = new Group( $group[0],$group[1], $group[2], $db );
		if(!$currentGroup->isGroupMember($currentUser))
		{
			setcookie('selectedGroup', '', time()-60);
			die("You are not a member of this group.");
		}
		$_SESSION['selectedGroup'] = $group[0];
		$_SESSION['selectedGroupType'] = $group[1];
		$_SESSION['selectedSemester'] = $group[2];
		
	}
	else
		die("You have not selected a valid group.");
?>
