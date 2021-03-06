<?php
include_once('superstring.php');
require_once('config.php');
if(!class_exists('File'))
{
	class File
	{
		var $id, $name, $desc, $folder, $author, $deleted, $group, $type, $semester, $origname, $date, $version, $mimeType, $obsolete, $private, $filesize, $valid;
		var $db;
		
		function File($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($file = mysql_fetch_array($db->query("SELECT * FROM Files WHERE iID=$id")))
			{
				$this->name = new SuperString( $file['sTitle'] );
				$this->desc = new SuperString( $file['sDescription'] );
				$this->origname = $file['sOriginalName'];
				$this->folder = $file['iFolderID'];
				$this->author = $file['iAuthorID'];
				$this->senddate = $file['dDate'];
				$this->deleted = $file['bDeletedFlag'];
				$this->group = $file['iGroupID'];
				$this->type = $file['iGroupType'];
				$this->semester = $file['iSemesterID'];
				$this->version = $file['iVersion'];
				$this->obsolete = $file['bObsolete'];
				$this->mimeType = $file['sMetaComments'];
				$this->private = $file['bPrivate'];
				$this->filesize = $file['iFileSize'];
				$this->valid = true;
			}
		}
		
		function getID()
		{
			return $this->id;
		}
		
		function getFilesize()
		{
			return $this->filesize;
		}
		
		function stringFilesize()
		{
			if($this->filesize < 1 << 10)
				return "{$this->filesize} bytes";
			else if($this->filesize < 1 << 20)
				return number_format($this->filesize / (1 << 10), 1).' KiB';
			else if($this->filesize < 1 << 30)
				return number_format($this->filesize / (1 << 20), 1).' MiB';
			else
				return number_format($this->filesize / (1 << 30), 1).' GiB';
		}

		function getVersion()
		{
			return $this->version;
		}

		function setVersion($ver)
		{
			$this->version = $ver; 
		}
			
		function getName()
		{
			if($this->getVersion() == 1)
				return $this->name->getString();
			else
				return $this->name->getString().' - v'.$this->getVersion();
		}
		
		function getNameNoVer()
		{
			return $this->name->getString();
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
		
		function getOriginalName()
		{
			return $this->origname;
		}
		
		function getFolderID()
		{
			return $this->folder;
		}
		
		function getFolder()
		{
			return new Folder($this->folder, $this->db);
		}
		
		function setPrivate($p)
		{
			$this->private = $p;	
		}

		function setFolderID($id)
		{
			$this->folder=$id;
		}
		
		function getAuthorID()
		{
			return $this->author;
		}
		
		function getAuthor()
		{
			return new Person($this->author, $this->db);
		}
		
		function getDate()
		{
			$date = substr($this->senddate, 0, strpos($this->senddate, ' '));
			$temp = explode('-', $date);
			return date('m/d/Y', mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]));
		}

		function getDateTime()
		{
			$date = substr($this->senddate, 0, strpos($this->senddate, ' '));
			$temp = explode('-', $date);
			$time = substr($this->senddate, strpos($this->senddate, ' ')+1, strlen($this->senddate));
			$temp2 = explode(':', $time);
			return date('Y-m-d h:m A', mktime($temp2[0], $temp2[1], $temp2[2], $temp[1], $temp[2], $temp[0]));
		}
		
		function getShortDateTime()
		{
			$date = substr($this->senddate, 0, strpos($this->senddate, ' '));
			$temp = explode('-', $date);
			$time = substr($this->senddate, strpos($this->senddate, ' ')+1, strlen($this->senddate));
			$temp2 = explode(':', $time);
			return timeAgoInWords(mktime($temp2[0], $temp2[1], $temp2[2], $temp[1], $temp[2], $temp[0]));
		}
	
		function getDateDB()
		{
			return $this->senddate;
		}
		
		function setDate($date)
		{
			$temp = explode('/', $date);
			if(count($temp) == 3)
				$this->senddate = date('Y-m-d', mktime(0, 0, 0, $temp[0], $temp[1], $temp[2]));
		}
		
		function isPrivate()
		{
			return $this->private;
		}

		function isInTrash()
		{
			return $this->deleted;
		}

		function isIPROFile()
		{
			return !$this->group;
		}
		
		function takeFromTrash()
		{
			$this->deleted = false;
		}
		
		function moveToTrash()
		{
			$this->deleted = true;
		}
		
		function makeObsolete()
		{
			$this->obsolete = 1;
		}

		function isObsolete()
		{
			return $this->obsolete();		
		}

		function delete()
		{
			if($this->getGroupID() != 0)
			{
				$quota = new Quota($this->getGroup(), $this->db);
				$quota->decreaseUsed(filesize($this->getDiskName()));
				$quota->updateDB();
			}
			unlink($this->getDiskName());
			$this->db->query("DELETE FROM Files WHERE iID={$this->getID()}");
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
		
		function getDiskName()
		{
			global $disk_prefix;
			return $disk_prefix."/{$this->id}.igroup";
		}

		function setMimeType($type)
		{
			$this->db->query("UPDATE Files SET sMetaComments='$type' WHERE iID={$this->id}");
			$this->mimeType = $type;
		}

		function getMimeType()
		{
			return $this->mimeType;
		}

		function isNuggetFile()
		{
			$count = 0;
			$results = $this->db->query("SELECT * FROM nuggetFileMap WHERE iFileID=$this->id");
			while($row = mysql_fetch_array($results))
				$count++;
			if($count != 0)
				return true;
			else
				return false;
		}

	       function getNugget()
	       {
			if($this->isNuggetFile())
			{
				$results = $this->db->query("SELECT iNuggetID FROM nuggetFileMap WHERE iFileID = $this->id");
				$row = mysql_fetch_array($results);
				$nugget = new Nugget($row['iNuggetID'], $this->db, 0);
				return $nugget;
			}
		}

		function updateDB()
		{
			$this->db->query( "UPDATE Files SET sTitle='".$this->getNameDB()."', sDescription='".$this->getDescDB()."', sOriginalName='".quickDBString( $this->origname )."', bObsolete=".$this->obsolete.", iFolderID=".$this->folder.", iVersion=".$this->version.", bDeletedFlag=".intval($this->deleted).", bPrivate=".$this->private." WHERE iID=".$this->id );
		}
	}
	
	function createFile($name, $desc, $folder, $author, $origname, $group, $tmp, $mime, $priv, $db)
	{
		global $contactemail, $disk_prefix;
		
		if($name == '')
			$name = $origname;
		$namess = new SuperString($name);
		$descss = new SuperString($desc);
		$dbdate = date('Y-m-d H:m:s');
		$db->query("INSERT INTO Files( sTitle, sDescription, iFolderID, iAuthorID, dDate, sOriginalName, iGroupID, iGroupType, iSemesterID, sMetaComments, bPrivate, iFileSize) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', $folder, $author, '$dbdate', '$origname', ".$group->getID().", ".$group->getType().", ".$group->getSemester().", '".mysql_real_escape_string($mime)."', $priv, ".filesize($tmp).")");
		$file = new File($db->insertID(), $db);
		if(disk_free_space($disk_prefix) > $file->getFilesize() && move_uploaded_file($tmp, $file->getDiskName()))
			return $file;
		else
		{
			$db->query("DELETE FROM Files WHERE iID=".$file->getID());
			if(disk_free_space($disk_prefix) <= $file->getFilesize())
			{
				mail($contactemail, "iGroups Uploaded Files Directory Out Of Space", "A user tried to upload a file to iGroups, and could not because the directory in which to place the file lacks enough free space to complete the transaction. You should fix this.\n\nTimestamp: ".date('Y-m-d H:i:s')."\nAttempt upload (bytes): ".$file->getFilesize()."\nFree space (bytes): ".disk_free_space($disk_prefix));
				return 1; //Disk full
			}
			else
				return 2; //Unspecified error (move_uploaded_file returned false)
		}
	}
	
	function createIPROFile($name, $desc, $folder, $author, $origname, $db)
	{
		if($name == '')
			$name = $origname;
		$namess = new SuperString($name);
		$descss = new SuperString($desc);
		$dbdate = date('Y-m-d H:m:s');
		$db->query("INSERT INTO Files( sTitle, sDescription, iFolderID, iAuthorID, dDate, sOriginalName ) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', $folder, $author, '$dbdate', '$origname' )");
		return new File( $db->insertID(), $db );
	}

	//digital drop box
	function createDDBFile($name, $desc, $folder, $author, $origname, $group, $db)
	{
		if($name == '')
			$name = $origname;
		$namess = new SuperString($name);
		$descss = new SuperString($desc);
		$dbdate = date('Y-m-d H:m:s');
		$db->query("INSERT INTO Files( sTitle, sDescription, iFolderID, iAuthorID, dDate, sOriginalName, iGroupID, iGroupType, iSemesterID ) VALUES ( '".$namess->getDBString()."', '".$descss->getDBString()."', $folder, $author, '$dbdate', '$origname', ".$group->getID().", ".$group->getType().", ".$group->getSemester().")");
		return new File($db->insertID(), $db);
	}
}
?>
