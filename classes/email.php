<?php
include_once('superstring.php');
require_once('config.php');

if(!class_exists('Email'))
{
	class Email
	{
		var $id, $to, $subject, $body, $sender, $senddate, $category, $group, $type, $semester, $next, $prev, $valid;
		var $db;
	
		function Email($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			$query = $db->query("SELECT * FROM Emails WHERE iID=$id");
			if(!mysql_num_rows($query))
				return;
			else if($email = mysql_fetch_array($query))
			{
				$this->to = $email['sTo'];
				$this->subject = new SuperString( $email['sSubject'] );
				$this->body = new SuperString( $email['sBody'] );
				$this->category = $email['iCategoryID'];
				$this->sender = $email['iSenderID'];
				$this->senddate = $email['dDate'];
				$this->group = $email['iGroupID'];
				$this->type = $email['iGroupType'];
				$this->semester = $email['iSemesterID'];
				$this->next = $email['iNextID'];
				$this->prev = $email['iPrevID'];
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

		function getTo()
		{
			return $this->to;
		}
			
		function getNextID()
		{
			return $this->next;
		}

		function getPrevID()
		{
			return $this->prev;
		}

		function setNextID($id)
		{
			$this->next = $id;
		}

		function setPrevID($id)
		{
			$this->prev = $id;
		}

		function getSubject()
		{
			return $this->subject->getString();
		}
		
		function getSubjectDB()
		{
			return $this->subject->getDBString();
		}
		
		function getSubjectHTML()
		{
			return $this->subject->getHTMLString();
		}
		
		function getSubjectJava()
		{
			return $this->subject->getJavaString();
		}
		
		function getShortSubject()
		{
			$subj = $this->subject->getString();
			if(strlen($subj) < 40)
				return $subj;
			$x = strpos($subj, ' ', 40);
			if($x > 0 && $x < strlen($subj))
				return substr($subj, 0, $x).'...';
			return $subj;
		}
		
		function setSubject($string)
		{
			if($string != '')
				$this->subject->setString($string);
		}
		
		function getBody()
		{
			return $this->body->getString();
		}
		
		function getBodyDB()
		{
			return $this->body->getDBString();
		}
		
		function getBodyHTML()
		{
			return $this->body->getHTMLString();
		}
		
		function getBodyJava()
		{
			return $this->body->getJavaString();
		}
		
		function getReplyBody()
		{
			$replybody = "\n".$this->getBody();
			$replybody = str_replace("\n", "\n>", $replybody);
			return $replybody;
		}

		function getAttachments()
		{
			$query = $this->db->query("SELECT * FROM EmailFiles WHERE iEmailID={$this->getID()}");
			$links = array();
			$i = 1;
			while($file = mysql_fetch_array($query))
			{
				$links[] = "<b>Attachment $i:</b> <a href=\"getattach.php?id={$file['iID']}&amp;email={$file['iEmailID']}\">{$file['sOrigName']}</a>";
				$i++;
			}
			return $links;
		}	

		function getAttachmentInfo()
		{
			$query = $this->db->query("SELECT * FROM EmailFiles WHERE iEmailID={$this->getID()}");
			$files = array();
			while ($file = mysql_fetch_array($query)) 
				$files[] = $file;
			return $files;
		}

		function hasAttachments()
		{
			$query = $this->db->query("SELECT * FROM EmailFiles WHERE iEmailID={$this->getID()}");
			if(mysql_num_rows($query) > 0) 
				return true;
			else
				return false;
		}	

		function setTo($to)
		{
			if($string != '')
				$this->to = $string;
		}

		function setBody($string)
		{
			if($string != '')
				$this->body->setString($string);
		}
		
		function getSenderID()
		{
			return $this->sender;
		}
		
		function getSender()
		{
			return new Person($this->sender, $this->db);
		}	
		
		function getDate()
		{
			$year = substr($this->senddate, 0, 4);
			$month = substr($this->senddate, 5, 2);
			$day = substr($this->senddate, 8, 2);
			
			return date('m/d/Y', mktime(0, 0, 0, $month, $day, $year));
		}	

		function getDateTime()
		{
			$year = substr($this->senddate, 0, 4);
			$month = substr($this->senddate, 5, 2);
			$day = substr($this->senddate, 8, 2);
			$hour = substr($this->senddate, 11, 2);
			$min = substr($this->senddate, 14, 2);
			$sec = substr($this->senddate, 17, 2);
			return date('Y-m-d h:m A', mktime($hour, $min, $sec, $month, $day, $year));
		}
		
		function getShortDateTime()
		{
			$year = substr($this->senddate, 0, 4);
			$month = substr($this->senddate, 5, 2);
			$day = substr($this->senddate, 8, 2);
			$hour = substr($this->senddate, 11, 2);
			$min = substr($this->senddate, 14, 2);
			$sec = substr($this->senddate, 17, 2);
			return timeAgoInWords(mktime( $hour, $min, $sec, $month, $day, $year));
		}

		function getDateDB()
		{
			return $this->senddate;
		}
		
		function setDate($date)
		{
			$year = substr($date, 0, 4);
			$month = substr($date, 5, 2);
			$day = substr($date, 8, 2);
			$hour = substr($date, 11, 2);
			$min = substr($date, 14, 2);
			$sec = substr($date, 17, 2);
			$this->senddate = date('Y-m-d H:i:s', mktime($hour, $min, $sec, $month, $day, $year));
		}
		
		function getCategoryID()
		{
			return $this->category;
		}
		
		function getCategory()
		{
			return new Category($this->category, $this->db);
		}
		
		function setCategory($id)
		{
			$this->category = $id;
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
		
		function delete()
		{
			global $disk_prefix;
			if($this->next!=null && $this->prev != null)
			{
				$next = new Email($this->next, $this->db);
				$prev = new Email($this->prev, $this->db);
				$next->setPrevID($prev->getID());
				$prev->setNextID($next->getID());
				$next->updateDB();
				$prev->updateDB();
			}
			else if($this->next!=null && $this->prev == null)
			{
				$next = new Email($this->next, $this->db);
				$next->setPrevID(null);
				$next->updateDB();
			}
			else if($this->prev != null && $this->next == null)
			{
				$prev = new Email($this->prev, $this->db);
				$prev->setNextID(null);
				$prev->updateDB();
			}
			$this->db->query("DELETE FROM Emails WHERE iID=".$this->getID());
			$query = $this->db->query( "SELECT * FROM EmailFiles WHERE iEmailID={$this->getID()}");
			while($file = mysql_fetch_array($query))
			{
				if($file['sDiskName'][0] != 'G' && file_exists("$disk_prefix/emails/{$file['sDiskName']}"))
					unlink("$disk_prefix/emails/{$file['sDiskName']}");
			}
			$this->db->query("DELETE FROM EmailFiles WHERE iEmailID={$this->getID()}");
		}
		
		function updateDB()
		{
			if(!$this->next)
				$nextID = 'NULL';
			else
				$nextID = $this->next;
			if(!$this->prev)
				$prevID = 'NULL';
			else
				$prevID = $this->prev;
			$this->db->query( "UPDATE Emails SET sTo='{$this->to}', sSubject='".$this->getSubjectDB()."', sBody='".$this->getBodyDB()."', iCategoryID=".$this->category.", iNextID=$nextID, iPrevID=$prevID WHERE iID=".$this->id );
		}
	}
	
	function createEmail($to, $subject, $body, $sender, $category, $reply, $group, $type, $semester, $db)
	{
		$subj = new SuperString($subject);
		$bod = new SuperString($body);
		$tto = new SuperString($to);
		$db->query("INSERT INTO Emails( sTo, sSubject, sBody, dDate, iSenderID, iCategoryID, iReplyID, iGroupID, iGroupType, iSemesterID ) VALUES ( '".$tto->getDBString()."', '".$subj->getDBString()."', '".$bod->getDBString()."', '".date("Y-m-d H-i-s")."', ".$sender.", $category, $reply, $group, $type, $semester )" );
		$email = new Email($db->insertID(), $db);
		if($reply != 0)
		{
			$id = $email->getID();
			$newPrev = findLastInThread($reply);
			$db->query("UPDATE Emails SET iPrevID=$newPrev WHERE iID=$id");
			$db->query("UPDATE Emails SET iNextID=$id WHERE iID=$newPrev");
		}

		return $email;
	}

	function findLastInThread($emailID)
	{
		$query = mysql_query("SELECT iNextID FROM Emails where iID=$emailID");
		$result = mysql_fetch_row($query);
		if ($result[0] != null)
			return findLastInThread($result[0]);
		else
			return $emailID;
	}
}
?>
