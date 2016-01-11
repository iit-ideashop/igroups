<?php
require_once('criterion.php');
if(!class_exists('CriteriaList'))
{
	class CriteriaList
	{
		var $id, $name, $isActive, $db;
		var $criteria = array();		

		function CriteriaList($id, $db)
		{
			$this->id = $id;
			$this->db = $db;
			$query = $this->db->query("SELECT * FROM CriteriaList WHERE id=".$this->id);
			$result = mysql_fetch_array($query);
			$this->name = stripslashes($result['name']);
			$this->isActive = $result['isActive'];
			$query = $this->db->query("select * from Criteria where listID=".$this->id." order by id");
			while($result = mysql_fetch_array($query))
				$this->criteria[] = new Criterion($result['id'], $this->db);
		}

		function getID()
		{
			return $this->id;
		}
		
		function getName()
		{
			return $this->name;
		}
		
		function getCriteria()
		{
			return $this->criteria;
		}

		function getNumCriteria()
		{
			return count($this->criteria);
		}		

		function isActive()
		{
			return $this->isActive();
		}

		function makeInactive()
		{
			$this->isActive = 0;
			$this->db->query("update CriteriaList set isActive=0 where id={$this->id}");
		}

		function delete()
		{
			$query = $this->db->query("select id from Groups where listID={$this->id}");
			if(mysql_num_rows($query) != 0)
				return;
			else
			{
				$this->db->query("delete from Criteria where listID={$this->id}");
				$this->db->query("delete from CriteriaList where id={$this->id}");
			}
		}

	}

	function createCriteriaList($name, $db)
	{
		$name = mysql_real_escape_string(stripslashes($name));
		$db->query("insert into CriteriaList (name) values ('$name')");
		$id = $db->insertID();
		return new CriteriaList($id, $db);
	}

	function getCriteriaLists($db)
	{
		$query = $db->query('select * from CriteriaList order by id');
		$lists = array();
		while($result = mysql_fetch_array($query))
			$lists[] = new CriteriaList($result['id'], $db);
		return $lists;
	}
}
?>
