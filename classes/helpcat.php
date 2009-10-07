<?php
include_once('helptopic.php');

if(!class_exists('HelpCategory'))
{
	class HelpCategory
	{
		var $id, $title, $valid, $db;
		
		function HelpCategory($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($cat = mysql_fetch_array($db->query("select * from HelpCategories where iID=$id")))
			{
				$this->title = $cat['sTitle'];
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
		
		function getTitle()
		{
			return $this->title;
		}
		
		function setTitle($title)
		{
			$sqltitle = mysql_real_escape_string(stripslashes($title));
			if($title != '' && $this->db->query("update HelpCategories set sTitle=\"$sqltitle\" where iID={$this->id}"))
			{
					$this->title = stripslashes($title);
					return true;
			}
			return false;
		}
		
		function getAllTopics()
		{
			$topics = array();
			$query = $this->db->query("select * from HelpPages where iCategoryID={$this->id}");
			while($row = mysql_fetch_array($query))
				$topics[$row['iID']] = new HelpTopic($row['iID'], $this->db);
			return $topics;
		}
		
		function delete()
		{ //Also deletes child help topics
			if($this->db->query("delete from HelpPages where iCategoryID={$this->id}") && $this->db->query("delete from HelpCategories where iID={$this->id}"))
			{
				$this->valid = false;
				return true;
			}
			return false;
		}
	}
	
	function createHelpCategory($title, $db)
	{
		$title = mysql_real_escape_string(stripslashes($title));
		if($title != '' && !mysql_num_rows($db->query("select * from HelpCategories where sTitle=\"$title\"")))
		{
			$db->query("insert into HelpCategories (sTitle) values (\"$title\")");
			return new HelpCategory($db->insertID(), $db);
		}
		return false;
	}
	
	function getAllHelpCategories($db)
	{
		$cats = array();
		$query = $db->query("select * from HelpCategories order by sTitle");
		while($row = mysql_fetch_array($query))
			$cats[$row['iID']] = new HelpCategory($row['iID'], $db);
		return $cats;
	}
}
?>