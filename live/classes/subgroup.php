<?php
if ( !class_exists( "SubGroup" ) ) {
	class SubGroup {
		var $id, $groupID, $name;
		var $db;
		
		function SubGroup( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			$this->semester = $semester;
			$result = $this->db->igroupsQuery( "SELECT * FROM SubGroups WHERE iID=$id" );
				if ( $row = mysql_fetch_array( $result ) ) {
					$this->name = $row['sName'];
					$this->groupID = $row['iGroupID'];
				}
		}

		function getName() {
			return $this->name;
		}
			
		function getID() {
			return $this->id;
		}
		
		function getGroupID() {
			return $this->groupID;
		}
		
		function isSubGroupMember( $person ) {
			$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleSubGroupMap WHERE iPersonID=".$person->getID()." AND iSubGroupID=".$this->getID() );
			
			return ( mysql_num_rows( $people ) != 0 );
		}

		function getSubGroupMembers() {
			$returnArray = array();
			
			$people = $this->db->igroupsQuery( "SELECT iPersonID FROM PeopleSubGroupMap WHERE iSubGroupID=".$this->getID() );
			while ( $row = mysql_fetch_row( $people ) ) {
				$tmp = new Person( $row[0], $this->db );
				$returnArray[] = $tmp;
			}
			return $returnArray;
		}

		function addMember($person) {
			if (!$this->isSubGroupMember($person))
				$this->db->igroupsQuery("INSERT INTO PeopleSubGroupMap VALUES ({$this->getID()}, {$person->getID()})");
		}

		function removeMember($person) {
			$this->db->igroupsQuery("DELETE FROM PeopleSubGroupMap WHERE iSubGroupID={$this->getID()} AND iPersonID={$person->getID()}");
		}

		function clearMembers() {
			$this->db->igroupsQuery("DELETE FROM PeopleSubGroupMap WHERE iSubGroupID={$this->getID()}");
		}

		function delete() {
			$this->db->igroupsQuery("DELETE FROM PeopleSubGroupMap WHERE iSubGroupID={$this->getID()}");
			$this->db->igroupsQuery("DELETE FROM SubGroups WHERE iID={$this->getID()}");
		}
	}
}
?>
