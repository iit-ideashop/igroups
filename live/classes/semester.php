<?php
if ( !class_exists( "Semester" ) ) {
	class Semester {
		var $id, $name, $active, $db;
	
		function Semester( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			$semester = $db->iknowQuery( "SELECT * FROM Semesters WHERE iID=$id" );
			if ( $row = mysql_fetch_array( $semester ) ) {
				$this->name = $row['sSemester'];
				$this->active = $row['bActiveFlag'];
			}
		}
		
		function getID() {
			return $this->id;
		}
		
		function getName() {
			return $this->name;
		}
		
		function isActive() {
			return $this->active;
		}
		
		function getGroups() {
			$returnArray = array();

			$groups = $this->db->iknowQuery( "SELECT iProjectID FROM ProjectSemesterMap WHERE iSemesterID=".$this->id );
			while ( $row = mysql_fetch_row( $groups ) ) {
				$returnArray[] = new Group( $row[0], 0, $this->id, $this->db );
			}
			
			return $returnArray;
		}
	}
}
?>
