<?php
if ( !class_exists( "Quota" ) ) {
	class Quota {
		var $group, $type, $semester, $used, $limit;
		var $db;
		var $limitDefault=104857600;
		
		function Quota( $group, $db ) {
			$this->group = $group->getID();
			$this->type = $group->getType();
			$this->semester = $group->getSemester();
			$this->db = $db;
			$query = "SELECT * FROM FileQuota WHERE iGroupID=".$this->group." AND iGroupType=".$this->type;
			if ( $this->type == 0 )
				$query .= " AND iSemesterID=".$this->semester;
			if ( $quota = mysql_fetch_array( $db->igroupsQuery( $query ) ) ) {
				$this->used = $quota['iUsed'];
				$this->limit = $quota['iLimit'];
			}
			else {
				$this->used = 0;
				$this->limit = $this->limitDefault;
				$db->igroupsQuery( "INSERT INTO FileQuota( iLimit, iGroupID, iGroupType, iSemesterID ) VALUES ( ".$this->limitDefault.", ".$this->group.", ".$this->type.", ".$this->semester." )" );
			}
		}
		
		function getUsed() {
			return $this->used;
		}
		
		function getLimit() {
			return $this->limit;
		}
		
		function setLimit( $limit ) {
			$this->limit = $limit;
		}
		
		function getPercentUsed() {
			return ( ( $this->used*100 )/$this->limit );
		}
		
		function sendWarning($warn) {
			if ($this->type == 0) {
				$result = $this->db->iknowQuery("SELECT sIITID from Projects WHERE iID=".$this->group);
				$array = mysql_fetch_array($result);
				$name = $array['sIITID'];
				}
				else {
				$result = $this->db->igroupsQuery("SELECT sName from Groups WHERE iID=".$this->group);
				$array = mysql_fetch_array($result);
				$name = $array['sName'];
				}
				$headers = "From: \"$appname Support\" <$contactemail>\n";
				$headers .= "To: \"$appname Support\" <$contactemail>\n";
				$headers .= "Content-Type: text/plain;\n";
				$headers .= "Content-Transfer-Encoding: 7bit;\n";
				if ($warn == 0)
				mail('', "$appname Group Quota Notification", "This is an auto-generated message warning you that $name has less than 20MB of space left in their quota. They currently have a quota of {$this->limit}. Please increase their quota to allow them to upload more files into $appname.", $headers);
				else
				mail('', "$appname Group Quota Warning", "This is an auto-generated message warning you that $name has run out of space in their quota. They currently have a quota of {$this->limit}. Please increase their quota to allow them to upload more files into $appname.", $headers);

		}	
	
		function increaseUsed( $amount ) {
			$this->used += $amount;
			if (($this->limit) - ($this->used) <= 20) {
				$this->limit += 50;
			} 
		}
		
		function decreaseUsed( $amount ) {
			$this->used -= ($amount);
		}
		
		function checkSpace( $amount ) {
			return ( ( $this->limit - $this->used ) > ($amount) );
		}
		
		function getGroupID() {
			return $this->group;
		}
		
		function getGroupType() {
			return $this->type;
		}
		
		function getSemester() {
			return $this->semester;
		}
		
		function updateDB() {
			$query = "UPDATE FileQuota SET iUsed=".$this->getUsed().", iLimit=".$this->getLimit()." WHERE iGroupID=".$this->group." AND iGroupType=".$this->type;
			if ( $this->type == 0 )
				$query .= " AND iSemesterID=".$this->semester;
			$this->db->igroupsQuery( $query );
		}
	}
	
	function createQuota( $group, $db ) {
		$db->igroupsQuery( "INSERT INTO FileQuota ( iGroupID, iGroupType, iSemesterID ) VALUES ( ".$group->getID().", ".$group->getType().", ".$group->getSemester()." )" );
		return new Quota( $group, $db );
	}
}
?>
