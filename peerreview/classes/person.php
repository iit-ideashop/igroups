<?php
if(!class_exists('Person'))
{
	class Person
	{
		var $id, $firstname, $lastname, $email, $password, $usertype;
		var $db;
		
		function Person($id, $db)
		{
			if(is_numeric($id) && is_object($db))
			{
				if($temp = mysql_fetch_array($db->query("select * from People where id=$id")))
				{
					$this->id = $id;
					$this->firstname = stripslashes($temp['fName']);
					$this->lastname = stripslashes($temp['lName']);
					$this->email = stripslashes($temp['email']);
					$this->usertype = $temp['userType'];
					$this->password = $temp['password'];
					$this->db = $db;
				}
			}
		}
		
		function getID()
		{
			return $this->id;
		}
		
		function getUserType()
		{
			return $this->usertype;
		}

		function setUserType($type)
		{
			if(is_numeric($type) && $type <= 2 && $type >= 0)
				$this->usertype = $type;
			$this->db->query("update People set userType=$type where id={$this->id}");
		}

		function getFirstName()
		{
			return $this->firstname;
		}
		
		function setFirstName($name)
		{
			if($name != '')
				$this->firstname = $name;
		}
		
		function getLastName()
		{
			return $this->lastname;
		}
		
		function setLastName($name)
		{
			if($name != '')
				$this->lastname = $name;
		}
		
		function getFullName()
		{
			return ($this->firstname.' '.$this->lastname);
		}
		
		function getCommaName()
		{
			return ($this->lastname.', '.$this->firstname);
		}
		
		function getEmail()
		{
			return $this->email;
		}
		
		function setEmail($email)
		{
			if($email != '')
				$this->email = $email;
		}

		function getPassword()
		{
			return $this->password;
		}
	
		function setPassword($pwd)
		{
			if ($pwd != '')
			{
				$this->password = md5($pwd);
				$this->updateDB();
			}
		}

		function getGroups()
		{
			$query = $this->db->query("select * from PeopleGroupMap where userID={$this->id}");
			$groups = array();
			while($row = mysql_fetch_row($query))
				$groups[] = new Group($row[1], $this->db);
			return $groups;
		}				
		
		function removeFromGroup($groupID)
		{
			if(!is_numeric($groupID))
				return false;
			$this->db->query("delete from Ratings where groupID=$groupID and (raterID={$this->id} or ratedID={$this->id})");
			$this->db->query("delete from CustomCriteriaRatings where (raterID={$this->id} or ratedID={$this->id}) and ccID in (select id from CustomCriteria where groupID=$groupID)");
			$this->db->query("delete from PeopleGroupMap where userID={$this->id} and groupID=$groupID");
			$query = $this->db->query("select * from PeopleGroupMap where userID={$this->id}");
			return true;
		}

		function addToGroup($groupID)
		{
			$group = new Group($groupID, $this->db);
			if(!$this->isFaculty())
			{
				$this->db->query("insert into Ratings (raterID, ratedID, groupID, listID) values ({$this->id}, {$this->id}, $groupID, {$group->getListID()})");
				$members = $group->getGroupStudents();
				foreach($members as $member)
				{
					$this->db->query("insert into Ratings (raterID, ratedID, groupID, listID) values ({$this->id}, {$member->getID()}, $groupID, {$group->getListID()})");
					$this->db->query("insert into Ratings (raterID, ratedID, groupID, listID) values ({$member->getID()}, {$this->id}, $groupID, {$group->getListID()})");
				}
			}
			$this->db->query("insert into PeopleGroupMap (userID, groupID) values ({$this->id}, $groupID)");
		}
		
		function getRatings()
		{
			$query = $this->db->query("select id from Ratings where raterID={$this->id}");
			$ratings = array();
			while($row = mysql_fetch_row($query))
				$ratings[] = new Rating($row[0], $this->db);
			return $ratings;
		}

		function getRatingsByGroup($groupID)
		{
			$query = $this->db->query("select id from Ratings where ratedID={$this->id} and groupID={$groupID}");
			$ratings = array();
			while ($row = mysql_fetch_row($query))
				$ratings[] = new Rating($row[0], $this->db);
			return $ratings;
		}
	

		function getNumRatings()
		{
			$query = $this->db->query("select COUNT(*) from Ratings where raterID={$this->id}");
			$row = mysql_fetch_row($query);
			return $row[0];
		}

		function getNumRatingsByGroup($groupID)
		{
			$query = $this->db->query("select COUNT(*) from Ratings where raterID={$this->id} and groupID={$groupID}");
			$row = mysql_fetch_row($query);
			return $row[0];
		}

		function getNumCompleted()
		{
			$query = $this->db->query("select COUNT(*) from Ratings where raterID={$this->id} and isComplete=1");
			$row = mysql_fetch_row($query);
			return $row[0];
		}

		function getNumCompletedByGroup($groupID)
		{
			$query = $this->db->query("select COUNT(*) from Ratings where raterID={$this->id} and groupID=$groupID and isComplete=1");
			$row = mysql_fetch_row($query);
			return $row[0];
		}

		function makeFaculty()
		{
			$this->usertype = 1;
			$this->db->query("delete from Ratings where (ratedID={$this->id} or raterID={$this->id})");
			$this->db->query('update People set userType=1 where id='.$this->id);
		}
		
		function isStudent()
		{
			return ($this->usertype == 0);
		}

		function isFaculty()
		{
			return ($this->usertype == 1);
		}		

		function isAdministrator()
		{
			return ($this->usertype == 2);
		}
		
		function updateDB()
		{
			$this->db->query("update People set fName=\"".$this->getFirstName()."\", lName=\"".$this->getLastName()."\", password='".$this->password."', userType='".$this->usertype."' where id=".$this->id);
		}

		function getAvgByQ($q, $groupID)
		{
			$query = $this->db->query("select rating from Ratings where ratedID={$this->id} and groupID=$groupID and isComplete=1");
			$num = mysql_num_rows($query);
			$sum = 0;
			
			while ($row = mysql_fetch_row($query))
				$sum += (int)(substr(strval($row[0]), $q-1, 1));
			if ($num != 0)
				return round($sum/$num,1);
			else
				return 'n/a';
		}
		
		function getAvgByCCID($ccID)
		{
			$query = $this->db->query("select avg(rating) from CustomCriteriaRatings where ratedID={$this->id} and ccID=$ccID");
			if($query)
			{
				$result = mysql_fetch_row($query);
				return $result[0];
			}
			return 0;
		}

		function getAvgRank($groupID)
		{
			$query = $this->db->query("select AVG(rank) from Ratings where ratedID={$this->id} and groupID=$groupID and isComplete=1");
			$row = mysql_fetch_row($query);
			return $row[0];
		}

		function getOverall($groupID)
		{
			$query = $this->db->query("select rating from Ratings where ratedID={$this->id} and groupID=$groupID and isComplete=1");
			$num = mysql_num_rows($query);
			while($row = mysql_fetch_row($query))
			{
				for($i=0; $i<strlen($row[0]); $i++)
					$sum += (int)substr($row[0], $i, 1);
			}

			if ($num != 0)
				return round($sum/$num,1);
			else
				return 'N/A';
		}

		function delete()
		{
			$this->db->query("delete from Ratings where (ratedID={$this->id} or raterID={$this->id})");
			$this->db->query("delete from PeopleGroupMap where userID={$this->id}");
			$this->db->query("delete from People where id={$this->id}");
		}
	}
	
	function createPerson($email, $fname, $lname, $type, $db)
	{
		if(!is_numeric($type) || $type < 0 || $type > 2)
			return false;
		$email = mysql_real_escape_string(stripslashes($email));
		$fname = mysql_real_escape_string(stripslashes($fname));
		$lname = mysql_real_escape_string(stripslashes($lname));
		$query = $db->query("select id from People where email='$email'");
		if($query && $result = mysql_fetch_row($query))
		{
			$per = new Person($result[0], $db);
			if($type > $per->getUserType())
				$per->setUserType($type);
			return $per;
		}
		$query = $db->igroupsQuery("select sPassword from People where sEmail='$email'");
		$igroupsPass = true;
		if($query && mysql_num_rows($query))
		{
			$result = mysql_fetch_row($query);
			$password = $result[0];
		}
		else
		{
			//Generate a random password
			$generated_pass = generatePassword($length=6, $strength=8);
			$password = md5($generated_pass);
			$igroupsPass = false;
		}
		//Insert user into database
		$query = $db->query("insert into People( email, fName, lName, password, userType) values ( '$email', \"$fname\", \"$lname\", \"$password\" , $type)");
		if(!$query)
			return false;

		$person = new Person($db->insertID(), $db);			
		//Email password and instructions to user only if they are an admin or faculty, or they had to have a password generated
		if($type >= 1 || !$igroupsPass)
		{
			$email = $person->getEmail();
			$subject = "Your {$GLOBALS['systemname']} user account";
			if($igroupsPass)
				$body = "Your {$GLOBALS['systemname']} user account has been created. Your login name and password are the same as on iGroups.";
			else
				$body = "Your {$GLOBALS['systemname']} user account has been created. Your login name is your email address and your password is:\n$generated_pass\nYou should change this password after you log in.";
			$body .= "\n\nRegards,\n{$GLOBALS['systemname']} Administrator\n{$GLOBALS['rootdir']}";
			// admin email specific to peer review system
			$headers = "From: {$GLOBALS['systemname']} <{$GLOBALS['adminemail']}>";
			mail($email, $subject, $body, $headers);
		}
		return $person;
	}
}

function generatePassword($length=9, $strength=0)
{
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1)
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	if ($strength & 2)
		$vowels .= "AEUY";
	if ($strength & 4)
		$consonants .= '23456789';
	if ($strength & 8)
		$consonants .= '@#$%';

	$password = '';
	$alt = time() % 2;
	for($i = 0; $i < $length; $i++)
	{
		if($alt == 1)
		{
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		}
		else
		{
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}
?>
