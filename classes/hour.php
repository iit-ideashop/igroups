<?php
require_once('person.php');

if(!class_exists('Hour'))
{
	class Hour
	{
		var $id, $db, $task, $person, $hours, $date, $desc, $valid;
		
		function Hour($id, $db)
		{
			$this->valid = false;
			if(is_numeric($id))
			{
				$query = $db->query("select * from Hours where iID=$id");
				if($result = mysql_fetch_array($query))
				{
					$this->id = $id;
					$this->db = $db;
					$this->task = $result['iTaskID'];
					$this->person = new Person($result['iPersonID'], $db);
					$this->hours = $result['fHours'];
					$this->date = $result['dDate'];
					$this->desc = $result['sDesc'];
					$this->valid = true;
				}
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
		
		function getTaskID()
		{
			return $this->task;
		}
		
		function getPerson()
		{
			return $this->person;
		}
		
		function getHours()
		{
			return $this->hours;
		}
		
		function getDate()
		{
			return $this->date;
		}
		
		function getDesc()
		{
			return stripslashes($this->desc);
		}
		
		function setHours($n)
		{
			if($this->db->query("update Hours set fHours=$n where iID={$this->id}"))
				$this->hours = $n;
			return ($this->hours == $n);
		}
		function setDate($n)
		{
			$n = mysql_real_escape_string(stripslashes($n));
			if($this->db->query("update Hours set dDate=\"$n\" where iID={$this->id}"))
				$this->date = $n;
			return ($this->date == $n);
		}
		
		function setDesc($n)
		{
			$nsql = mysql_real_escape_string(stripslashes($n));
			if($this->db->query("update Hours set sDesc=\"$nsql\" where iID={$this->id}"))
				$this->desc = $n;
			return ($this->desc == $n);
		}
	}
}
?>
