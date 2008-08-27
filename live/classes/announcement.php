<?php
include_once( "superstring.php" );

if ( !class_exists( "Announcement" ) ) {
	class Announcement {
		var $id, $heading, $body, $expires;
		var $db;
		
		function Announcement( $id, $db ) {
			$this->id = $id;
			if ( $tmp = mysql_fetch_array( $db->igroupsQuery( "SELECT * FROM News WHERE iID=$id" ) ) ) {
				$this->heading = new SuperString( $tmp['sHeadline'] );
				$this->body = new SuperString( $tmp['sBody'] );
				$this->expires = $tmp['dExpire'];
				$this->db = $db;
			}
		}
		
		function getID() {
			return $this->id;
		}
		
		function getHeading() {
			return $this->heading->getString();
		}
		
		function getHeadingDB() {
			return $this->heading->getDBString();
		}
		
		function getHeadingHTML() {
			return $this->heading->getHTMLString();
		}
		
		function getHeadingJava() {
			return $this->heading->getJavaString();
		}
		
		function setHeading( $string ) {
			if ( $string != "" )
				$this->heading->setString( $string );
		}
		
		function getBody() {
			return $this->body->getString();
		}
		
		function getBodyDB() {
			return $this->body->getDBString();
		}
		
		function getBodyHTML() {
			return $this->body->getHTMLString();
		}
		
		function getBodyJava() {
			return $this->body->getJavaString();
		}
		
		function setBody( $string ) {
			if ( $string != "" )
				$this->body->setString( $string );
		}
			
		function getExpirationDate() {
			$temp = explode( "-", $this->expires );
			return date( "m/d/Y", mktime( 0, 0, 0, $temp[1], $temp[2], $temp[0] ) );
		}	
	
		function getExpirationDateDB() {
			return $this->expires;
		}
		
		function setExpirationDate( $date ) {
			$temp = explode( "/", $date );
			if ( count( $temp ) == 3 )
				$this->expires = date( "Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2] ) );
		}

		function isExpired() {
			$currDate = date('Y-m-d');
			if ($this->expires <= $currDate)
				return true;
			else
				return false;
		}
		
		function delete() {
			$this->db->igroupsQuery( "DELETE FROM News WHERE iID=".$this->getID() );
		}
		
		function updateDB() {
			$this->db->igroupsQuery( "UPDATE News SET sHeadline='".$this->getHeadingDB()."', sBody='".$this->getBodyDB()."', dExpire='".$this->getExpirationDateDB()."' WHERE iID=".$this->getID() );
		}
	}
	
	function createAnnouncement( $heading, $body, $date, $db ) {
		$temp = explode( "/", $date );
		if ( $heading != "" && $body != "" && count( $temp ) == 3 ) {
			$head = new SuperString( $heading );
			$bod = new SuperString( $body );
			$db->igroupsQuery( "INSERT INTO News ( sHeadline, sBody, dExpire ) VALUES ( '".$head->getDBString()."', '".$bod->getDBString()."', '".date( "Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2] ) )."' )" );
			return new Announcement( $db->igroupsInsertID(), $db );
		}
		return false;
	}
}
?>
