<?php
if ( !class_exists( "GroupPicture" ) ) {
	class GroupPicture {
		var $id, $ext, $title, $group, $type, $semester, $db;
		function GroupPicture( $id, $db ) {
			$this->id = $id;
			$this->db = $db;
			$pic = $db->igroupsQuery( "SELECT * FROM Pictures WHERE iID=$id" );
			if ( $row = mysql_fetch_array( $pic ) ) {
				$this->ext = $row['sExtension'];
				$this->group = $row['iGroupID'];
				$this->type = $row['iGroupType'];
				$this->semester = $row['iSemesterID'];
				$this->title = $row['sTitle'];
			}
		}
			
		function getID() {
			return $this->id;
		}
		
		function getExtension() {
			return $this->ext;
		}
		
		function getTitle() {
			return $this->title;
		}

		function getDiskName() {
			return "/srv/igroups/group-pics/".$this->getID().".".$this->getExtension();
		}
		
		function getRelativeName() {
			return "group-pics/".$this->getID().".".$this->getExtension();
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
			unlink( $this->getDiskName() );
			$this->db->igroupsQuery( "DELETE FROM Pictures WHERE iID=".$this->getID() );
		}
	}
	
	function createGroupPicture( $fileName, $title, $group, $db ) {
		$ext = explode( ".", $fileName );
		$extension = $ext[count($ext)-1];
		if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'gif' && $extension != 'bmp' && $extension != 'png')
			die();
		$date = date("Y-m-d");
		if ($title == '')
			$db->igroupsQuery( "INSERT INTO Pictures( sExtension, iGroupID, iGroupType, iSemesterID, dDate, sTitle ) VALUES ( '".$ext[count($ext)-1]."', ".$group->getID().", ".$group->getType().", ".$group->getSemester().", '".$date."', '(Untitled)' )" );
		else
			$db->igroupsQuery( "INSERT INTO Pictures( sExtension, iGroupID, iGroupType, iSemesterID, dDate, sTitle ) VALUES ( '".$ext[count($ext)-1]."', ".$group->getID().", ".$group->getType().", ".$group->getSemester().", '".$date."', '{$title}' )" );
		return new GroupPicture( $db->igroupsInsertID(), $db );
	}
}
