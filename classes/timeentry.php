<?php
if ( !class_exists( "TimeEntry" ) ) {
	class TimeEntry {
		var $userID, $groupID, $semesterID, $entrydate, $hoursSpent, $taskDescription, $entryID, $weekID, $projTask, $db;
		
		function TimeEntry( $entryID, $db ) {
			$this->entryID = $entryID;
			$this->db = $db;
			$result = $db->igroupsQuery( "SELECT * FROM Timesheets WHERE iEntryID=$entryID" );
			if ( $row = mysql_fetch_array( $result ) ) {
				$this->userID = $row['iUserID'];
				$this->groupID = $row['iGroupID'];
				$this->semesterID = $row['iSemesterID'];
				$this->weekID = $row['iWeekID'];
				$this->entrydate = $row['dDate'];
				$this->hoursSpent = $row['iHoursSpent'];
				$this->taskDescription = new SuperString( $row['sTaskDescription'] );
				$this->projTask = $row['bProjTask'];
			}
		}
		
		function getID() {
			return $this->entryID;
		}

		function getUserID() {
			return $this->userID;
		}
		
		function getGroupID() {
			return $this->groupID;
		}
		
		function getSemesterID() {
			return $this->semesterID;
		}

		function getWeekID() {
			return $this->weekID;
		}
		
		function getDate() {
			$temp = explode( "-", $this->entrydate );
			return date( "m/d/Y", mktime( 0, 0, 0, $temp[1], $temp[2], $temp[0] ) );
		}
		
		function getDateDB() {
			return $this->date;
		}
		
		function getHoursSpent() {
			return $this->hoursSpent;
		}
		
		function getTaskDescription() {
			return $this->taskDescription->getHTMLString();
		}

		function delete() {
			mysql_query("DELETE FROM Timesheets WHERE iEntryID={$this->entryID}");
		}
		
		function printEntryRow() {
			$user = new Person( $this->userID, $this->db );
			print "<tr><td>".$user->getFullName()."</td><td>".$this->getDate()."</td><td>".$this->getHoursSpent()."</td><td>".$this->getTaskDescription()."</td></tr>";
		}
	}
	
	function createTimeEntry( $userID, $groupID, $semesterID, $date, $hours, $description, $db ) {
		$temp = explode( "/", $date );
		$dbDate = date("Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2]));
		$query = $db->igroupsQuery( "SELECT iID FROM Weeks WHERE dStartDate <= \"$dbDate\" and dEndDate >= \"$dbDate\"");
		$result = mysql_fetch_row($query);
		$weekID = $result[0];
		if (!$hours)
			$hours = 0;
		if ( $description != "" && count( $temp ) == 3 ) {
			$desc = new SuperString( $description );
			if ($weekID != null)
				$db->igroupsQuery( "INSERT INTO Timesheets ( iUserID, iGroupID, iSemesterID, dDate, iHoursSpent, sTaskDescription, iWeekID ) VALUES ( $userID, $groupID, $semesterID, '".$dbDate."', $hours, '".$desc->getDBString()."', $weekID )" );
			else	
				$db->igroupsQuery( "INSERT INTO Timesheets ( iUserID, iGroupID, iSemesterID, dDate, iHoursSpent, sTaskDescription ) VALUES ( $userID, $groupID, $semesterID, '".$dbDate."', $hours, '".$desc->getDBString()."' )" );
			return new TimeEntry( $db->igroupsInsertID(), $db );
		}
		return false;
	}

	function createProjTask( $userID, $groupID, $semesterID, $date, $hours, $description, $db ) {
		$temp = explode( "/", $date );
		$dbDate = date("Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2]));
		$query = $db->igroupsQuery( "SELECT iID FROM Weeks WHERE dStartDate <= \"$dbDate\" and dEndDate >= \"$dbDate\"");
		$result = mysql_fetch_row($query);
		$weekID = $result[0];
		if (!$hours)
			$hours=0;
		if ( $description != "" && count( $temp ) == 3 ) {
			$desc = new SuperString( $description );
			if ($weekID != null)
				$db->igroupsQuery( "INSERT INTO Timesheets ( iUserID, iGroupID, iSemesterID, dDate, iHoursSpent, sTaskDescription, iWeekID, bProjTask ) VALUES ( $userID, $groupID, $semesterID, '".$dbDate."', $hours, '".$desc->getDBString()."', $weekID, 1 )" );
			else
				$db->igroupsQuery( "INSERT INTO Timesheets ( iUserID, iGroupID, iSemesterID, dDate, iHoursSpent, sTaskDescription, bProjTask ) VALUES ( $userID, $groupID, $semesterID, '".$dbDate."', $hours, '".$desc->getDBString()."', 1 )" );
			return new TimeEntry( $db->igroupsInsertID(), $db );
		}
		return false;
	}
}
?>
