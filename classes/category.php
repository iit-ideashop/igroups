<?php
include_once( "superstring.php" );
include_once("group.php");

if ( !class_exists( "Category" ) ) {
	class Category {
		var $id, $name, $desc, $group, $type, $semester;
		var $db;
		
		function Category( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			if ( $category = mysql_fetch_array( $db->igroupsQuery( "SELECT * FROM Categories WHERE iID=$id" ) ) ) {
				$this->name = new SuperString( $category['sName'] );
				$this->desc = new SuperString( $category['sDescription'] );
				$this->group = $category['iGroupID'];
				$this->type = $category['iGroupType'];
				$this->semester = $category['iSemesterID'];
			}
		}
		
		function getID() {
			return $this->id;
		}
		
		function getName() {
			if($this->name)
				return $this->name->getString();
			else
				return "Uncategorized";
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
		
		function setName( $string ) {
			if ( $string != "" && $this->name)
				$this->name->setString( $string );
		}
		
		function getDesc() {
			if ($this->name)
				return $this->desc->getString();
			else
				return "Uncategorized E-mails";
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
			if ( $string != "" && $this->name)
				$this->desc->setString( $string );
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

		function setGroup( $gid ) {
			$this->group = $gid;
		}	

		function setSemester ( $sem ) {
			$this->semester = $sem;
		}

		function setType ( $newType ) {
			$this->type = $newType;
		}

		function getEmails() {
			$returnArray = array();
			
			if ( $this->getGroupType() == 0 ) {
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iCategoryID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." AND iSemesterID=".$this->getSemester()." ORDER BY iID DESC" );
			}
			else {
				$emails = $this->db->igroupsQuery( "SELECT iID FROM Emails WHERE iCategoryID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." ORDER BY iID DESC" );
			}
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getEmailsSortedBy($sort) {
			$returnArray = array();
			$add = decodeEmailSort($sort);
			
			if ( $this->getGroupType() == 0 )
				$emails = $this->db->igroupsQuery( "SELECT Emails.iID, People.sFName, People.sLName FROM Emails inner join People on Emails.iSenderID=People.iID WHERE Emails.iCategoryID=0 AND Emails.iGroupID=".$this->getID()." AND Emails.iGroupType=".$this->getType()." AND Emails.iSemesterID=".$this->getSemester().$add );
			else 
				$emails = $this->db->igroupsQuery( "SELECT Emails.iID, People.sFName, People.sLName FROM Emails inner join People on Emails.iSenderID=People.iID WHERE Emails.iCategoryID=0 AND Emails.iGroupID=".$this->getID()." AND Emails.iGroupType=".$this->getType().$add );
			while ( $row = mysql_fetch_row( $emails ) ) {
				$returnArray[] = new Email( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function delete() {
			$emails = $this->getEmails();
			foreach ( $emails as $email ) {
				$email->setCategory( 0 );
				$email->updateDB();
			}
			$this->db->igroupsQuery( "DELETE FROM Categories WHERE iID=".$this->getID() );
		}
		
		function updateDB() {
			$this->db->igroupsQuery( "UPDATE Categories SET sName='".$this->getNameDB()."', sDescription='".$this->getDescDB()."' WHERE iID=".$this->id );
		}
	}
	
	function createCategory( $name, $desc, $group, $type, $semester, $db ) {
		if ( $name != "" ) {
			$namess = new SuperString( $name );
			$descss = new SuperString( $desc );
			if ( $type == 0 ) {
				$query = "INSERT INTO Categories ( sName, sDescription, iGroupID, iGroupType, iSemesterID ) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', $group, $type, $semester )";
			}
			else {
				$query = "INSERT INTO Categories ( sName, sDescription, iGroupID, iGroupType ) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', $group, $type )";
			}
			$db->igroupsQuery( $query );
			return new Category( $db->igroupsInsertID(), $db );
		}
		return false;
	}
}
?>
