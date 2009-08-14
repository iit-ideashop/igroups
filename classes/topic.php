<?php
include_once('thread.php');

if(!class_exists('Topic'))
{
	class Topic
	{
		var $id;
		var $db;
		
		function Topic($id, $db)
		{
			$this->id = (is_numeric($id) ? $id : 0);
			$this->db = $db;
		}

		function getID()
		{
			return $this->id;
		}
		
		function getThreadCount()
		{
			$result = mysql_fetch_row($this->db->query("SELECT COUNT(*) FROM Threads WHERE iTopicID={$this->id}"));
			return $result[0];
		}

		function getPostCount()
		{
			$result = mysql_fetch_row($this->db->query("select count(*) from Threads t, Posts p where t.iTopicID={$this->id} and t.iID = p.iThreadID"));
			return $result[0];
		}

		function getThreads()
		{
			$threads = array();
			$query = $this->db->query("select t.iID, m.d from Threads t, (select max(dDateTime) as d, iThreadID from Posts group by iThreadID order by max(dDateTime) desc) m where t.iID = m.iThreadID and t.iTopicID={$this->id} order by m.d desc");
			while($row = mysql_fetch_row($query))
				$threads[] = new Thread($row[0], $this->db);
			return $threads;
		}

		function getLastPost()
		{
			if($result = mysql_fetch_row($this->db->query("SELECT p.iID FROM Posts p, Threads t WHERE t.iTopicID={$this->id} AND t.iID = p.iThreadID ORDER BY p.dDateTime DESC")))
			{
				$post = new Post($result[0], $this->db);
				return $post;
			}
			else
				return null;
		}
	}
}
?>
