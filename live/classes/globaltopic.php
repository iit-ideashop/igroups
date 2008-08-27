<?php

require_once('topic.php');

if ( !class_exists( "GlobalTopic" ) ) {
	class GlobalTopic extends Topic {
		
		var $id, $db, $name, $desc;

		function GlobalTopic( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			$query = $db->igroupsQuery("SELECT * FROM GlobalTopics WHERE iID={$this->id}");
			$result = mysql_fetch_array($query);
			$this->name = $result['sName'];
			$this->desc = $result['sDesc'];
		}
		
		function getID() {
			return $this->id;
		}

		function getName() {
			return $this->name;
		}
		
		function getDesc() {
			return $this->desc;
		}

		function setName($name) {
			$this->name = $name;
		}

		function setDesc($desc) {
			$this->desc = $desc;
		}

		function updateDB() {
			$this->db->igroupsQuery("UPDATE GlobalTopics SET sName='{$this->name}', sDesc='{$this->desc}' WHERE iID={$this->id}");
		}
	
		function delete() {
			$this->db->igroupsQuery("DELETE FROM GlobalTopics WHERE iID={$this->id}");
			$this->db->igroupsQuery("DELETE FROM Threads WHERE iTopicID={$this->id}");
		}
	}
	
	function createGlobalTopic($name, $desc, $db) {
		$db->igroupsQuery("INSERT INTO GlobalTopics (sName) VALUES ('New Topic')");
		$topic = new GlobalTopic($db->igroupsInsertID(), $db);
		$topic->setName($name);
		$topic->setDesc($desc);
		$topic->updateDB();
		return $topic;
	}

	function getGlobalTopics($db) {
		$topics = array();
		$query = $db->igroupsQuery("SELECT iID FROM GlobalTopics");
		while ($result = mysql_fetch_row($query))
			$topics[] = new GlobalTopic($result[0], $db);
		return $topics;
	}

}
?>
