<?php
if(!class_exists('Message'))
{
	class Message
	{
		var $id, $name, $text, $db;
		
		function Message($id, $db)
		{
			if(is_numeric($id))
			{
				$this->id = $id;
				$this->db = $db;
				$result = $this->db->query("select name, contents from Messages where id=$id");
				if($row = mysql_fetch_row($result))
				{
					$this->name = stripslashes($row[0]);
					$this->text = stripslashes($row[1]);
				}
			}
			else
				$this->id = -1;
		}

		function getID()
		{
			return $this->id;
		}

		function getName()
		{
			return $this->name;
		}

		function getText()
		{
			return $this->text;
		}

		function setName($name)
		{
			if($name != '')
				$this->name = $name;
		}

		function setText($text)
		{
			$this->text = $text;
		}

		function updateDB()
		{
			$nname = mysql_real_escape_string(stripslashes($this->name));
			$ntext = mysql_real_escape_string(stripslashes($this->text));
			$this->db->query("update Messages set name='$nname', contents='$ntext' where id={$this->id}");
		}	
	}
	
	function getAllMessages($db)
	{
		$messages = array();
		$query = $db->query('select * from Messages order by id');
		while($row = mysql_fetch_array($query))
			$messages[$row['id']] = new Message($row['id'], $db);
		return $messages;
	}
}
?>
