<?php
include_once('post.php');
include_once('person.php');

if(!class_exists('Thread'))
{
	class Thread
	{
		var $id, $views, $name, $author, $parentTopic, $next, $prev, $valid;
		var $db;
		
		function Thread($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			$query = $db->query("SELECT * FROM Threads WHERE iID={$this->id}");
			$result = mysql_fetch_array($query);
			if($result)
			{
				$this->views = $result['iViews'];
				$this->name = addslashes($result['sName']);
				$this->author = $result['iAuthorID'];
				$this->parentTopic = $result['iTopicID'];
				$this->next = $result['iNextID'];
				$this->prev = $result['iPrevID'];
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
			return htmlspecialchars(stripslashes($this->name));
		}

		function getAuthor()
		{
			$person = new Person($this->author, $this->db);
			return $person;
		}

		function getAuthorID()
		{
			return $this->author;
		}

		function getAuthorName()
		{
			$person = new Person($this->author, $this->db);
			return $person->getFullName();
		}

		function getAuthorLink()
		{
			$person = $this->getAuthor();
			return "<a href='../viewprofile.php?uID={$this->author}'>{$person->getFullName()}</a>";
		}

		function getLastPost()
		{
			$posts = $this->getPosts();
			return $posts[count($posts)-1];
		}

		function getParentTopic()
		{
			$topic = new Topic($this->parentTopic, $this->db);
			return $topic;
		}

		function getTopicID()
		{
			return $this->parentTopic;
		}

		function getGroupID()
		{
			return $this->parentTopic;
		}

		function getNext()
		{
			if($this->next != 0)
			{
				$next = new Thread($this->next, $this->db);
				return $next;
			}
			else
				return null;
		}

		function getPrev()
		{
			if($this->prev != 0)
			{
				$prev = new Thread($this->prev, $this->db);
				return $prev;
			}
			else
				return null;
		}

		function getViews()
		{
			return $this->views;
		}

		function setViews($views)
		{
			if(is_numeric($views))
				$this->views = $views;
		}
		
		function incViews()
		{
			$this->views++;
		}

		function getPosts()
		{
			$posts = array();
			$query = $this->db->query("SELECT iID FROM Posts WHERE iThreadID={$this->id} ORDER BY dDateTime");
			while ($row = mysql_fetch_row($query))	
				$posts[] = new Post($row[0], $this->db);
			return $posts;
		}		
	
		function getPostCount()
		{
			$result = mysql_fetch_row($this->db->query("SELECT COUNT(*) FROM Posts WHERE iThreadID={$this->id}"));
			if(!$result[0])
				return 0;
			else
				return $result[0];
		}

		function delete()
		{
			$this->db->query("DELETE FROM Posts WHERE iThreadID={$this->id}");
			$this->db->query("UPDATE Threads SET iNextID={$this->next} WHERE iID={$this->prev}");
			$this->db->query("UPDATE Threads SET iPrevID={$this->prev} WHERE iID={$this->next}");
			$this->db->query("DELETE FROM Threads WHERE iID={$this->id}");
		}

		function setNext($next)
		{
			if(is_numeric($next))
				$this->next = $next;
		}
		
		function setPrev($prev)
		{
			if(is_numeric($prev))
				$this->prev = $prev;
		}

		function setName($name)
		{
			$this->name = $name;
		}

		function setAuthor($authorID)
		{
			if(is_numeric($authorID))
				$this->author = $authorID;
		}

		function setTopic($topicID)
		{
			$this->parentTopic = $topicID;
		}
		
		function updateDB()
		{
			$nname = mysql_real_escape_string(stripslashes($this->name));
			$this->db->query("UPDATE Threads SET iViews={$this->views}, sName=\"$nname\", iAuthorID={$this->author}, iNextID={$this->next}, iPrevID={$this->prev}, iTopicID={$this->parentTopic} WHERE iID={$this->id}");
		}
		
	}

	function createThread($name, $authorID, $topicID, $db)
	{
		if(!is_numeric($topicID))
			return
		$query = $db->query("SELECT iID FROM Threads WHERE iTopicID={$topicID} ORDER BY iID DESC");
		if($query && mysql_num_rows($query))
		{
			$last = mysql_fetch_row($query);
			if($last)
				$lastThread = new Thread($last[0], $db);
		}
		$db->query("INSERT INTO Threads (sName) VALUES ('New Thread')");
		$thread = new Thread($db->insertID(), $db);
		$thread->setName($name);
		$thread->setAuthor($authorID);
		$thread->setTopic($topicID);
		$thread->setNext(0);
		if($last)
			$thread->setPrev($last[0]);
		else
			$thread->setPrev(0);
		$thread->setViews(0);
		if(isset($lastThread))
		{
			$lastThread->setNext($thread->getID());
			$lastThread->updateDB();
		}
		$thread->updateDB();
		return $thread;
	}
}
?>
