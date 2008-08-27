<?php
include_once( "superstring.php" );
include_once( "timeentry.php" );

if ( !class_exists( "TimeLog" ) ) {
	class TimeLog {
		var $groupID, $semesterID, $db;
		
		function TimeLog( $group, $semester, $db ) {
			$this->groupID = $group;
			$this->semesterID = $semester;
			$this->db = $db;
		}
		
		function getWeeks( $userID ) {
			$weekArray = array();

			$results = $this->db->igroupsQuery( "SELECT DISTINCT t.iWeekID, w.dStartDate, w.dEndDate FROM Timesheets t, Weeks w WHERE t.iWeekID = w.iID AND t.iUserID=$userID AND t.iGroupID=\"{$this->groupID}\" AND t.iSemesterID=\"{$this->semesterID}\" AND t.bProjTask=0 ORDER BY w.dStartDate DESC");

			while ($row = mysql_fetch_row($results)) {
				$weekArray[] = array('iID' => $row[0], 'dStartDate' => $row[1], 'dEndDate' => $row[2]);
			}

			return $weekArray;
		}
		
		function getTaskWeeks( $userID ) {
                        $weekArray = array();

                        $results = $this->db->igroupsQuery( "SELECT DISTINCT t.iWeekID, w.dStartDate, w.dEndDate FROM Timesheets t, Weeks w WHERE t.iWeekID = w.iID AND t.iUserID=$userID AND t.iGroupID=\"{$this->groupID}\" AND t.iSemesterID=\"{$this->semesterID}\" AND t.bProjTask=1 ORDER BY w.dStartDate DESC");

                        while ($row = mysql_fetch_row($results)) {
                                $weekArray[] = array('iID' => $row[0], 'dStartDate' => $row[1], 'dEndDate' => $row[2]);
                        }

                        return $weekArray;
                }

		function getEntriesByUser( $userID ) {
			$returnArray = array();
			
			$results = $this->db->igroupsQuery( "SELECT iEntryID FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND iSemesterID=".$this->semesterID." AND bProjTask=0 ORDER BY dDate");
			
			while ( $row = mysql_fetch_row( $results ) ) {
				$returnArray[] = new TimeEntry( $row[0], $this->db );
			}
			
			return $returnArray;
		}

		function getTasksByUser( $userID ) {
                        $returnArray = array();

                        $results = $this->db->igroupsQuery( "SELECT iEntryID FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND iSemesterID=".$this->semesterID." AND bProjTask=1 ORDER BY dDate");

                        while ( $row = mysql_fetch_row( $results ) ) {
                                $returnArray[] = new TimeEntry( $row[0], $this->db );
                        }

                        return $returnArray;
                }

		function getEntriesByUserAndWeek( $userID, $weekID ) {
			$returnArray = array();
                        $results = $this->db->igroupsQuery( "SELECT iEntryID FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND iWeekID=".$weekID." AND iSemesterID=".$this->semesterID." AND bProjTask=0 ORDER BY dDate" );
                        while ( $row = mysql_fetch_row( $results ) ) {
                                $returnArray[] = new TimeEntry( $row[0], $this->db );
                        }
                        return $returnArray;
		}

		function getTasksByUserAndWeek( $userID, $weekID ) {
                        $returnArray = array();
                        $results = $this->db->igroupsQuery( "SELECT iEntryID FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND iWeekID=".$weekID." AND iSemesterID=".$this->semesterID." AND bProjTask=1 ORDER BY dDate" );
                        while ( $row = mysql_fetch_row( $results ) ) {
                                $returnArray[] = new TimeEntry( $row[0], $this->db );
                        }
                        return $returnArray;
                }
				
		function getHoursSpentByUser( $userID ) {
			$results = $this->db->igroupsQuery( "SELECT SUM(iHoursSpent) FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND bProjTask=0 AND iSemesterID=".$this->semesterID );
			
			$row = mysql_fetch_row( $results );
			
			return $row[0];
		}

		function getTaskHoursSpentByUser( $userID ) {
                        $results = $this->db->igroupsQuery( "SELECT SUM(iHoursSpent) FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND bProjTask=1 AND iSemesterID=".$this->semesterID );

                        $row = mysql_fetch_row( $results );

                        return $row[0];
                }

		function getHoursSpentByWeek( $weekID ) {
			$results = $this->db->igroupsQuery( "SELECT SUM(iHoursSpent) FROM Timesheets WHERE iGroupID={$this->groupID} AND iWeekID={$weekID} AND bProjTask=0 AND iSemesterID={$this->semesterID}");
			$row = mysql_fetch_row($results);
			return $row[0];
		}

		function getAvgHoursSpentByWeek ($weekID) {
			$weekTotal = $this->getHoursSpentByWeek($weekID);
			$query = $this->db->igroupsQuery("SELECT count(DISTINCT iUserID) FROM Timesheets WHERE iGroupID={$this->groupID} AND iWeekID={$weekID} AND bProjTask=0 AND iSemesterID={$this->semesterID}");
			$count = mysql_fetch_row($query);
                        return round($weekTotal/$count[0],1);
		}

		function getAvgHoursSpentPerWeek () {
			$query = $this->db->igroupsQuery("SELECT count(DISTINCT iWeekID) FROM Timesheets WHERE iGroupID={$this->groupID} AND AND bProjTask=0 AND iSemesterID={$this->semesterID}");
			$weeks = mysql_fetch_row($query);
			return round(($this->getTotalHoursSpent()/$weeks[0]), 1);
		}

		function getHoursSpentByUserAndWeek( $userID, $weekID ) {
			$results = $this->db->igroupsQuery( "SELECT SUM(iHoursSpent) FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND iWeekID=".$weekID." AND bProjTask=0 AND iSemesterID=".$this->semesterID );
                        $row = mysql_fetch_row( $results );
                        return $row[0];
		}

		function getTaskHoursSpentByUserAndWeek( $userID, $weekID ) {
                        $results = $this->db->igroupsQuery( "SELECT SUM(iHoursSpent) FROM Timesheets WHERE iUserID=$userID AND iGroupID=".$this->groupID." AND iWeekID=".$weekID." AND bProjTask=1 AND iSemesterID=".$this->semesterID );
                        $row = mysql_fetch_row( $results );
                        return $row[0];
                }
		
		function getEntriesByDateSpan( $date1, $date2 ) {
			$temp = explode( "/", $date1 );
			$dbdate1 = date( "Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2] ) );
			$temp = explode( "/", $date2 );
			$dbdate2 = date( "Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2] ) );
			
			$returnArray = array();
			
			$results = $this->db->igroupsQuery( "SELECT iEntryID FROM Timesheets WHERE dDate<='$dbdate2' AND dDate>='$dbdate1' AND iGroupID=".$this->groupID." AND bProjTask=0 AND iSemesterID=".$this->semesterID );
			
			while ( $row = mysql_fetch_row( $results ) ) {
				$returnArray[] = new TimeEntry( $row[0], $this->db );
			}
			
			return $returnArray;
		}
		
		function getTotalHoursSpent() {
			$results = $this->db->igroupsQuery( "SELECT SUM(iHoursSpent) FROM Timesheets WHERE iGroupID=".$this->groupID." AND bProjTask=0 AND iSemesterID=".$this->semesterID );
			
			$row = mysql_fetch_row( $results );
			
			return $row[0];
		}
	
	}
}
