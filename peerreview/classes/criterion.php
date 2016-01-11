<?php
if(!class_exists('Criterion'))
{
	class Criterion
	{
		var $id, $name, $description, $listID, $db;
		
		function Criterion($id, $db)
		{
			$this->id = $id;
			$this->db = $db;
			$query = $this->db->query("select * from Criteria where id={$this->id}");
			$result = mysql_fetch_array($query);
			$this->name = stripslashes($result['name']);
			$this->description = stripslashes($result['description']);
			$this->listID = $result['listID'];
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

		function getListID()
		{
			return $this->listID;
		}

		function getList()
		{
			$list = new CriteriaList($this->listID, $this->db);
			return $list;
		}
		
		function delete()
		{
			$this->db->query("delete from Criteria where id={$this->id}");
		}
	}

	function createCriterion($name, $description, $listID, $db)
	{
		$name = mysql_real_escape_string(stripslashes($name));
		$description = mysql_real_escape_string(stripslashes($description));
		$db->query("insert into Criteria (name, description, listID) values ('$name', '$description', $listID)");
		$id = $db->insertID();
		return new Criterion($id, $db);
	}

	function getCriteria($listID, $db)
	{
		$query = $db->query("select id from Criteria where listID={$listID}");
		$criteria = array();
		while ($result = mysql_fetch_array($query))
			$criteria[] = new Criterion($result['id'], $db);
		return $criteria;
	}
}
?>
