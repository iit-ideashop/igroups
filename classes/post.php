<?php

require_once('thread.php');
require_once('person.php');

if ( !class_exists( "Post" ) ) {
	class Post {
		var $id, $datetime, $author, $body, $threadID;
		var $db;
		
		function Post( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			$result = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Posts WHERE iID={$this->id}"));
			$this->datetime = $result['dDateTime'];
			$this->author = $result['iAuthorID'];
			$this->body = addslashes($result['sBody']);
			$this->threadID = $result['iThreadID'];
		}
		
		function getID() {
			return $this->id;
		}
		
		function getDateTime() {
			list($year, $month, $day_time) = explode('-', $this->datetime);
                        list($day, $time) = explode(" ", $day_time);
                        list($hour, $minute, $second) = explode(":", $time);
                        $ts = mktime($hour, $minute, $second, $month, $day, $year);
                        $date = date("D M d, Y g:i a", $ts);
			return $date;
		}
	
		function getBody() {
			$body = htmlspecialchars(stripslashes($this->body));
			return $body;
		}

		function getAuthor() {
			$person = new Person($this->author, $this->db);
			return $person;
		}
	
		function getAuthorName() {
			$person = $this->getAuthor();
			return $person->getFullName();
		}

		function getAuthorID() {
			return $this->author;
		}

		function getAuthorLink() {
			$person = $this->getAuthor();
			return "<a href=\"../viewprofile.php?uID={$this->author}\">{$person->getFullName()}</a>";
		}

		function getThreadID() {
			return $this->threadID;
		}

		function setBody($body) {
			$this->body = $body;
		}

		function setAuthor($authorID) {
			$this->author = $authorID;
		}

		function updateDB() {
			$this->db->igroupsQuery("UPDATE Posts SET iThreadID={$this->threadID}, sBody=\"{$this->body}\", iAuthorID={$this->author} WHERE iID={$this->id}");
		}
	
		function delete() {
			$this->db->igroupsQuery("DELETE FROM Posts WHERE iID={$this->id}");
		}
	}

	function createPost($threadID, $body, $author, $db) {
		$db->igroupsQuery("INSERT INTO Posts (iThreadID, dDateTime) VALUES ($threadID, now())");
		$post = new Post($db->igroupsInsertID(), $db);
		$post->setBody($body);
		$post->setAuthor($author);
		$post->updateDB();
		return $post;
	}
}
?>
