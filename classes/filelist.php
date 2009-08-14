<?php
include_once('superstring.php');

if(!class_exists('FileList'))
{
	class FileList
	{
		var $id, $name, $basefolder, $db, $valid;
		
		function FileList($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($row = mysql_fetch_array($db->query("SELECT * FROM FileLists WHERE iID=$id")))
			{
				$this->basefolder = $row['iBaseFolder'];
				$this->name = new SuperString( $row['sListName'] );
				$this->valid = true;
			}
		}
		
		function isValid()
		{
			return $this->valid;
		}
	
		function getID()
		{
			return $this->id;
		}
		
		function getBaseFolder()
		{
			return new Folder( $this->basefolder, $this->db );
		}
		
		function getName()
		{
			return $this->name->getString();
		}
		
		function delete()
		{
			$folder = $this->getBaseFolder();
			$folder->delete();
			$this->db->query("DELETE FROM FileLists WHERE iID=".$this->id);
		}
	}
	
	function createFileList($name, $basefolder, $db)
	{
		$namess = new SuperString($name);
		$db->query("INSERT INTO FileLists ( sListName, iBaseFolder ) VALUES ( '".$namess->getDBString()."', $basefolder )");
		return $db->insertID();
	}
}
