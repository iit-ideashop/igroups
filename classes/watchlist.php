<?php
include_once('thread.php');
include_once('person.php');
include_once('post.php');

if(!class_exists('WatchList'))
{
	class WatchList
	{
		var $threadID, $thread, $watchers;
		var $db;
		
		function WatchList($threadID, $db)
		{
			if(!is_numeric($threadID))
				return;
			$this->threadID = $threadID;
			$this->db = $db;
			$this->watchers = array();
			$query = $this->db->query("SELECT * FROM ThreadWatchList WHERE threadID={$this->threadID}");
			while($row = mysql_fetch_row($query))
				$this->watchers[] = new Person($row[1], $this->db);
			$this->thread = new Thread ($this->threadID, $this->db);
		}
	
		function refresh()
		{
			$this->watchers = array();
			$query = $this->db->query("SELECT * FROM ThreadWatchList WHERE threadID={$this->threadID}");
			while($row = mysql_fetch_row($query))
				$this->watchers[] = new Person($row[1], $this->db);
		}
	
		function getWatchList()
		{
			return $this->watchers;
		}

		function isOnWatchList($user)
		{
			foreach($this->watchers as $watcher)
			{
				if($watcher->getID() == $user->getID())
					return true;
			}
			return false;
		}

		function addToWatchList($user)
		{
			if(!$this->isOnWatchList($user))
			{
				$this->db->query("INSERT INTO ThreadWatchList VALUES ({$this->threadID}, {$user->getID()})");
				$this->refresh();
			}
		}

		function removeFromWatchList($user)
		{
			$this->db->query("DELETE FROM ThreadWatchList WHERE threadID={$this->threadID} AND userID={$user->getID()}");
			$this->refresh();
		}

		function sendNotification($post, $topicName)
		{
			foreach ($this->watchers as $watcher)
			{
				$threadName = $this->thread->getName();
				$postBody = $post->getBody();
				$postDate = $post->getDateTime();
				$postAuthor = $post->getAuthorName();

				$headers = "From: $appname <$contactemail>\n";
				$headers .= 'MIME-Version: 1.0'."\n";
				$headers .= 'Content-Type: text/plain; charset=iso-8859-1'."\n";
				
				$subject = "Reply: {$threadName}";

				$body .= "Reply made to: $topicName -> $threadName\n";
				$body .= "Posted on: $postDate\n";
				$body .= "By: $postAuthor\n\n";
				$body .= "{$postBody}\n\n";
				$body .= "----------------------\n\n";
				$body .= "You are receiving this e-mail because you have chosen to watch the thread \"{$threadName}\" in the $appname Discussion Board. If you no longer want to receive these e-mails, you can choose to unwatch the thread by clicking on \"Unwatch Thread\" after logging in and navigating to the thread in the $appname Discussion Board.";

				mail($watcher->getEmail(), $subject, $body, $headers);
			}
		}
	}
}
?>
