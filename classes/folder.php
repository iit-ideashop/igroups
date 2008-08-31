<?php
include_once( "superstring.php" );

if ( !class_exists( "Folder" ) ) {
	class Folder {
		var $id, $name, $desc, $pfid, $security, $group, $type, $semester;
		var $db;
		
		function Folder( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			if ( $folder = mysql_fetch_array( $db->igroupsQuery( "SELECT * FROM Folders WHERE iID=$id" ) ) ) {
				$this->name = new SuperString( $folder['sTitle'] );
				$this->desc = new SuperString( $folder['sDescription'] );
				$this->pfid = $folder['iParentFolderID'];
				$this->security = $folder['iFolderType'];
				$this->group = $folder['iGroupID'];
				$this->type = $folder['iGroupType'];
				$this->semester = $folder['iSemesterID'];
			}
		}
		
		function setSemester($sem){
			$this->semester = $sem;
		}

		function setGroup($gid){
			$this->group = $gid;
		}

		function setType($type){
			$this->type = $type;
		}
				
		function getID() {
			return $this->id;
		}
		
		function getName() {
			if($this->name)
				return $this->name->getString();
			else
				return "Your Files";
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
		
		function isWriteOnly() {
			return ( $this->security == 1 );
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

		function isIPROFolder() {
			return !$this->group;
		}
		
		function getParentFolderID() {
			return $this->pfid;
		}
		
		function getParentFolder() {
			if ( $this->pfid == 0 )
				return false;
			else
				return new Folder( $this->pfid, $this->db );
		}
		
		function setParentFolderID( $id ) {
			$this->pfid=$id;
		}
		
		function getFolders() {
			$returnArray = array();
			
			if ( $this->getGroupType() == 0 && $this->getGroupID() != 0 ) {
				$folders = $this->db->igroupsQuery( "SELECT iID FROM Folders WHERE iParentFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle" );
			}
			else {
				$folders = $this->db->igroupsQuery( "SELECT iID FROM Folders WHERE iParentFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." ORDER BY sTitle" );
			}
			while ( $row = mysql_fetch_row( $folders ) ) {
				$returnArray[] = new Folder( $row[0], $this->db );
			}
			return $returnArray;
		}
		
		function getAllFolderIDs()
		{
			$topLevel = $this->getFolders();
			$allFolders = array();
			if(count($topLevel) > 0)
			{
				foreach($topLevel as $key => $val)
				{
					$allFolders[] = $val->getID();
					$allFolders = $allFolders + $val->getAllFolderIDs();
				}
			}
			return $allFolders;
		}
		
		function getFiles() {
			$returnArray = array();
			
			if ( $this->getGroupType() == 0 && $this->getGroupID() != 0 ) {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bObsolete=0 AND bDeletedFlag=0 AND iFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle" );
			}
			else {
				$files = $this->db->igroupsQuery( "SELECT iID FROM Files WHERE bObsolete=0 AND bDeletedFlag=0 AND iFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." ORDER BY sTitle" );
			}
			
			while ( $row = mysql_fetch_row( $files ) ) {
				$returnArray[] = new File( $row[0], $this->db );
			}
			return $returnArray;
		}
				
		function trash() {
			$subfolders = $this->getFolders();
			foreach ( $subfolders as $key => $folder ) {
				$folder->trash( $user, $group );
			}
			
			$files = $this->getFiles();
			foreach ( $files as $key => $file ) {
				$file->moveToTrash();
				$file->updateDB();
			}
			
			$this->db->igroupsQuery( "DELETE FROM Folders WHERE iID=".$this->getID() );
		}
		
		function delete() {
			$subfolders = $this->getFolders();
			foreach ( $subfolders as $key => $folder ) {
				$folder->delete( $user, $group );
			}
			
			$files = $this->getFiles();
			foreach ( $files as $key => $file ) {
				$file->delete();
				$file->updateDB();
			}
			
			$this->db->igroupsQuery( "DELETE FROM Folders WHERE iID=".$this->getID() );
		}
		
		function updateDB() {
			$this->db->igroupsQuery( "UPDATE Folders SET sTitle='".$this->getNameDB()."', sDescription='".$this->getDescDB()."', iParentFolderID=".$this->pfid." WHERE iID=".$this->id );
		}
	}
	
	function createFolder( $name, $desc, $security, $parent, $group, $db ) {
		$db->igroupsQuery( "INSERT INTO Folders( sTitle, sDescription, iFolderType, iParentFolderID, iGroupID, iGroupType, iSemesterID ) VALUES ( '$name', '$desc', $security, $parent, ".$group->getID().", ".$group->getType().", ".$group->getSemester()." )" );
	}
	
	function createIPROFolder( $name, $desc, $security, $parent, $db ) {
		$db->igroupsQuery( "INSERT INTO Folders( sTitle, sDescription, iFolderType, iParentFolderID ) VALUES ( '$name', '$desc', $security, $parent )" );
		return $db->igroupsInsertID();
	}
}
?>
