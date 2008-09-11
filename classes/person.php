<?php
include_once( "superstring.php" );

if ( !class_exists( "Person" ) ) {
	class Person {
		var $id, $firstname, $lastname, $email, $phone, $address, $password, $usertype;
		var $db;
		
		function Person( $id, $db ) {
			if (isset($id)){
				if ( $temp = mysql_fetch_array( $db->iknowQuery( "SELECT * FROM People WHERE iID=$id" ) ) ) {
					$this->id = $id;
					$this->firstname = $temp['sFName'];
					$this->lastname = $temp['sLName'];
					$this->email = $temp['sEmail'];
					$this->phone = $temp['sPhone'];
					$this->address = $temp['sAddress'];
					$this->usertype = $temp['iUserTypeID'];
					$this->password = $temp['sPassword'];
					$this->db = $db;
				}
			}
		}
		
		function getID() {
			return $this->id;
		}
		
		function getFirstName() {
			return $this->firstname;
		}
		
		function setFirstName( $name ) {
			if ( $name != "" )
				$this->firstname = $name;
		}
		
		function getLastName() {
			return $this->lastname;
		}
		
		function setLastName( $name ) {
			if ( $name != "" )
				$this->lastname = $name;
		}
		
		function getFullName() {
			return ( $this->firstname." ".$this->lastname );
		}
		
		function getCommaName() {
			return( $this->lastname.", ".$this->firstname );
		}
	
		function getShortName() {
			$firstInitial = substr($this->firstname, 0, 1) . '.';
			return ($firstInitial . ' ' . $this->lastname);	
		}
		
		function getEmail() {
			return $this->email;
		}
		
		function setEmail( $email ) {
			if ( $email != "" )
				$this->email = $email;
		}
		
		function getPhone() {
			return $this->phone;
		}
		
		function setPhone( $phone ) {
			if ( $phone != "" )
				$this->phone = $phone;
		}
		
		function setPassword( $pwd ) {
			if ( $pwd != "" )
				$this->password = md5($pwd);
		}
		
		function getAddress() {
			return $this->address;
		}
		
		function setAddress( $address ) {
			if ( $address != "" )
				$this->address = $address;
		}

		function getProfile() {
			$query = $this->db->igroupsQuery("SELECT * FROM Profiles where iPersonID={$this->id}");
			$result = mysql_fetch_array($query);
			return $result;
		}

		function isGroupMember( $group ) {
			if ( !$group )
				return false;
		
			switch ($group->getType()) {
				case 0:
					if ( mysql_num_rows( $this->db->iknowQuery( "SELECT * FROM PeopleProjectMap WHERE iProjectID=".$group->getID()." AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id ) ) == 0 )
						return false;
					else
						return true;
				case 1:
					if ( mysql_num_rows( $this->db->igroupsQuery( "SELECT * FROM PeopleGroupMap WHERE iGroupID=".$group->getID()." AND iPersonID=".$this->id ) ) == 0 )
						return false;
					else
						return true;
				default:
					return false;
			}
		}
		
		function addToGroupNoEmail($group) {
			if (!$this->isGroupMember($group) ) {
				if ($group->getType() == 0) {
					$this->db->iknowQuery( "INSERT INTO PeopleProjectMap(iPersonID, iProjectID, iSemesterID) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getSemester()." )" );
				}
				else {
					$this->db->igroupsQuery( "INSERT INTO PeopleGroupMap(iPersonID, iGroupID) VALUES ( ".$this->id.", ".$group->getID()." )" );
				}
			} 
		}

		function addToGroup( $group ) {
			if ( !$this->isGroupMember( $group ) ) {
				switch ($group->getType()) {
					case 0:
						$this->db->iknowQuery( "INSERT INTO PeopleProjectMap(iPersonID, iProjectID, iSemesterID) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getSemester()." )" );

						//Generate E-mail Responder
        					if (strchr($this->getEmail(), '@iit.edu'))
					                $username = substr($this->getEmail(),0,strlen($this->getEmail())-8);
					        else
					                $username = $this->getEmail();
					        $msg = "You have been added to {$group->getName()}: {$group->getDesc()} in the iGROUPS system with the following account details:\n\n";
					        $msg .= "Username: {$username}\n";
					        $msg .= "Password: If you are a new iGROUPS user, your initial password is the 
same as your username (or the first part of your e-mail address for non-IIT e-mails). Please change your password the first time you log the system.\n\n";
					        $msg .= "You can access the iGROUPS system at igroups.iit.edu\nContact iproadmin@iit.edu with any problems or questions.\n\n";
					        $msg .= "--- The IPRO Office Team";
					        $headers = "From: \"IPRO Office\" <iproadmin@iit.edu>\n";
					        $headers .= "To: \"{$this->getFullName()}\" <{$this->getEmail()}>\n";
					        $headers .= "Content-Type: text/plain;\n";
					        $headers .= "Content-Transfer-Encoding: 7bit;\n";
					        mail('', 'Your iGROUPS Account', $msg, $headers);
						break;
					case 1:
						$this->db->igroupsQuery( "INSERT INTO PeopleGroupMap(iPersonID, iGroupID) VALUES ( ".$this->id.", ".$group->getID()." )" );
						
						//Generate E-mail Responder
                                                if (strchr($this->getEmail(), '@iit.edu'))
                                                        $username = substr($this->getEmail(),0,strlen($this->getEmail())-8);
                                                else
                                                        $username = $this->getEmail();
                                                $msg = "You have been added to {$group->getName()} in the iGROUPS system with the following account details:\n\n";
                                                $msg .= "Username: {$username}\n";
                                                $msg .= "Password: If you are a new iGROUPS user, your initial password is the 
same as your username (or the first part of your e-mail address for non-IIT e-mails). Please change your password the first time you log the system.\n\n";
                                                $msg .= "You can access the iGROUPS system at igroups.iit.edu\nContact iproadmin@iit.edu with any problems or questions.\n\n";
                                                $msg .= "--- The IPRO Office Team";
                                                $headers = "From: \"IPRO Office\" <iproadmin@iit.edu>\n";
                                                $headers .= "To: \"{$this->getFullName()}\" <{$this->getEmail()}>\n";
                                                $headers .= "Content-Type: text/plain;\n";
                                                $headers .= "Content-Transfer-Encoding: 7bit;\n";
                                                mail('', 'Your iGROUPS Account', $msg, $headers);
						break;
				}
			}
		}
		
		function removeFromGroup( $group ) {	
			switch ( $group->getType() ) {
				case 0:
					$this->db->iknowQuery( "DELETE FROM PeopleProjectMap WHERE iProjectID=".$group->getID()." AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id );
					$this->db->igroupsQuery( "DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id );
					break;
				case 1:
					$this->db->igroupsQuery( "DELETE FROM PeopleGroupMap WHERE iGroupID=".$group->getID()." AND iPersonID=".$this->id );
					$this->db->igroupsQuery( "DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=1 AND iPersonID=".$this->id );
					break;
			}
		}
		
		function isGroupGuest( $group ) {
                        if ( !$group )
                                return false;

                        if ( $group->getType() == 0 )
                                $result = $this->db->igroupsQuery( "SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester() );
                        else
                                $result = $this->db->igroupsQuery( "SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=1" );
                        if ( $row = mysql_fetch_row( $result ) ) {
                                if ( $row[0] == 0 )
                                        return true;
                                else
                                        return false;
                        }
                        else {
                                return false;
                        }
                }

		function isGroupModerator( $group ) {
			if ( !$group ) 
				return false;
			
			if ( $group->getType() == 0 ) 
				$result = $this->db->igroupsQuery( "SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester() );
			else
				$result = $this->db->igroupsQuery( "SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=1" );
			if ( $row = mysql_fetch_row( $result ) ) {
				if ( $row[0] >= 1 )
					return true;
				else
					return false;
			}
			else {
				return false;
			}
		}		
		
		function isGroupAdministrator( $group ) {
			if ( !$group )
				return false;
		
			if ( $group->getType() == 0 ) 
				$result = $this->db->igroupsQuery( "SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester() );
			else
				$result = $this->db->igroupsQuery( "SELECT iAccessLevel FROM GroupAccessMap WHERE iPersonID=".$this->getID()." AND iGroupID=".$group->getID()." AND iGroupType=1" );
			if ( $row = mysql_fetch_row( $result ) ) {
				if ( $row[0] >= 2 )
					return true;
				else
					return false;
			}
			else {
				return false;
			}
		}
		
		function setGroupAccessLevel( $level, $group ) {
			if ( $group->getType() == 0 ) 
				$this->db->igroupsQuery( "DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=0 AND iSemesterID=".$group->getSemester()." AND iPersonID=".$this->id );
			else
				$this->db->igroupsQuery( "DELETE FROM GroupAccessMap WHERE iGroupID=".$group->getID()." AND iGroupType=1 AND iPersonID=".$this->id );
			if ( $level == -1 )
				$this->db->igroupsQuery( "INSERT INTO GroupAccessMap( iPersonID, iGroupID, iGroupType, iSemesterID, iAccessLevel ) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getType().", ".$group->getSemester().", 0 )" );
			else if ($level == 1)
				 $this->db->igroupsQuery( "INSERT INTO GroupAccessMap( iPersonID, iGroupID, iGroupType, iSemesterID, iAccessLevel ) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getType().", ".$group->getSemester().", 1 )" );
			else if ($level == 2)
				  $this->db->igroupsQuery( "INSERT INTO GroupAccessMap( iPersonID, iGroupID, iGroupType, iSemesterID, iAccessLevel ) VALUES ( ".$this->id.", ".$group->getID().", ".$group->getType().", ".$group->getSemester().", 2 )" );				 
		}
		
		function getGroups() {
			$ipros = $this->db->iknowQuery( "SELECT iProjectID,iSemesterID FROM PeopleProjectMap WHERE iPersonID=".$this->getID() );
			while ( $row = mysql_fetch_row( $ipros ) ) {
				$returnArray[] = new Group( $row[0], 0, $row[1], $this->db );
			}
			$igroups = $this->db->igroupsQuery( "SELECT iGroupID FROM PeopleGroupMap WHERE iPersonID=".$this->getID() );
			while ( $row = mysql_fetch_row( $igroups ) ) {
				$returnArray[] = new Group( $row[0], 1, 0, $this->db );
			}
			return $returnArray;
		}
		
		function getGroupsBySemester($semester) {
			$ipros = $this->db->iknowQuery( "SELECT iProjectID FROM PeopleProjectMap WHERE iPersonID=".$this->getID()." AND iSemesterID=$semester order by iSemesterID desc, iProjectID desc");
                        while ( $row = mysql_fetch_row( $ipros ) ) {
                                $returnArray[] = new Group( $row[0], 0, $semester, $this->db );
                        }
                        /*$igroups = $this->db->igroupsQuery( "SELECT iGroupID FROM PeopleGroupMap WHERE iPersonID=".$this->getID() );
                        while ( $row = mysql_fetch_row( $igroups ) ) {
                                $returnArray[] = new Group( $row[0], 1, 0, $this->db );
                        }*/
                        return $returnArray;
		}

		function isAdministrator() {
			return ( $this->usertype == 1 );
		}

		function getNuggets(){
			//get nuggets from old and new system
			$db = new dbConnection();
			$query = "SELECT iNuggetID FROM PeopleNuggetMap WHERE iPersonID = $this->id ";
			$results = $db->igroupsQuery($query);
			$nuggets = array();
			while($row = mysql_fetch_array($results)){
				$nuggets[] = new Nugget($row[0],$this->db,1);
			}
			$query = "SELECT iNuggetID FROM nuggetAuthorMap WHERE iAuthorID = $this->id ";
			$results = $this->db->igroupsQuery($query);
			while($row = mysql_fetch_array($results)){
				$nuggets[] = new Nugget($row[0], $this->db,0);
			}
			return $nuggets;
		}
		
		function updateDB() {
			$this->db->igroupsQuery( "UPDATE People SET sFName='".quickDBString($this->getFirstName())."', sLName='".quickDBString($this->getLastName())."', sPhone='".quickDBString($this->getPhone())."', sAddress='".quickDBString($this->getAddress())."', sPassword='".$this->password."' WHERE iID=".$this->id );
			$this->db->ireviewQuery("UPDATE peer_review.People SET password='{$this->password}' where email='{$this->email}'");
		}
	}
	
	function createPerson( $email, $fname, $lname, $db ) {
		$temp = explode( "@", $email );
		$pw = md5( $temp[0] );
		$db->iknowQuery( "INSERT INTO People( sEmail, sFName, sLName, sPassword, bActiveFlag) VALUES ( '$email', '$fname', '$lname', '$pw' , '1')" );
		return new Person( $db->iknowInsertID(), $db );
	}
}
?>
