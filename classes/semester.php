<?php
if(!class_exists('Semester'))
{
	class Semester
	{
		var $id, $name, $active, $valid, $db;
	
		function Semester($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			$semester = $db->query("SELECT * FROM Semesters WHERE iID=$id");
			if($row = mysql_fetch_array($semester))
			{
				$this->name = $row['sSemester'];
				$this->active = $row['bActiveFlag'] ? true : false;
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
		
		function getName()
		{
			return $this->name;
		}
		
		function setName($newname)
		{
			$this->name = $newname;
			$this->db->query('update Semesters set sSemester="'.mysql_real_escape_string(stripslashes($newname)).'" where iID='.$this->id);
		}
		
		function isActive()
		{
			return $this->active;
		}
		
		function setActive()
		{
			$q = $this->db->query('select iID from Semesters where bActiveFlag=1');
			if($q)
			{
				$r = mysql_fetch_row($q);
				$q = $r[0];
			}
			else
				return false;
			
			if($this->db->query('update Semesters set bActiveFlag=0'))
			{
				if($this->db->query('update Semesters set bActiveFlag=1 where iID='.$this->id))
				{
					$this->active = true;
					return true;
				}
				else
					$this->db->query("update Semesters set bActiveFlag=1 where iID=$q");
			}
			return false;
		}
		
		function getGroups()
		{
			$returnArray = array();

			$groups = $this->db->query("SELECT iProjectID FROM ProjectSemesterMap WHERE iSemesterID=".$this->id);
			while($row = mysql_fetch_row($groups))
				$returnArray[] = new Group($row[0], 0, $this->id, $this->db);
			
			return $returnArray;
		}
		
		function getCount()
		{
			$q = mysql_fetch_row($this->db->query("SELECT count(*) FROM ProjectSemesterMap WHERE iSemesterID=".$this->id));
			return $q[0];
		}
	}
	
	function createSemester($name, $active, $db)
	{
		if($active === true)
			$active = 1;
		else if($active === false)
			$active = 0;
		if($active != 1 && $active != 0)
			return false;
		$name = mysql_real_escape_string(stripslashes($name));
		if($active == 1)
			$db->query('update Semesters set bActiveFlag=0');
		if($db->query("insert into Semesters (sSemester, bActiveFlag) values (\"$name\", $active)"))
			return new Semester($db->insertID(), $db);
		else
			return false;
	}
}
?>
