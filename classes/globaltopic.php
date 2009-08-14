<?php
require_once('topic.php');

if(!class_exists('GlobalTopic'))
{
	class GlobalTopic extends Topic
	{
		var $id, $db, $name, $desc, $valid;

		function GlobalTopic($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			$query = $db->query("SELECT * FROM GlobalTopics WHERE iID={$this->id}");
			$result = mysql_fetch_array($query);
			if($result)
			{
				$this->name = $result['sName'];
				$this->desc = $result['sDesc'];
				$this->valid = true;	
			}
		}
		
		function isValid()
		{
			return $this->valid;
		}
		
		function getID()
		{
			return $this->id;
		}

		function getName()
		{
			return $this->name;
		}
		
		function getDesc()
		{
			return $this->desc;
		}

		function setName($name)
		{
			$this->name = $name;
		}

		function setDesc($desc)
		{
			$this->desc = $desc;
		}

		function updateDB()
		{
			$this->db->query("UPDATE GlobalTopics SET sName='{$this->name}', sDesc='{$this->desc}' WHERE iID={$this->id}");
		}
	
		function delete()
		{
			$this->db->query("DELETE FROM GlobalTopics WHERE iID={$this->id}");
			$this->db->query("DELETE FROM Threads WHERE iTopicID={$this->id}");
		}
	}
	
	function createGlobalTopic($name, $desc, $db)
	{
		$db->query("INSERT INTO GlobalTopics (sName) VALUES ('New Topic')");
		$topic = new GlobalTopic($db->insertID(), $db);
		$topic->setName($name);
		$topic->setDesc($desc);
		$topic->updateDB();
		return $topic;
	}

	function getGlobalTopics($db)
	{
		$topics = array();
		$query = $db->query("SELECT iID FROM GlobalTopics");
		while($result = mysql_fetch_row($query))
			$topics[] = new GlobalTopic($result[0], $db);
		return $topics;
	}

}
?>
