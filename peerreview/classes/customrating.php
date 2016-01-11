<?php
if(!class_exists('CustomRating'))
{
	class CustomRating
	{
		var $complete, $rating, $raterID, $ratedID, $ccID, $db;
		
		function CustomRating($raterID, $ratedID, $ccID, $db)
		{
			if(!is_numeric($raterID) || !is_numeric($ratedID) || !is_numeric($ccID))
			{
				return;
			}
			$this->raterID = $raterID;
			$this->ratedID = $ratedID;
			$this->ccID = $ccID;
			$this->db = $db;
			$result = $this->db->query("select * from CustomCriteriaRatings where raterID=$raterID and ratedID=$ratedID and ccID=$ccID");
			if($row = mysql_fetch_array($result))
			{
				$this->rating = $row['rating'];
				$this->complete = true;
			}
			else
			{
				$this->rating = 0;
				$this->complete = false;
			}
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
		
		function getCCID()
		{
			return $this->ccID;
		}
		
		function getCC()
		{
			return new CustomCriterion($this->ccID, $this->db);
		}

		function getType()
		{
			return ($this->ratedID == $this->raterID) ? 'self' : 'peer';
		}

		function setRating($rating)
		{
			$this->rating = $rating;
			if($this->complete)
				$this->db->query("update CustomCriteriaRatings set rating=$rating where raterID={$this->raterID} and ratedID={$this->ratedID} and ccID={$this->ccID}");
			else
			{
				$this->complete = true;
				$this->db->query("insert into CustomCriteriaRatings (raterID, ratedID, ccID, rating) values ({$this->raterID}, {$this->ratedID}, {$this->ccID}, $rating)");
			}
		}

		function getRating()
		{
			return $this->rating;
		}
		
		function isComplete()
		{
			return $this->complete;
		}

		function sendToArchive($rID)
		{
			$rater = new Person($this->raterID, $this->db);
			$rated = new Person($this->ratedID, $this->db);
			$this->db->query("insert into CustomCriteriaArchive (raterID, ratedID, runningID, rating, ccID) values ({$rater->getID()}, {$rated->getID()}, $rID, {$this->rating}, {$this->getCCID()})");
		}
	}
}
?>
