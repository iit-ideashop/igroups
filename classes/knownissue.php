<?php
if(!class_exists('KnownIssue'))
{
	class KnownIssue
	{
		var $id, $issue, $resolved, $valid, $db;
		
		function KnownIssue($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($cat = mysql_fetch_array($db->query("select * from KnownIssues where iID=$id")))
			{
				$this->issue = $cat['sIssue'];
				$this->resolved = $cat['bResolved'] ? true : false;
				$this->valid = true;
			}
		}
		
		function isValid()
		{
			return $this->valid;
		}
		
		function isResolved()
		{
			return $this->resolved;
		}
		
		function getID()
		{
			return $this->id;
		}
		
		function getIssue()
		{
			return $this->issue;
		}
		
		function setResolved($res)
		{
			$this->resolved = $res ? true : false;
			$this->db->query("update KnownIssues set bResolved=".($res ? '1' : '0')." where iID={$this->id}");
		}
		
		function setIssue($issue)
		{
			$sqlissue = mysql_real_escape_string(stripslashes($issue));
			if($issue != '' && $this->db->query("update KnownIssues set sIssue=\"$sqlissue\" where iID={$this->id}"))
			{
					$this->issue = stripslashes($issue);
					return true;
			}
			return false;
		}
		
		function delete()
		{
			if($this->db->query("delete from KnownIssues where iID={$this->id}"))
			{
				$this->valid = false;
				return true;
			}
			return false;
		}
	}
	
	function createKnownIssue($issue, $db)
	{
		$issue = mysql_real_escape_string(stripslashes($issue));
		if($issue != '')
		{
			$db->query("insert into KnownIssues (sIssue) values (\"$issue\")");
			return new KnownIssue($db->insertID(), $db);
		}
		return false;
	}
	
	function getAllIssues($db)
	{
		$issues = array();
		$query = $db->query('select iID from KnownIssues');
		while($row = mysql_fetch_row($query))
			$issues[$row[0]] = new KnownIssue($row[0], $db);
		return $issues;
	}
	
	function getAllUnresolvedIssues($db)
	{
		$issues = array();
		$query = $db->query('select iID from KnownIssues where bResolved=0');
		while($row = mysql_fetch_row($query))
			$issues[$row[0]] = new KnownIssue($row[0], $db);
		return $issues;
	}
}
?>