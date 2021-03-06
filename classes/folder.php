<?php
include_once('superstring.php');
include_once('sort.php');

if(!class_exists('Folder'))
{
	class Folder
	{
		var $id, $name, $desc, $pfid, $security, $group, $type, $semester, $valid;
		var $db;
		
		function Folder($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($folder = mysql_fetch_array($db->query("SELECT * FROM Folders WHERE iID=$id")))
			{
				$this->name = new SuperString($folder['sTitle']);
				$this->desc = new SuperString($folder['sDescription']);
				$this->pfid = $folder['iParentFolderID'];
				$this->security = $folder['iFolderType'];
				$this->group = $folder['iGroupID'];
				$this->type = $folder['iGroupType'];
				$this->semester = $folder['iSemesterID'];
				$this->valid = true;
			}
		}
		
		function isValid()
		{
			return $this->valid;
		}
		
		function setSemester($sem)
		{
			$this->semester = $sem;
		}

		function setGroup($gid)
		{
			$this->group = $gid;
		}

		function setType($type)
		{
			$this->type = $type;
		}
				
		function getID()
		{
			return $this->id;
		}
		
		function getName()
		{
			if($this->name)
				return $this->name->getString();
			else
				return 'Your Files';
		}
		
		function getNameDB()
		{
			return $this->name->getDBString();
		}
		
		function getNameHTML()
		{
			return $this->name->getHTMLString();
		}
		
		function getNameJava()
		{
			return $this->name->getJavaString();
		}
		
		function setName($string)
		{
			if($string != '')
				$this->name->setString($string);
		}
		
		function getDesc()
		{
			return $this->desc->getString();
		}
		
		function getDescDB()
		{
			return $this->desc->getDBString();
		}
		
		function getDescHTML()
		{
			return $this->desc->getHTMLString();
		}
		
		function getDescJava()
		{
			return $this->desc->getJavaString();
		}
		
		function setDesc($string)
		{
			if($string != '')
				$this->desc->setString($string);
		}
		
		function isWriteOnly()
		{
			return ($this->security == 1);
		}
		
		function getGroupID()
		{
			return $this->group;
		}
		
		function getGroupType()
		{
			return $this->type;
		}
		
		function getSemester()
		{
			return $this->semester;
		}
		
		function getGroup()
		{
			return new Group($this->getGroupID(), $this->getGroupType(), $this->getSemester(), $this->db);
		}

		function isIPROFolder()
		{
			return !$this->group;
		}
		
		function getParentFolderID()
		{
			return $this->pfid;
		}
		
		function getParentFolder()
		{
			if($this->pfid == 0)
				return false;
			else
				return new Folder($this->pfid, $this->db);
		}
		
		function setParentFolderID($id)
		{
			$this->pfid = $id;
		}

		function getFolders()
		{
			$returnArray = array();
			if(!is_numeric($this->getID()) || !is_numeric($this->getGroupID()) || !is_numeric($this->getGroupType()))
				return array();
			if($this->getGroupType() == 0 && $this->getGroupID() != 0)
				$folders = $this->db->query("SELECT iID FROM Folders WHERE iParentFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle");
			else
				$folders = $this->db->query("SELECT iID FROM Folders WHERE iParentFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." ORDER BY sTitle");
			while($row = mysql_fetch_row($folders))
				$returnArray[] = new Folder($row[0], $this->db);
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
					$allFolders = array_merge($allFolders, $val->getAllFolderIDs());
				}
			}
			return $allFolders;
		}
		
		function getFiles()
		{
			$returnArray = array();
			
			if($this->getGroupType() == 0 && $this->getGroupID() != 0)
				$files = $this->db->query("SELECT iID FROM Files WHERE bObsolete=0 AND bPrivate=0 AND bDeletedFlag=0 AND iFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." AND iSemesterID=".$this->getSemester()." ORDER BY sTitle");
			else
				$files = $this->db->query("SELECT iID FROM Files WHERE bObsolete=0 AND bPrivate=0 AND bDeletedFlag=0 AND iFolderID=".$this->getID()." AND iGroupID=".$this->getGroupID()." AND iGroupType=".$this->getGroupType()." ORDER BY sTitle");
			
			while($row = mysql_fetch_row($files))
				$returnArray[] = new File( $row[0], $this->db );
			return $returnArray;
		}
		
		function getFilesSortedBy($sort)
		{
			$returnArray = array();
			$add = decodeFileSort($sort);
			
			if($this->getGroupType() == 0 && $this->getGroupID() != 0)
				$files = $this->db->query("SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bObsolete=0 AND Files.bPrivate=0 AND Files.bDeletedFlag=0 AND Files.iFolderID=".$this->getID()." AND Files.iGroupID=".$this->getGroupID()." AND Files.iGroupType=".$this->getGroupType()." AND Files.iSemesterID=".$this->getSemester().$add);
			else
				$files = $this->db->query("SELECT Files.iID, People.sFName, People.sLName FROM Files inner join People on Files.iAuthorID=People.iID WHERE Files.bObsolete=0 AND Files.bPrivate=0 AND Files.bDeletedFlag=0 AND Files.iFolderID=".$this->getID()." AND Files.iGroupID=".$this->getGroupID()." AND Files.iGroupType=".$this->getGroupType().$add);
			
			while($row = mysql_fetch_row($files))
				$returnArray[] = new File($row[0], $this->db);
			return $returnArray;
		}
				
		function trash()
		{
			$subfolders = $this->getFolders();
			foreach($subfolders as $key => $folder)
				$folder->trash( $user, $group );
			
			$files = $this->getFiles();
			foreach($files as $key => $file)
			{
				$file->moveToTrash();
				$file->updateDB();
			}
			
			$this->db->query("DELETE FROM Folders WHERE iID=".$this->getID());
		}
		
		function delete()
		{
			$subfolders = $this->getFolders();
			foreach($subfolders as $key => $folder)
				$folder->delete($user, $group);
			
			$files = $this->getFiles();
			foreach($files as $key => $file)
			{
				$file->delete();
				$file->updateDB();
			}
			
			$this->db->query("DELETE FROM Folders WHERE iID=".$this->getID());
		}
		
		function updateDB()
		{
			$this->db->query("UPDATE Folders SET sTitle='".$this->getNameDB()."', sDescription='".$this->getDescDB()."', iParentFolderID=".$this->pfid." WHERE iID=".$this->id);
		}
	}
	
	function createFolder($name, $desc, $security, $parent, $group, $db)
	{
		if(!is_numeric($security) || !is_numeric($parent))
			return;
		$name = mysql_real_escape_string(stripslashes($name));
		$desc = mysql_real_escape_string(stripslashes($desc));
		$db->query("INSERT INTO Folders( sTitle, sDescription, iFolderType, iParentFolderID, iGroupID, iGroupType, iSemesterID ) VALUES ( '$name', '$desc', $security, $parent, ".$group->getID().", ".$group->getType().", ".$group->getSemester()." )");
	}
	
	function createIPROFolder($name, $desc, $security, $parent, $db)
	{
		if(!is_numeric($security) || !is_numeric($parent))
			return;
		$name = mysql_real_escape_string(stripslashes($name));
		$desc = mysql_real_escape_string(stripslashes($desc));
		$db->query("INSERT INTO Folders( sTitle, sDescription, iFolderType, iParentFolderID ) VALUES ( '$name', '$desc', $security, $parent )");
		return $db->insertID();
	}
}
?>
