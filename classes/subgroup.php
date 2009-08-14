<?php
if(!class_exists('SubGroup'))
{
	class SubGroup
	{
		var $id, $groupID, $name, $valid;
		var $db;
		
		function SubGroup($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			$this->semester = $semester;
			$result = $this->db->query("SELECT * FROM SubGroups WHERE iID=$id");
			if($row = mysql_fetch_array($result))
			{
				$this->name = $row['sName'];
				$this->groupID = $row['iGroupID'];
				$this->valid = true;
			}
		}
		
		function isValid()
		{
			return $this->valid;
		}

		function getName()
		{
			return $this->name;
		}
			
		function getID()
		{
			return $this->id;
		}
		
		function getGroupID()
		{
			return $this->groupID;
		}
		
		function isSubGroupMember($person)
		{
			$people = $this->db->query("SELECT iPersonID FROM PeopleSubGroupMap WHERE iPersonID=".$person->getID()." AND iSubGroupID=".$this->getID());
			return (mysql_num_rows($people) != 0);
		}

		function getSubGroupMembers()
		{
			$returnArray = array();
			
			$people = $this->db->query("SELECT iPersonID FROM PeopleSubGroupMap WHERE iSubGroupID=".$this->getID());
			while($row = mysql_fetch_row($people))
			{
				$tmp = new Person($row[0], $this->db);
				$returnArray[] = $tmp;
			}
			return $returnArray;
		}

		function addMember($person)
		{
			if(!$this->isSubGroupMember($person))
				$this->db->query("INSERT INTO PeopleSubGroupMap VALUES ({$this->getID()}, {$person->getID()})");
		}

		function removeMember($person)
		{
			$this->db->query("DELETE FROM PeopleSubGroupMap WHERE iSubGroupID={$this->getID()} AND iPersonID={$person->getID()}");
		}

		function clearMembers()
		{
			$this->db->query("DELETE FROM PeopleSubGroupMap WHERE iSubGroupID={$this->getID()}");
		}

		function delete()
		{
			$this->db->query("DELETE FROM PeopleSubGroupMap WHERE iSubGroupID={$this->getID()}");
			$this->db->query("DELETE FROM SubGroups WHERE iID={$this->getID()}");
		}
	}
}
?>
