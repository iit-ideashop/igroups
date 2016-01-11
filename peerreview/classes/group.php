<?php
include_once('person.php');
include_once('customcriteria.php');
include_once('customrating.php');
if(!class_exists('Group'))
{
	class Group
	{
		var $id, $name, $listID;
		var $db;
		
		function Group($id, $db)
		{
			$this->id = $id;
			$this->db = $db;
			$result = $this->db->query("select listID, name from Groups where ID=$id");
			if($row = mysql_fetch_row($result))
			{
				$this->name = stripslashes($row[1]);
				$this->listID = $row[0];
			}
		}

		function getName()
		{
			return $this->name;
		}
			
		function getID()
		{
			return $this->id;
		}

		function getListID()
		{
			return $this->listID;
		}

		function getList()
		{
			return new CriteriaList($this->listID, $this->db);
		}
		
		function isGroupMember($person)
		{
			$people = $this->db->query("select userID from PeopleGroupMap where userID=".$person->getID()." and groupID=".$this->getID());
			
			return (mysql_num_rows($people) != 0);
		}
		
		function getCustomCriteria()
		{
			$query = $this->db->query("select id from CustomCriteria where groupID={$this->id}");
			$criteria = array();
			while ($result = mysql_fetch_array($query))
				$criteria[] = new CustomCriterion($result['id'], $this->db);
			return $criteria;
		}
	
		function getGroupMembers()
		{
			$returnArray = array();
			
			$people = $this->db->query("select userID from PeopleGroupMap where groupID=".$this->getID());
		
			while($row = mysql_fetch_row($people))
				$returnArray[] = new Person($row[0], $this->db);
			
			return $returnArray;
		}

		function getGroupStudents()
		{
			$returnArray = array();

			$people = $this->db->query('select userID from PeopleGroupMap m, People p where m.userID=p.id and p.userType=0 and m.groupID='.$this->getID().' order by lName, fName');
			while ($row = mysql_fetch_row($people))
				$returnArray[] = new Person($row[0], $this->db);
			return $returnArray;
		}
		
		function getGroupFaculty()
		{
			$returnArray = array();

			$people = $this->db->query('select userID from PeopleGroupMap m, People p where m.userID=p.id and p.userType=1 and m.groupID='.$this->getID().' order by lName, fName');
			while ($row = mysql_fetch_row($people))
				$returnArray[] = new Person($row[0], $this->db);
			return $returnArray;
		}

		function delete()
		{
			$members = $this->getGroupMembers();
			foreach($members as $member)
				$member->removeFromGroup($this->id);
			$customcriteria = getCustomCriteria($this->id, $this->db);
			foreach($customcriteria as $cc)
				$cc->delete();
			$this->db->query("delete from Groups where id={$this->id}");
		}

		// Backs-up Raw Data
		function sendToArchive()
		{
			$query = $this->db->query("select MAX(runningID) from RatingsArchive where groupID={$this->id}");
			$res = mysql_fetch_row($query);
			if($res[0])
				$rID = intval($res[0]) + 1;
			else
				$rID = 1;

			$query = $this->db->query("select id from Ratings where groupID={$this->getID()} and isComplete=1");
			while($row = mysql_fetch_row($query))
			{
				$rating = new Rating($row[0], $this->db);
				$rating->sendToArchive($rID);
			}
			$query = $this->db->query("select raterID, ratedID, ccID from CustomCriteriaRatings where ccID in (select id from CustomCriteria where groupID={$this->getID()})");
			while($row = mysql_fetch_row($query))
			{
				$rating = new CustomRating($row[0], $row[1], $row[2], $this->db);
				$rating->sendToArchive($rID);
			}
		}

		//Statistics Gathering Functions follow
		function getGroupAvgByQ($q)
		{
			$query = $this->db->query("select rating from Ratings where groupID={$this->getID()} and isComplete=1");
			$num = mysql_num_rows($query);
			while ($result = mysql_fetch_row($query))
				$total += substr($result[0], $q-1, 1);
			$avg = $num > 0 ? ($total / $num) : 0;
			return round($avg,1);
		}
		
		function getGroupAvgByCCID($ccID)
		{
			if(!is_numeric($ccID))
				return false;
			$query = $this->db->query("select rating from CustomCriteriaRatings where ccID=$ccID");
			$num = mysql_num_rows($query);
			while ($result = mysql_fetch_row($query))
				$total += $result[0];
			$avg = $num > 0 ? ($total / $num) : 0;
			return round($avg,1);
		}

		/*
		function getGroupCatAvg($cat)
		{
			$sum=0;
			if($cat == 1)
			{
				for($i=1; $i<=5; $i++)
					$sum += $this->getGroupAvgByQ("q$i");
				return round(($sum/5),1);
			}
			else if($cat == 2)
			{
				for($i=6; $i<=9; $i++)
					$sum += $this->getGroupAvgByQ("q$i");
				return round(($sum/4),1);
			}
			else if($cat == 3)
			{
				for($i=10; $i<=15; $i++)
					$sum += $this->getGroupAvgByQ("q$i");
				return round(($sum/6),1);
			}
			else if($cat == 4)
			{
				for($i=16; $i<=20; $i++)
					$sum += $sum + $this->getGroupAvgByQ("q$i");
				return round(($sum/5),1);
			}
			else
				return 0;
		}*/

		function getGroupMaxByQ($q)
		{
			$query = $this->db->query("select rating from Ratings where groupID={$this->getID()} and isComplete=1");
			$max = 0;
			while($row = mysql_fetch_row($query))
			{
				if($max < (int)substr($row[0], $q-1, 1))
					$max = (int)substr($row[0], $q-1, 1);
			}
			return $max;
		}

		function getGroupMinByQ($q)
		{
			$query = $this->db->query("select rating from Ratings where groupID={$this->getID()} and isComplete=1");
			$min = $this->getGroupMaxByQ($q);
			while($row = mysql_fetch_row($query))
			{
				if($min > (int)substr($row[0], $q-1, 1))
					$min = (int)substr($row[0], $q-1, 1);
			}
			return $min;
		}
		
		function getGroupMaxByCCID($ccID)
		{
			if(!is_numeric($ccID))
				return false;
			$query = $this->db->query("select max(rating) from CustomCriteriaRatings where ccID=$ccID");
			$num = mysql_num_rows($query);
			if ($result = mysql_fetch_row($query))
				return $result[0];
			else
				return false;
		}
		
		function getGroupMinByCCID($ccID)
		{
			if(!is_numeric($ccID))
				return false;
			$query = $this->db->query("select min(rating) from CustomCriteriaRatings where ccID=$ccID");
			$num = mysql_num_rows($query);
			if ($result = mysql_fetch_row($query))
				return $result[0];
			else
				return false;
		}

		function getAvgOverall()
		{
			$sum = 0;
			$list = $this->getList();
			$num = $list->getNumCriteria();
			for($i=1; $i<=$num; $i++)
				$sum = $sum + $this->getGroupAvgByQ($i);
			
			if($num != 0)
				return round($sum,1);
			else
				return 'N/A';
		}
		
		function reset()
		{
			$lid = $this->getListID();
			$delMembers = $this->getGroupStudents();
			$this->db->query("delete from Ratings where groupID={$this->getID()}");
			foreach($delMembers as $user)
			{
				foreach($delMembers as $otherUser)
					$this->db->query("insert into Ratings (raterID, ratedID, groupID, isComplete, listID) values ({$user->getID()}, {$otherUser->getID()}, {$this->getID()}, 0, $lid)");
			}
		}
		
		function fillHoles()
		{
			$lid = $this->getListID();
			$members = $this->getGroupStudents();
			foreach($members as $user)
			{
				foreach($members as $otherUser)
				{
					$q = $this->db->query("select * from Ratings where raterID={$user->getID()} and ratedID={$otherUser->getID()} and groupID={$this->getID()}");
					if(!mysql_num_rows($q))
						$this->db->query("insert into Ratings (raterID, ratedID, groupID, isComplete, listID) values ({$user->getID()}, {$otherUser->getID()}, {$this->getID()}, 0, $lid)");
				}
			}
		}
		
		function addUser($user)
		{
			$uid = $user->getID();
			$gid = $this->getID();
			$lid = $this->getListID();
			$q = $this->db->query("insert into PeopleGroupMap (userID, groupID) values ($uid, $gid)");
			if(!$q)
				return false;
			$members = $this->getGroupStudents();
			if($user->isStudent())
			{
				foreach($members as $member)
				{
					$mid = $member->getID();
					$q = $this->db->query("select * from Ratings where raterID=$uid and ratedID=$mid and groupID=$gid");
					$a = mysql_num_rows($q) ? true : $this->db->query("insert into Ratings (raterID, ratedID, groupID, isComplete, listID) values ($uid, $mid, $gid, 0, $lid)");
					$q = $this->db->query("select * from Ratings where raterID=$mid and ratedID=$uid and groupID=$gid");
					$b = mysql_num_rows($q) ? true : $this->db->query("insert into Ratings (raterID, ratedID, groupID, isComplete, listID) values ($mid, $uid, $gid, 0, $lid)");
					if(!$a || !$b)
					{
						$this->db->query("delete from Ratings where raterID=$uid and ratedID=$mid and groupID=$gid");
						$this->db->query("delete from Ratings where raterID=$mid and ratedID=$uid and groupID=$gid");
						$this->db->query("delete from PeopleGroupMap (userID, groupID) values ($uid, $gid)");
						return false;
					}
				}
			}
			return true;
		}
	}

	function createGroup($name, $listID, $db)
	{
		if(!is_numeric($listID))
			return false;
		$name = mysql_real_escape_string(stripslashes($name));
		$query = $db->query("insert into Groups (name, listID) values ('$name', $listID)");
		$id = $db->insertID();
		return new Group($id, $db);
	}
}
?>
