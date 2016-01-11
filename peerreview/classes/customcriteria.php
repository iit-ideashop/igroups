<?php
include_once('group.php');
if(!class_exists('CustomCriterion'))
{
	class CustomCriterion
	{
		var $id, $name, $description, $groupID, $db;
		
		function CustomCriterion($id, $db)
		{
			$this->id = $id;
			$this->db = $db;
			$query = $this->db->query("select * from CustomCriteria where id={$this->id}");
			$result = mysql_fetch_array($query);
			$this->name = stripslashes($result['name']);
			$this->description = stripslashes($result['description']);
			$this->groupID = $result['groupID'];
		}

		function getID()
		{
			return $this->id;
		}
		
		function getName()
		{
			return $this->name;
		}
		
		function getDescription()
		{
			return $this->description;
		}

		function getGroupID()
		{
			return $this->groupID;
		}
		
		function update($nm, $desc)
		{
			$this->name = stripslashes($nm);
			$this->description = stripslashes($desc);
			$nm = mysql_real_escape_string($this->name);
			$desc = mysql_real_escape_string($this->description);
			$this->db->query("update CustomCriteria set name=\"$nm\", description=\"$desc\" where id={$this->id}");
		}
		
		function delete()
		{
			$this->db->query("delete from CustomCriteriaRatings where ccID={$this->id}");
			$this->db->query("delete from CustomCriteria where id={$this->id}");
		}
	}

	function createCustomCriterion($name, $description, $groupID, $db)
	{
		$name = mysql_real_escape_string(stripslashes($name));
		$description = mysql_real_escape_string(stripslashes($description));
		$db->query("insert into CustomCriteria (name, description, groupID) values ('$name', '$description', $groupID)");
		$id = $db->insertID();
		return new CustomCriterion($id, $db);
	}

	function getCustomCriteria($groupID, $db)
	{
		if(!is_numeric($groupID))
			return false;
		$query = $db->query("select id from CustomCriteria where groupID=$groupID");
		$criteria = array();
		while ($result = mysql_fetch_array($query))
			$criteria[$result['id']] = new CustomCriterion($result['id'], $db);
		return $criteria;
	}
}
?>
