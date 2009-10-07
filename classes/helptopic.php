<?php
include_once('helpcat.php');

if(!class_exists('HelpTopic'))
{
	class HelpTopic
	{
		var $id, $cat, $title, $text, $valid, $db;
		
		function HelpTopic($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($topic = mysql_fetch_array($db->query("select * from HelpPages where iID=$id")))
			{
				$this->cat = new HelpCategory($topic['iCategoryID'], $db);
				$this->title = $topic['sTitle'];
				$this->text = $topic['sText'];
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
		
		function getCategory()
		{
			return $this->cat;
		}
		
		function getTitle()
		{
			return $this->title;
		}
		
		function getText()
		{
			return $this->text;
		}
		
		function setTitle($title)
		{
			$sqltitle = mysql_real_escape_string(stripslashes($title));
			if($title != '' && $this->db->query("update HelpPages set sTitle=\"$sqltitle\" where iID={$this->id}"))
			{
					$this->title = stripslashes($title);
					return true;
			}
			return false;
		}
		
		function setText($text)
		{
			$sqltext = mysql_real_escape_string(stripslashes($text));
			if($text != '' && $this->db->query("update HelpPages set sTitle=\"$sqltext\" where iID={$this->id}"))
			{
					$this->text = stripslashes($text);
					return true;
			}
			return false;
		}
		
		function assignTo($cat)
		{
			if(is_object($cat) && $cat->isValid() && $this->db->query("update HelpPages set iCategoryID={$cat->getID()} where iID={$this->id}"))
			{
				$this->cat = $cat;
				return true;
			}
			return false;
		}
		
		function delete()
		{
			if($this->db->query("delete from HelpPages where iID={$this->id}"))
			{
				$this->valid = false;
				return true;
			}
			return false;
		}
	}
	
	function createHelpTopic($title, $text, $cat, $db)
	{
		$sqltitle = mysql_real_escape_string(stripslashes($title));
		$sqltext = mysql_real_escape_string(stripslashes($text));
		if($title != '' && $text != '' && is_object($cat))
		{
			$db->query("insert into HelpPages (iCategoryID, sTitle, sText) values ({$cat->getID()}, \"$sqltitle\", \"$sqltext\")");
			return new HelpTopic($db->insertID(), $db);
		}
		return false;
	}
}
?>