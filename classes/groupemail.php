<?php
include_once( "superstring.php" );

if ( !class_exists( "GroupEmail" ) ) {
	class GroupEmail {
		var $id, $to, $subject, $body, $sender, $senddate, $semester;
		var $db;
	
		function GroupEmail( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			if ( $email = mysql_fetch_array( $db->igroupsQuery( "SELECT * FROM GroupEmails WHERE iID=$id" ) ) ) {
				$this->to = $email['sTo'];
				$this->subject = new SuperString( $email['sSubject'] );
				$this->body = new SuperString( $email['sBody'] );
				$this->sender = $email['iSenderID'];
				$this->senddate = $email['dDate'];
				$this->semester = $email['iSemesterID'];
			}
		}
		
		function getID() {
			return $this->id;
		}
			
		function getTo() {
			return $this->to;
		}
	
		function getSubject() {
			return $this->subject->getString();
		}
		
		function getSubjectDB() {
			return $this->subject->getDBString();
		}
		
		function getSubjectHTML() {
			return $this->subject->getHTMLString();
		}
		
		function getSubjectJava() {
			return $this->subject->getJavaString();
		}
		
		function getShortSubject() {
			$subj = $this->subject->getString();
			if ( strlen( $subj ) < 40 )
				return $subj;
			$x = strpos( $subj, " ", 40 );
			if ( $x > 0  && $x < strlen( $subj ) )
				return substr( $subj, 0, $x )."...";
			return $subj;
		}
		
		function setSubject( $string ) {
			if ( $string != "" )
				$this->subject->setString( $string );
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
		
		function getSenderID() {
			return $this->sender;
		}
		
		function getSender() {
			return new Person( $this->sender, $this->db );
		}	
		
		function getDate() {
			$temp = explode( "-", $this->senddate );
			return date( "m/d/Y", mktime( 0, 0, 0, $temp[1], $temp[2], $temp[0] ) );
		}	
	
		function getDateDB() {
			return $this->senddate;
		}
		
		function setDate( $date ) {
			$temp = explode( "/", $date );
			if ( count( $temp ) == 3 )
				$this->senddate = date( "Y-m-d", mktime( 0, 0, 0, $temp[0], $temp[1], $temp[2] ) );
		}
		
		function getSemester() {
			return $this->semester;
		}
		
		function delete() {
			$this->db->igroupsQuery( "DELETE FROM GroupEmails WHERE iID=".$this->getID() );
		}
		
		function updateDB() {
			$this->db->igroupsQuery( "UPDATE GroupEmails SET sTo='".$this->getTo()."', sSubject='".$this->getSubjectDB()."', sBody='".$this->getBodyDB()."', iCategoryID=".$this->category." WHERE iID=".$this->id );
		}
	}
	
	function createEmail( $to, $subject, $body, $sender, $semester, $db ) {
		$subj = new SuperString( $subject );
		$bod = new SuperString( $body );
		$db->igroupsQuery( "INSERT INTO GroupEmails( sTo, sSubject, sBody, dDate, iSenderID, iSemesterID ) VALUES ( '".$to."', '".$subj->getDBString()."', '".$bod->getDBString()."', '".date("Y-m-d")."', $sender, $semester )" );
		return new GroupEmail( $db->igroupsInsertID(), $db );
	}
}
?>
