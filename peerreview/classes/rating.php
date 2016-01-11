<?php
if(!class_exists('Rating'))
{
	class Rating
	{
		var $id, $rating, $raterID, $ratedID, $groupID, $listID, $isComplete, $rank, $comment, $db;
		
		function Rating($id, $db)
		{
			$this->id = $id;
			$this->db = $db;
			$result = $this->db->query("select * from Ratings where id=$id");
			if($row = mysql_fetch_array($result))
			{
				$this->raterID = $row['raterID'];
				$this->ratedID = $row['ratedID'];
				$this->groupID = $row['groupID'];
				$this->listID = $row['listID'];
				$this->isComplete = $row['isComplete'];
				$this->rating = $row['rating'];
				$this->rank = $row['rank'];
				$this->comment = stripslashes($row['comment']);
			}
			else
				$this->id = -1;
		}
		
		function ArchivedRating($id, $db)
		{
			$this->id = $id;
			$this->db = $db;
			$result = $this->db->query("select * from RatingsArchive where id=$id");
			if($row = mysql_fetch_array($result))
			{
				$this->raterID = $row['raterID'];
				$this->ratedID = $row['ratedID'];
				$this->groupID = $row['groupID'];
				$this->listID = $row['listID'];
				$this->isComplete = $row['isComplete'];
				$this->rating = $row['rating'];
				$this->rank = $row['rank'];
				$this->comment = stripslashes($row['comment']);
			}
			else
				$this->id = -1;
		}

		function getID()
		{
			return $this->id;
		}

		function getRatedID()
		{
			return $this->ratedID;
		}

		function getRatedName()
		{
			$person = new Person($this->ratedID, $this->db);
			return $person->getFullName();
		}

		function getRaterID()
		{
			return $this->raterID;
		}
	
		function getRaterName()
		{
			$person = new Person($this->raterID, $this->db);
			return $person->getFullName();
		}

		function getRank()
		{
			return $this->rank;
		}

		function getGroupID()
		{
			return $this->groupID;
		}

		function getGroupName()
		{
			$group = new Group($this->groupID, $this->db);
			return $group->getName();
		}

		function getComment()
		{
			return $this->comment;
		}	

		function getListID()
		{
			return $this->listID;
		}
		
		function getList()
		{
			return new CriteriaList($this->listID, $this->db);
		}

		function getOverall()
		{
			$length = strlen($this->rating);
			for($i = 0; $i < $length; $i++)
				if($this->rating[$i] != '!')
					$sum += (int)($this->rating[$i]);
			return $sum;
		}

		function getType()
		{
			return ($this->ratedID == $this->raterID) ? 'self' : 'peer';
		}

		function getScoreByQ($q)
		{
			return (int)substr($this->rating, $i-1);
		}

		function isComplete()
		{
			return $this->isComplete;
		}

		function setRating($rating)
		{
			$group = new Group($this->groupID, $this->db);
			$num = $group->getList()->getNumCriteria();
			if(strlen($rating) == $num)
				$this->rating = $rating;
			return;
		}

		function getRating()
		{
			return $this->rating;
		}

		function getQuestion($q)
		{
			return substr($this->rating, $q-1, 1);
		}

		function sendToArchive($rID)
		{
			$rater = new Person($this->raterID, $this->db);
			$rated = new Person($this->ratedID, $this->db);
			$group = new Group($this->groupID, $this->db);
			$gname = mysql_real_escape_string($group->getName());
			$query = $this->db->query("select * from RatingsArchive where id={$this->id}");
			if($res = mysql_fetch_array($query))
				$query = $this->db->query("delete from RatingsArchive where id={$this->id}");
			$text = mysql_real_escape_string($this->comment);
			$this->db->query("insert into RatingsArchive (id, raterID, ratedID, groupID, groupName, runningID, rating, comment, rank, listID) values ({$this->id}, {$rater->getID()}, {$rated->getID()}, {$group->getID()}, '$gname', $rID, '{$this->rating}', \"$text\", {$this->rank}, {$group->getListID()})");
		}
	}
}
?>
