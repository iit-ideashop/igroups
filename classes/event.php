<?php
include_once( "superstring.php" );

if ( !class_exists( "Event" ) ) {
	class Event {
		var $id, $name, $desc, $eventdate, $group, $type, $semester;
		var $db;
		
		function Event( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			if ( $event = mysql_fetch_array( $db->igroupsQuery( "SELECT * FROM Events WHERE iID=$id" ) ) ) {
				$this->name = new SuperString( $event['sTitle'] );
				$this->desc = new SuperString( $event['sDescription'] );
				$this->eventdate = $event['dDate'];
				$this->group = $event['iGroupID'];
				$this->type = $event['iGroupType'];
				$this->semester = $event['iSemesterID'];
			}
		}
			
		function getID() {
			return $this->id;
		}
		
		function getName() {
			return $this->name->getString();
		}
		
		function getNameDB() {
			return $this->name->getDBString();
		}
		
		function getNameHTML() {
			return $this->name->getHTMLString();
		}
		
		function getNameJava() {
			return $this->name->getJavaString();
		}
		
		function isIPROEvent() {
			return !$this->group;
		}

		function setName( $string ) {
			if ( $string != "" )
				$this->name->setString( $string );
		}
		
		function getDesc() {
			return $this->desc->getString();
		}
		
		function getDescDB() {
			return $this->desc->getDBString();
		}
		
		function getDescHTML() {
			return $this->desc->getHTMLString();
		}
		
		function getDescJava() {
			return $this->desc->getJavaString();
		}
		
		function setDesc( $string ) {
			if ( $string != "" )
				$this->desc->setString( $string );
		}
			
		function getDate() {
			$temp = explode( "-", $this->eventdate );
			return date( "m/d/Y", mktime( 0, 0, 0, $temp[1], $temp[2], $temp[0] ) );
		}	
	
		function getDateDB() {
			return $this->eventdate;
		}
		
		function setDate( $date ) {
			$temp = explode( "/", $date );
			if ( count( $temp ) == 3 )
				$this->eventdate = date( "Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2] ) );
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
		
		function getGroup() {
			return new Group( $this->getGroupID(), $this->getGroupType(), $this->getSemester(), $this->db );
		}
		
		function delete() {
			$this->db->igroupsQuery( "DELETE FROM Events WHERE iID=".$this->getID() );
		}
		
		function updateDB() {
			$this->db->igroupsQuery( "UPDATE Events SET sTitle='".$this->getNameDB()."', sDescription='".$this->getDescDB()."', dDate='".$this->getDateDB()."' WHERE iID=".$this->id );
		}
	}
	
	function createEvent( $name, $desc, $date, $group, $db ) {
		$namess = new SuperString( $name );
		$descss = new SuperString( $desc );
		$currDate = date("Y-m-d");
		$temp = explode( "/", $date );
		$dbdate = date( "Y-m-d", mktime(0,0,0,$temp[0],$temp[1],$temp[2] ) );
		
		$db->igroupsQuery( "INSERT INTO Events( sTitle, sDescription, dDate, iGroupID, iGroupType, iSemesterID, dCreateDate ) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', '$dbdate', ".$group->getID().", ".$group->getType().", ".$group->getSemester().", '".$currDate."' )" );
		
		return new Event( $db->igroupsInsertID(), $db );
	}
	
	function createIPROEvent( $name, $desc, $date, $semester, $db ) {
		$namess = new SuperString( $name );
		$descss = new SuperString( $desc );
		$currDate = date("Y-m-d");
		$temp = explode( "/", $date );
		$dbdate = date( "Y-m-d", mktime(0,0,0,$temp[0],$temp[1],$temp[2] ) );
		
		$db->igroupsQuery( "INSERT INTO Events( sTitle, sDescription, dDate, iGroupID, iGroupType, iSemesterID, dCreateDate ) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', '$dbdate', 0, 0, ".$semester->getID().", '".$currDate."' )" );
		
		return new Event( $db->igroupsInsertID(), $db );
	}
}
?>
