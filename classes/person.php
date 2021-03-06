<?php
include_once('superstring.php');
include_once('globals.php');

if(!class_exists('Person'))
{
	class Person
	{
		var $id, $firstname, $lastname, $email, $phone, $address, $password, $usertype, $receives, $valid;
		var $db;
		
		function Person($id, $db)
		{
			if(is_numeric($id))
			{
				if($temp = mysql_fetch_array($db->query("SELECT * FROM People WHERE iID=$id")))
				{
					$this->id = $id;
					$this->firstname = $temp['sFName'];
					$this->lastname = $temp['sLName'];
					$this->email = $temp['sEmail'];
					$this->phone = $temp['sPhone'];
					$this->address = $temp['sAddress'];
					$this->usertype = $temp['iUserTypeID'];
					$this->password = $temp['sPassword'];
					$this->receives = ($temp['bReceiveNotifications'] ? true : false);
					$this->valid = true;
					$this->db = $db;
				}
				else
					$this->valid = false;
			}
			else
				$this->valid = false;
		}
		
		function isValid()
		{
			return $this->valid;
		}
		
		function getID()
		{
			return $this->id;
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
	
		function getShortName()
		{
			$firstInitial = substr($this->firstname, 0, 1).'.';
			return ($firstInitial.' '.$this->lastname);
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
		
		function getPhone()
		{
			return $this->phone;
		}
		
		function setPhone($phone)
		{
			if($phone != '')
				$this->phone = $phone;
		}
		
		function setPassword($pwd)
		{
			if($pwd != '')
				$this->password = md5($pwd);
		}
		
		function getAddress()
		{
			return $this->address;
		}
		
		function setAddress($address)
		{
			if($address != '')
				$this->address = $address;
		}

		function getProfile()
		{
			$query = $this->db->query("SELECT * FROM Profiles where iPersonID={$this->id}");
			$result = mysql_fetch_array($query);
			return $result;
		}

		function isGroupMember($group)
		{
			if(!$group)
				return false;
		
			switch($group->getType())
			{
				case 0:
					if(mysql_num_rows($this->db->query("SELECT * FROM PeopleProjectMap WHERE iProjectID=".$group->getID()." AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id)) == 0)
						return false;
					else
						return true;
				case 1:
					if(mysql_num_rows($this->db->query("SELECT * FROM PeopleGroupMap WHERE iGroupID=".$group->getID()." AND iPersonID=".$this->id)) == 0)
						return false;
					else
						return true;
				default:
					return false;
			}
		}
		
		function addToGroupNoEmail($group)
		{
			if (!$this->isGroupMember($group))
			{
				if ($group->getType() == 0)
					$this->db->query("INSERT INTO PeopleProjectMap(iPersonID, iProjectID, iSemesterID) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getSemester().")");
				else
					$this->db->query("INSERT INTO PeopleGroupMap(iPersonID, iGroupID) VALUES ( ".$this->id.", ".$group->getID()." )");
			} 
		}

		function addToGroup($group)
		{
			global $appname, $appurl, $contactemail;
			if(!$this->isGroupMember($group))
			{
				switch ($group->getType())
				{
					case 0:
						$this->db->query("INSERT INTO PeopleProjectMap(iPersonID, iProjectID, iSemesterID) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getSemester()." )");

						//Generate E-mail Responder
						if (strchr($this->getEmail(), '@iit.edu'))
							$username = substr($this->getEmail(),0,strlen($this->getEmail())-8);
						else
							$username = $this->getEmail();

						$msg = "Welcome to {$group->getName()}: {$group->getDesc()}! You have been added to this IPRO team space in the $appname system at http://igroups.iit.edu with the following account details: \n\n";
						$msg .= "Username: {$username}\n";
						$msg .= "Password: If you are a new $appname user and IIT student, your initial password is the same as the first part of your e-mail address in front of the \"@hawk.iit.edu\" sign. If you are a new iGroups user and not an IIT student, your initial password is the same as the first part of your e-mail address in front of \"@iit.edu\", \"@gmail.com\", etc.\n\n";
						$msg .= "Please change your password the first time you log into iGroups. If you are a previous iGroups user, you can use your previous password to log in, or request a new password if you no longer remember it. (Note that the iGroups system is separate and distinct from the MyIIT portal login.)\n\n";
						$msg .= "Please contact ipro@iit.edu if you have any questions, problems or concerns.";
						$msg .= "Thank you!\n\nYour IPRO Program Staff";
						$headers = "From: \"$appname Support\" <$contactemail>\n";
						$headers .= "To: \"{$this->getFullName()}\" <{$this->getEmail()}>\n";
						$headers .= "Content-Type: text/plain;\n";
						$headers .= "Content-Transfer-Encoding: 7bit;\n";
						$headers .= "Reply-to: ipro@iit.edu\n";
						mail('', "Your $appname Account", $msg, $headers);
						break;
					case 1:
						$this->db->query("INSERT INTO PeopleGroupMap(iPersonID, iGroupID) VALUES ( ".$this->id.", ".$group->getID()." )");
						
						//Generate E-mail Responder
						if (strchr($this->getEmail(), '@iit.edu'))
							$username = substr($this->getEmail(),0,strlen($this->getEmail())-8);
						else
							$username = $this->getEmail();
						$msg = "Welcome to {$group->getName()}: {$group->getDesc()}! You have been added to this IPRO team space in the $appname system at http://igroups.iit.edu with the following account details: \n\n";
						$msg .= "Username: {$username}\n";
						$msg .= "Password: If you are a new $appname user and IIT student, your initial password is the same as the first part of your e-mail address in front of the \"@hawk.iit.edu\" sign. If you are a new iGroups user and not an IIT student, your initial password is the same as the first part of your e-mail address in front of \"@iit.edu\", \"@gmail.com\", etc.\n\n";
						$msg .= "Please change your password the first time you log into iGroups. If you are a previous iGroups user, you can use your previous password to log in, or request a new password if you no longer remember it. (Note that the iGroups system is separate and distinct from the MyIIT portal login.)\n\n";
						$msg .= "Please contact ipro@iit.edu if you have any questions, problems or concerns.";
						$msg .= "Thank you!\n\nYour IPRO Program Staff";
						$headers = "From: \"$appname Support\" <$contactemail>\n";
						$headers .= "To: \"{$this->getFullName()}\" <{$this->getEmail()}>\n";
						$headers .= "Content-Type: text/plain;\n";
						$headers .= "Content-Transfer-Encoding: 7bit;\n";
						$headers .= "Reply-to: ipro@iit.edu\n";
						mail('', "Your $appname Account", $msg, $headers);
						break;
				}
			}
		}
		
		function removeFromGroup($group)
		{	
			switch($group->getType())
			{
				case 0:
					$this->db->query("DELETE FROM PeopleProjectMap WHERE iProjectID=".$group->getID()." AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id);
					$this->db->query("DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id);
					break;
				case 1:
					$this->db->query("DELETE FROM PeopleGroupMap WHERE iGroupID=".$group->getID()." AND iPersonID=".$this->id);
					$this->db->query("DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=1 AND iPersonID=".$this->id);
					break;
			}
		}
		
		function isGroupGuest($group)
		{
			if(!$group)
				return false;

			if($group->getType() == 0)
				$result = $this->db->query("SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester());
			else
				$result = $this->db->query("SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=1");
			if($row = mysql_fetch_row($result))
			{
				if($row[0] == 0)
					return true;
				else
					return false;
			}
			else
				return false;
		}

		function isGroupModerator($group)
		{
			if(!$group) 
				return false;
			
			if($group->getType() == 0) 
				$result = $this->db->query("SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester());
			else
				$result = $this->db->query("SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=1");
			if($row = mysql_fetch_row($result))
			{
				if($row[0] >= 1)
					return true;
				else
					return false;
			}
			else
				return false;
		}		
		
		function isGroupAdministrator($group)
		{
			if(!$group)
				return false;
		
			if($group->getType() == 0)
				$result = $this->db->query("SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester());
			else
				$result = $this->db->query("SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=1");
			if($row = mysql_fetch_row($result))
			{
				if($row[0] >= 2)
					return true;
				else
					return false;
			}
			else
			{
				return false;
			}
		}
		
		function setGroupAccessLevel($level, $group)
		{
			if($group->getType() == 0) 
				$this->db->query("DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id);
			else
				$this->db->query("DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=1 AND iPersonID=".$this->id);
			if($level == -1)
				$this->db->query("INSERT INTO GroupAccessMap( iPersonID, iGroupID, iGroupType, iSemesterID, iAccessLevel ) VALUES(".$this->id.", ".$group->getID().", ".$group->getType().", ".$group->getSemester().", 0 )");
			else if ($level == 1)
				 $this->db->query("INSERT INTO GroupAccessMap( iPersonID, iGroupID, iGroupType, iSemesterID, iAccessLevel ) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getType().", ".$group->getSemester().", 1 )");
			else if ($level == 2)
				  $this->db->query("INSERT INTO GroupAccessMap( iPersonID, iGroupID, iGroupType, iSemesterID, iAccessLevel ) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getType().", ".$group->getSemester().", 2 )");				 
		}
		
		function getGroups()
		{
			$returnArray = array();
			$ipros = $this->db->query("SELECT iProjectID,iSemesterID FROM PeopleProjectMap WHERE iPersonID=".$this->getID());

			while($row = mysql_fetch_row($ipros))
				$returnArray[] = new Group($row[0], 0, $row[1], $this->db);

			$igroups = $this->db->query("SELECT iGroupID FROM PeopleGroupMap WHERE iPersonID=".$this->getID());

			while($row = mysql_fetch_row($igroups))
				$returnArray[] = new Group($row[0], 1, 0, $this->db);

			return $returnArray;
		}
		
    function getActiveGroups() 
		{
      $activeIpros = $this->db->query("SELECT iProjectID,iSemesterID FROM PeopleProjectMap JOIN Semesters WHERE iPersonID=".$this->id." AND PeopleProjectMap.iSemesterID=Semesters.iID and Semesters.bActiveFlag=1 LIMIT 1");
			$returnArray = array();
			while($row = mysql_fetch_row($activeIpros))
				  $returnArray[] = new Group($row[0], 0,$row[1] , $this->db);
			return $returnArray;
    }

		function getGroupsBySemester($semester)
		{
			$ipros = $this->db->query("SELECT iProjectID FROM PeopleProjectMap WHERE iPersonID=".$this->id." AND iSemesterID=$semester order by iProjectID");
			$returnArray = array();
			while($row = mysql_fetch_row($ipros))
				$returnArray[] = new Group($row[0], 0, $semester, $this->db);
			return $returnArray;
		}

		function isAdministrator()
		{
			return ($this->usertype == 1);
		}
		
		function receivesNotifications()
		{
			return $this->receives;
		}

		function getNuggets()
		{
			//get nuggets from old and new system
			$db = new dbConnection();
			$query = "SELECT iNuggetID FROM PeopleNuggetMap WHERE iPersonID = $this->id ";
			$results = $db->query($query);
			$nuggets = array();
			while($row = mysql_fetch_array($results))
				$nuggets[] = new Nugget($row[0],$this->db,1);
			$query = "SELECT iNuggetID FROM nuggetAuthorMap WHERE iAuthorID = $this->id ";
			$results = $this->db->query($query);
			while($row = mysql_fetch_array($results))
				$nuggets[] = new Nugget($row[0], $this->db,0);
			return $nuggets;
		}
		
		function getAssignedTasks($group)
		{
			$query = $this->db->query("select iTaskID from TaskAssignments join Tasks on Tasks.iID=TaskAssignments.iTaskID where iPersonID={$this->id} and Tasks.iTeamID={$group->getID()}");
			$tasks = array();
			while($row = mysql_fetch_row($query))
				$tasks[] = new Task($row[0], $group->getType(), $group->getSemester(), $this->db);
			return $tasks;
		}
		
		function getAssignedTasksByName($group)
		{
			$query = $this->db->query("select iTaskID from TaskAssignments join Tasks on Tasks.iID=TaskAssignments.iTaskID where iPersonID={$this->id} and Tasks.iTeamID={$group->getID()} order by Tasks.sName");
			$tasks = array();
			while($row = mysql_fetch_row($query))
				$tasks[] = new Task($row[0], $group->getType(), $group->getSemester(), $this->db);
			return $tasks;
		}
		
		function updateDB()
		{
			$this->db->query("UPDATE People SET sFName='".quickDBString($this->getFirstName())."', sLName='".quickDBString($this->getLastName())."', sPhone='".quickDBString($this->getPhone())."', sAddress='".quickDBString($this->getAddress())."', sPassword='".$this->password."' WHERE iID=".$this->id);
		}
	}
	
	function createPerson($email, $fname, $lname, $db)
	{
		$temp = explode('@', $email);
		$pw = md5($temp[0]);
		$db->query("INSERT INTO People( sEmail, sFName, sLName, sPassword, bActiveFlag) VALUES ( '$email', '$fname', '$lname', '$pw' , '1')");
		return new Person($db->insertID(), $db);
	}
}
?>
