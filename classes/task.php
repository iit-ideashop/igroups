<?php
require_once('person.php');
require_once('group.php');
require_once('subgroup.php');
require_once('hour.php');

if(!class_exists('Task'))
{
	class Task
	{
		var $id, $db, $name, $desc, $team, $creator, $created, $due, $closed, $valid;
		
		function Task($id, $type, $sem, $db)
		{
			$this->valid = false;
			if(is_numeric($id))
			{
				$query = $db->igroupsQuery("select * from Tasks where iID=$id");
				if($result = mysql_fetch_array($query))
				{
					$this->id = $id;
					$this->db = $db;
					$this->name = $result['sName'];
					$this->desc = $result['sDescription'];
					$this->team = new Group($result['iTeamID'], $type, $sem, $db);
					$this->creator = new Person($result['iOwnerID'], $db);
					$this->created = $result['dCreated'];
					$this->due = $result['dDue'];
					$this->closed = $result['dClosed'];
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
		
		function getName()
		{
			return $this->name;
		}
		
		function getDesc()
		{
			return $this->desc;
		}
		
		function getTeam()
		{
			return $this->team;
		}
		
		function getCreator()
		{
			return $this->creator;
		}
		
		function getCreated()
		{
			return $this->created;
		}
		
		function getDue()
		{
			return $this->due;
		}
		
		function getClosed()
		{
			return $this->closed;
		}
		
		function setName($n)
		{
			$n = mysql_real_escape_string(stripslashes($n));
			if($this->db->igroupsQuery("update Tasks set sName=\"$n\" where iID={$this->id}"))
				$this->name = $n;
			return ($this->name == $n);
		}
		
		function setDesc($n)
		{
			$n = mysql_real_escape_string(stripslashes($n));
			if($this->db->igroupsQuery("update Tasks set sDescription=\"$n\" where iID={$this->id}"))
				$this->desc = $n;
			return ($this->desc == $n);
		}
		function setDue($n)
		{
			$sqldate = date('Y-m-d', $n);
			if($this->db->igroupsQuery("update Tasks set dDue=\"$sqldate\" where iID={$this->id}"))
				$this->due = $sqldate;
			return ($this->due == $sqldate);
		}
		
		function setClosed($n)
		{
			$sqldate = date('Y-m-d', $n);
			if($this->db->igroupsQuery("update Tasks set dClosed=\"$sqldate\" where iID={$this->id}"))
				$this->closed = $sqldate;
			return ($this->closed == $sqldate);
		}
		
		function isOverdue()
		{
			return (!$this->closed && strtotime($this->due) <= time());
		}
		
		function delete()
		{
			$this->db->igroupsQuery('delete from TaskAssignments where iTaskID='.$this->id);
			$this->db->igroupsQuery('delete from TaskSubgroupAssignments where iTaskID='.$this->id);
			$this->db->igroupsQuery('delete from Milestones where iTaskID='.$this->id);
			$this->db->igroupsQuery('delete from Hours where iTaskID='.$this->id);
			$this->db->igroupsQuery('delete from Tasks where iID='.$this->id);
		}
		
		function getAssignedPeople()
		{
			$query = $this->db->igroupsQuery("select * from TaskAssignments where iTaskID={$this->id}");
			$people = array();
			while($result = mysql_fetch_array($query))
				$people[$result['iPersonID']] = new Person($result['iPersonID'], $this->db);
			return $people;
		}
		
		function getAssignedSubgroups()
		{
			$query = $this->db->igroupsQuery("select * from TaskSubgroupAssignments where iTaskID={$this->id}");
			$sgs = array();
			while($result = mysql_fetch_array($query))
				$sgs[$result['iSubgroupID']] = new SubGroup($result['iSubgroupID'], $this->db);
			return $sgs;
		}
		
		function assignPerson($p)
		{
			if(!$this->isAssignedPerson($p))
				$this->db->igroupsQuery("insert into TaskAssignments (iTaskID, iPersonID) values ({$this->id}, {$p->getID()})");
		}
		
		function assignSubgroup($s)
		{
			if(!$this->isAssignedSubgroup($s))
				$this->db->igroupsQuery("insert into TaskSubgroupAssignments (iTaskID, iSubgroupID) values ({$this->id}, {$s->getID()})");
		}
		
		function deassignPerson($p)
		{
			if($this->isAssignedPerson($p))
				$this->db->igroupsQuery("delete from TaskAssignments where iTaskID={$this->id} and iPersonID={$p->getID()})");
		}
		
		function deassignSubgroup($s)
		{
			if($this->isAssignedSubgroup($p))
				$this->db->igroupsQuery("delete from TaskSubgroupAssignments where iTaskID={$this->id} and iSubgroupID={$s->getID()})");
		}
		
		function isAssignedPerson($p)
		{
			$query = $this->db->igroupsQuery("select * from TaskAssignments where iTaskID={$this->id} and iPersonID={$p->getID()}");
			return (mysql_num_rows($query) ? true : false);
		}
		
		function isAssignedSubgroup($s)
		{
			$query = $this->db->igroupsQuery("select * from TaskSubgroupAssignments where iTaskID={$this->id} and iSubgroupID={$s->getID()}");
			return (mysql_num_rows($query) ? true : false);
		}
		
		function isAssigned($p)
		{
			if($this->isAssignedPerson($p))
				return true;
			$query = $db->igroupsQuery('select * from TaskSubgroupAssignments where iTaskID='.$this->id);
			while($row = mysql_fetch_array($query))
			{
				$sg = new SubGroup($row['iSubgroupID'], $this->db);
				if($sg->isSubGroupMember($p))
					return true;
			}
			return false;
		}
		
		function getHours($person)
		{
			$hours = array();
			$query = $this->db->igroupsQuery("select * from Hours where iTaskID={$this->id} and iPersonID={$person->getID()} order by dDate asc");
			while($result = mysql_fetch_array($query))
				$hours[$result['iID']] = new Hour($result['iID'], $db);
			return $hours;
		}
		
		function getTotalHours()
		{
			$query = mysql_fetch_row($this->db->igroupsQuery("select sum(fHours) from Hours where iTaskID={$this->id}"));
			return $query[0];
		}
		
		function getTotalHoursFor($person)
		{
			$query = mysql_fetch_row($this->db->igroupsQuery("select sum(fHours) from Hours where iTaskID={$this->id} and iPersonID={$person->getID()}"));
			return $query[0];
		}
		
		function setHours($date, $person, $hours)
		{
			$sqldate = date('Y-m-d', $date);
			if(!is_numeric($hours) || $hours < 0)
				return false;
			$date = mysql_real_escape_string(stripslashes($date));
			$query = $this->db->igroupsQuery("select * from Hours where iTaskID={$this->id} and iPersonID={$person->getID()} and dDate=\"$sqldate\"")
			$result = mysql_fetch_array($query);
			if($result && $hours > 0)
				return $this->db->igroupsQuery("update Hours set fHours=$hours where iID={$result['iID']}");
			else if($result && $hours == 0)
				return $this->db->igroupsQuery("delete from Hours where iID={$result['iID']}");
			else
				return $this->db->igroupsQuery("insert into Hours (iTaskID, iPersonID, dDate, fHours) values ({$this->id}, {$person->getID()}, \"$sqldate\", $hours)");
		}
	}
}
?>
