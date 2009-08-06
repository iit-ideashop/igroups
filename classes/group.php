<?php
require_once('subgroup.php');
require_once('sort.php');

if ( !class_exists( "Group" ) ) {
		
	class Group {
		var $id, $type, $semester, $name, $desc, $scratch, $scratchUpdated, $scratchUpdater;
		var $db;
		
		function Group( $id, $type, $semester, $db ) {
			$this->id = $id;
			$this->type = $type;
			$this->db = $db;
			if ( $type == 0 ) {
				$this->semester = $semester;
				$result = $this->db->igroupsQuery( "SELECT sIITID, sName, sScratch, dScratchUpdated, iScratchUpdater FROM Projects WHERE iID=$id" );
				if ( $row = mysql_fetch_row( $result ) ) {
					$this->name = $row[0];
					$this->desc = $row[1];
					$this->scratch = $row[2];
					$this->scratchUpdated = $row[3];
					$this->scratchUpdater = $row[4];
				}
			}
			else {
				$this->semester = 0;
				$result = $this->db->igroupsQuery( "SELECT sName, sScratch, dScratchUpdated, iScratchUpdater FROM Groups WHERE iID=$id" );
				if ( $row = mysql_fetch_row( $result ) ) {
					$this->name = $row[0];
					$this->desc = '';
					$this->scratch = $row[1];
					$this->scratchUpdated = $row[2];
					$this->scratchUpdater = $row[3];
				}

			}
		}
		
		function getDesc() {
			return $this->desc;
		}

		function getName() {
			return $this->name;
		}
			
		function getID() {
			return $this->id;
		}
		
		function getType() {
			return $this->type;
		}
		
		function getSemester() {
			return $this->semester;
		}
		
		function getScratch() {
			return stripslashes($this->scratch);
		}
		
		function getScratchUpdated() {
			return $this->scratchUpdated;
		}
		
		function getScratchUpdater() {
			return $this->scratchUpdater;
		}
		
		function setScratch($news, $by) {
			$news = stripslashes($news);
			$this->scratch = $news;
			$this->scratchUpdater = $by;
			$news = mysql_real_escape_string($news);
			$now = date('Y-m-d H:i:s');
			$this->scratchUpdated = $now;
			if($this->type == 0)
				$this->db->igroupsQuery("update Projects set sScratch=\"$news\", iScratchUpdater=$by, dScratchUpdated=\"$now\" where iID={$this->id}");
			else
				$this->db->igroupsQuery("update Groups set sScratch=\"$news\", iScratchUpdater=$by, dScratchUpdated=\"$now\" where iID={$this->id}");
		}

		function isActive() {
			if ( $this->type == 0 ) {
				$result = $this->db->iknowQuery( "SELECT bActiveFlag FROM Semesters WHERE iID=".$this->semester );
				$row = mysql_fetch_row( $result );
				return $row[0];
			}
			else {
				return true;
			}
		}
		
		function isGroupMember( $person ) {
			if(!is_object($person) || !$person->isValid())
				return false;
			if ( $this->getType() == 0 ) {
				$people = $this->db->iknowQuery( "SELECT iPersonID FROM PeopleProjectMap WHERE iPersonID=".$person->getID()." AND iProjectID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			}
			else {
				$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleGroupMap WHERE iPersonID=".$person->getID()." AND iGroupID=".$this->getID() );
			}
			return ( mysql_num_rows( $people ) != 0 );
		}
		
		function getGroupUsers() {
			$returnArray = array();

			if ( $this->getType() == 0 ) {
				$people = $this->db->iknowQuery( "SELECT iPersonID FROM PeopleProjectMap WHERE iProjectID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			}
			else {
				$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleGroupMap WHERE iGroupID=".$this->getID() );
			}
			while ( $row = mysql_fetch_row( $people ) ) {
				$tmp = new Person( $row[0], $this->db );
				if (!$tmp->isGroupGuest($this) && !$tmp->isGroupAdministrator($this))
					$returnArray[] = $tmp;
			}
			return $returnArray;
		}

		function getGroupGuests() {
			$returnArray = array();

			if ( $this->getType() == 0 ) {
				$people = $this->db->iknowQuery( "SELECT iPersonID FROM PeopleProjectMap WHERE iProjectID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			}
			else {
				$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleGroupMap WHERE iGroupID=".$this->getID() );
			}
			while ( $row = mysql_fetch_row( $people ) ) {
				$tmp = new Person( $row[0], $this->db );
				if ($tmp->isGroupGuest($this))
					$returnArray[] = $tmp;
			}
			return $returnArray;
		}


		function getGroupMembers() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) {
				$people = $this->db->iknowQuery( "SELECT iPersonID FROM PeopleProjectMap WHERE iProjectID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			}
			else {
				$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleGroupMap WHERE iGroupID=".$this->getID() );
			}
			while ( $row = mysql_fetch_row( $people ) ) {
				$tmp = new Person( $row[0], $this->db );
				if (!$tmp->isGroupGuest($this))
					$returnArray[] = $tmp;
			}
			return $returnArray;
		}

		function getAllGroupMembers() {
			$returnArray = array();

			if ( $this->getType() == 0 ) {
				$people = $this->db->iknowQuery( "SELECT iPersonID FROM PeopleProjectMap WHERE iProjectID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			}
			else {
				$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleGroupMap WHERE iGroupID=".$this->getID() );
			}
			while ( $row = mysql_fetch_row( $people ) ) {
				$tmp = new Person( $row[0], $this->db );
				$returnArray[] = $tmp;
			}
			return $returnArray;
		}
		
		function getSubGroups() {
			$returnArray = array();
			$subgroups = $this->db->igroupsQuery( "SELECT iID FROM SubGroups WHERE iGroupID={$this->id} ORDER BY sName");
			while ($row = mysql_fetch_row($subgroups) ) 
				$returnArray[] = new SubGroup($row[0], $this->db);
			return $returnArray;
		}

		function getGroupFolders() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) {
				$folders = $this->db->igroupsQuery( "SELECT iID FROM Folders WHERE iParentFolderID=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle" );
			}
			else {
				$folders = $this->db->igroupsQuery( "SELECT iID FROM Folders WHERE iParentFolderID=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY sTitle" );
			}
			while ( $row = mysql_fetch_row( $folders ) ) {
				$returnArray[] = new Folder( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupFiles() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bObsolete=0 AND bPrivate=0 AND bDeletedFlag=0 AND iFolderID=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle" );
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bObsolete=0 AND bPrivate=0 AND bDeletedFlag=0 AND iFolderID=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY sTitle" );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			$finalArray = array();
			foreach($returnArray as $item){
				if(!$item->isNuggetFile()){
					$finalArray[] = $item;
				}
			}
		       return $finalArray;
		}
		
		function getGroupFilesSortedBy($sort) {
			$returnArray = array();
			$add = decodeFileSort($sort);
			
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bObsolete=0 AND Files.bPrivate=0 AND Files.bDeletedFlag=0 AND Files.iFolderID=0 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType()." AND Files.iSemesterID=".$this->getSemester().$add );
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bObsolete=0 AND Files.bPrivate=0 AND Files.bDeletedFlag=0 AND Files.iFolderID=0 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType().$add );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			$finalArray = array();
			foreach($returnArray as $item){
				if(!$item->isNuggetFile()){
					$finalArray[] = $item;
				}
			}
		       return $finalArray;
		}
		
		function getIPROOfficeFolders() {
			$returnArray = array();
			
			if ( $this->getType() == 1 )
				return $returnArray;
				
			$lists = $this->db->igroupsQuery( "SELECT iListID FROM GroupListMap WHERE iGroupID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			while ( $row = mysql_fetch_row( $lists ) ) {
				$basefolder = $this->db->igroupsQuery( "SELECT iBaseFolder FROM FileLists WHERE iID=".$row[0] );
				if ( $folderid = mysql_fetch_row( $basefolder ) )
					$returnArray[] = new Folder( $folderid[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupTrashBin() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bDeletedFlag=1 AND bPrivate=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle" );
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bDeletedFlag=1 AND bPrivate=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY sTitle" );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupTrashBinSortedBy($sort) {
			$returnArray = array();
			$add = decodeFileSort($sort);
			
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bDeletedFlag=1 AND Files.bPrivate=0 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType()." AND Files.iSemesterID=".$this->getSemester().$add );
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bDeletedFlag=1 AND Files.bPrivate=0 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType().$add );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}

		function getGroupObsolete() {
			$returnArray = array();
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bObsolete=1 AND bDeletedFlag=0 AND bPrivate=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle" );
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bObsolete=1 AND bDeletedFlag=0 AND bPrivate=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY sTitle" );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupObsoleteSortedBy($sort) {
			$returnArray = array();
			$add = decodeFileSort($sort);
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bObsolete=1 AND Files.bDeletedFlag=0 AND Files.bPrivate=0 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType()." AND Files.iSemesterID=".$this->getSemester().$add);
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bObsolete=1 AND Files.bDeletedFlag=0 AND Files.bPrivate=0 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType().$add );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getUserDropboxSortedBy($userID, $sort) {
			$returnArray = array();
			$add = decodeFileSort($sort);
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE bPrivate=1 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." AND iAuthorID=$userID".$add);
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE bPrivate=1 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType().$add );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getDropboxSortedBy($sort) {
			$returnArray = array();
			$add = decodeFileSort($sort);
			if ( $this->getType() == 0 ) {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bPrivate=1 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType()." AND Files.iSemesterID=".$this->getSemester().$add);
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bPrivate=1 AND Files.iGroupID=".$this->getID()." AND Files.iGroupType=".$this->getType().$add );
			}
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupCategories() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) 
				$categories = $this->db->igroupsQuery( "SELECT iID FROM Categories WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY sName" );
			else 
				$categories = $this->db->igroupsQuery( "SELECT iID FROM Categories WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY sName" );
			
			while ( $row = mysql_fetch_row( $categories ) ) {
				$returnArray[] = new Category( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupEmails() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iCategoryID=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY iID DESC" );
			else 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iCategoryID=0 AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY iID DESC" );
				
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupEmailsSortedBy($sort) {
			$returnArray = array();
			$add = decodeEmailSort($sort);
			
			if ( $this->getType() == 0 ) 
				$emails = $this->db->igroupsQuery( "SELECT Emails.iID, People.sFName, People.sLName FROM Emails inner join People on Emails.iSenderID=People.iID WHERE Emails.iCategoryID=0 AND Emails.iGroupID=".$this->getID()." AND Emails.iGroupType=".$this->getType()." AND Emails.iSemesterID=".$this->getSemester().$add );
			else 
				$emails = $this->db->igroupsQuery( "SELECT Emails.iID, People.sFName, People.sLName FROM Emails inner join People on Emails.iSenderID=People.iID WHERE Emails.iCategoryID=0 AND Emails.iGroupID=".$this->getID()." AND Emails.iGroupType=".$this->getType().$add );
				
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getGroupAnnouncements() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) 
				$ann = $this->db->igroupsQuery( "SELECT iID FROM Announcements WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester() );
			else
				$ann = $this->db->igroupsQuery( "SELECT iID FROM Announcements WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType() );
				
			while ( $row = mysql_fetch_row( $ann ) ) {
				$returnArray[] = new GroupAnnouncement( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getWeekEvents() {
			$returnArray = array();
			
			if ( $this->getType() == 0 )
				$events = $this->db->igroupsQuery( "SELECT iID FROM Events WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." AND dDate>='".date( "Y-m-d" )."' AND dDate<'".date( "Y-m-d", mktime( 0, 0 ,0, date( "n" ), date( "j" )+7, date( "Y" ) ) )."'" );
			else
				$events = $this->db->igroupsQuery( "SELECT iID FROM Events WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND dDate>='".date( "Y-m-d" )."' AND dDate<='".date( "Y-m-d", mktime( 0, 0 ,0, date( "n" ), date( "j" )+7, date( "Y" ) ) )."'" );
			
			while ( $row = mysql_fetch_row( $events ) ) {
				$returnArray[] = new Event( $row[0], $this->db );
			}
			
			if ( $this->getType() == 0 ) {
				$events = $this->db->igroupsQuery( "SELECT iID FROM Events WHERE iGroupID=0 AND iGroupType=0 AND iSemesterID=".$this->getSemester()." AND dDate>='".date( "Y-m-d" )."' AND dDate<'".date( "Y-m-d", mktime( 0, 0 ,0, date( "n" ), date( "j" )+7, date( "Y" ) ) )."'" );
				while ( $row = mysql_fetch_row( $events ) ) {
					$returnArray[] = new Event( $row[0], $this->db );
				}
			}
			
			return $returnArray;
		}
		
		function getWeekTasks() {
			$returnArray = array();
			
			$events = $this->db->igroupsQuery("SELECT iID FROM Tasks WHERE iTeamID=".$this->getID()." and dClosed is null AND dDue>='".date( "Y-m-d" )."' AND dDue<='".date( "Y-m-d", mktime( 0, 0 ,0, date( "n" ), date( "j" )+7, date( "Y" ) ) )."'" );
			
			while ( $row = mysql_fetch_row( $events ) ) {
				$returnArray[] = new Task( $row[0], $this->type, $this->semester, $this->db );
			}
			
			return $returnArray;
		}
		
		function getMonthEvents( $month, $year ) {
			$returnArray = array();
			
			if ( $this->getType() == 0 )
				$events = $this->db->igroupsQuery( "SELECT iID FROM Events WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." AND dDate>='".date( "Y-m-d", mktime( 0, 0 ,0, $month, 1, $year ) )."' AND dDate<='".date( "Y-m-d", mktime( 0, 0 ,0, $month+1, 0, $year ) )."'" );
			else
				$events = $this->db->igroupsQuery( "SELECT iID FROM Events WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND dDate>='".date( "Y-m-d", mktime( 0, 0 ,0, $month, 1, $year ) )."' AND dDate<='".date( "Y-m-d", mktime( 0, 0 ,0, $month+1, 0, $year ) )."'" );
			
			while ( $row = mysql_fetch_row( $events ) ) {
				$returnArray[] = new Event( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function getMonthTasks( $month, $year ) {
			$returnArray = array();
			
				$events = $this->db->igroupsQuery( "SELECT iID FROM Tasks WHERE iTeamID=".$this->getID()." and dClosed is null AND dDue>='".date( "Y-m-d", mktime( 0, 0 ,0, $month, 1, $year ) )."' AND dDue<='".date( "Y-m-d", mktime( 0, 0 ,0, $month+1, 0, $year ) )."'" );
			
			while ( $row = mysql_fetch_row( $events ) ) {
				$returnArray[] = new Task( $row[0], $this->type, $this->semester, $this->db );
			}
			
			return $returnArray;
		}

		function findPastIPROs() {
			$sectNum = $this->name;
			if (strchr($sectNum, ' '))
				$sectNum2 = str_replace(' ', '', $sectNum);
			else
				$sectNum2 = substr($sectNum, 0, 4) . ' ' . substr($sectNum, 5);
			$query = $this->db->iknowQuery("SELECT p.iID, m.iSemesterID, s.sSemester from Projects p, ProjectSemesterMap m, Semesters s where s.iID = m.iSemesterID and p.iID=m.iProjectID and (p.sIITID='$sectNum' or p.sIITID='$sectNum2') ORDER BY m.iSemesterID DESC");
			
			if ($result = mysql_fetch_array($query)) {
				$array = array();
				if (($result['iID'] != $this->id) || ($result['iSemesterID'] != $this->semester))
					$array[] = new Group($result['iID'], 0, $result['iSemesterID'], $this->db);
				while ($result = mysql_fetch_array($query)) {
					if (($result['iID'] != $this->id) || ($result['iSemesterID'] != $this->semester))
						$array[] = new Group($result['iID'], 0, $result['iSemesterID'], $this->db);
				}
				return $array;
			}
			else
				return false;
		}
		
		function getMonthIproEvents( $month, $year ) {
			$returnArray = array();
			
			if ( $this->getType() == 0 )
				$events = $this->db->igroupsQuery( "SELECT iID FROM Events WHERE iGroupID=0 AND iGroupType=0 AND iSemesterID=".$this->getSemester()." AND dDate>='".date( "Y-m-d", mktime( 0, 0 ,0, $month, 1, $year ) )."' AND dDate<='".date( "Y-m-d", mktime( 0, 0 ,0, $month+1, 0, $year ) )."'" );
			else
				return $returnArray;
			
			while ( $row = mysql_fetch_row( $events ) ) {
				$returnArray[] = new Event( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function getRecentEmails() {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY iID DESC LIMIT 5" );
			else 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY iID DESC LIMIT 5" );
			
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function getRecentFiles() {
			$returnArray = array();
			
			if ( $this->getType() == 0 )
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." AND bPrivate=0 ORDER BY iID DESC LIMIT 5" );
			else 
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND bPrivate=0 ORDER BY iID DESC LIMIT 5" );
		
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function getGroupPictures() {
			$returnArray = array();
			
			if ( $this->getType() == 0 )
				$pics = $this->db->igroupsQuery( "SELECT iID FROM Pictures WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester()." ORDER BY iID" );
			else
				$pics = $this->db->igroupsQuery( "SELECT iID FROM Pictures WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." ORDER BY iID" );
			
			while ( $row = mysql_fetch_row( $pics ) ) {
				$returnArray[] = new GroupPicture( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function getRandomGroupPicture() {
			if ( $this->getType() == 0 )
				$pics = $this->db->igroupsQuery( "SELECT iID FROM Pictures WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester() );
			else
				$pics = $this->db->igroupsQuery( "SELECT iID FROM Pictures WHERE iGroupID=".$this->getID()." AND iGroupType=".$this->getType() );
			if ( mysql_num_rows( $pics ) > 0 ) {	
				$num = rand( 0, mysql_num_rows( $pics )-1 );
				mysql_data_seek( $pics, $num );
				$row = mysql_fetch_row( $pics );
				return new GroupPicture( $row[0], $this->db );
			}
			else
				return false;
		}
		
		function searchEmailByText( $text ) {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE MATCH( sSubject, sBody ) AGAINST ( '$text' ) AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester() );
			else 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE MATCH( sSubject, sBody ) AGAINST ( '$text' ) AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType() );
			
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function searchEmailBySender( $sender ) {
			$returnArray = array();
			
			if ( $this->getType() == 0 ) 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iSenderID=$sender AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType()." AND iSemesterID=".$this->getSemester() );
			else 
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iSenderID=$sender AND iGroupID=".$this->getID()." AND iGroupType=".$this->getType() );
			
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function usesFileList( $id ) {
			if ( $this->getType() == 0 ) 
				$list = $this->db->igroupsQuery( "SELECT * FROM GroupListMap WHERE iListID=$id AND iGroupID=".$this->getID()." AND iSemesterID=".$this->getSemester() );
			else 
				return false;
			
			return ( mysql_num_rows( $list ) > 0 );
		}
		
		function addFileList( $id ) {
			$this->db->igroupsQuery( "INSERT INTO GroupListMap( iListID, iGroupID, iSemesterID ) VALUES ( $id, ".$this->id.", ".$this->semester." )" );
		}
		
		function getTimeLog() {
			if ( $this->getType() == 0 ) {
				return new TimeLog( $this->id, $this->semester, $this->db );
			}
			else
				return false;
		}

		function getActiveNuggets(){
			$returnArray = array();
			$nuggets = $this->db->igroupsQuery("SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID =".$this->getID()." AND iSemesterID =".$this->getSemester());
			while($row = mysql_fetch_array($nuggets)){
				$returnArray[] = new Nugget( $row[0], $this->db,0);
			}
			return $returnArray;
		}

		function getInactiveNuggets(){
			$returnArray = array();

			$nuggets = $this->db->igroupsQuery("SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID =".$this->getID()." AND iSemesterID !=".$this->getSemester());
			while($row = mysql_fetch_row($nuggets)){
			       $returnArray[] = new Nugget( $row[0], $this->db, 1);
			}
		       return $returnArray;

		}
		function getNuggets(){
		       $returnArray = array();

		       $nuggets = $this->db->igroupsQuery("SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID =".$this->getID());
		       while($row = mysql_fetch_row($nuggets)){
			       $returnArray[] = new Nugget( $row[0], $this->db, 0);
		       }
		       return $returnArray;
		}

		function getNuggetFiles(){
			$returnArray = array();

			$nuggets = $this->getNuggets();
			foreach($nuggets as $nugget){
				$nuggetID = $nugget->getID();
				$query = "SELECT distinct iFileID FROM nuggetFileMap WHERE iNuggetID = $nuggetID";
				$results = $this->db->igroupsQuery($query);
				while($row = mysql_fetch_row($results)){
				       $returnArray[] = new File($row[0], $this->db);
			       }
		       }
		       return $returnArray;
		}

	}
}
?>
