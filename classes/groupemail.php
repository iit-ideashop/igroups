<?php
include_once('superstring.php');

if(!class_exists('GroupEmail'))
{
	class GroupEmail
	{
		var $id, $to, $subject, $body, $sender, $senddate, $semester, $valid;
		var $db;
	
		function GroupEmail($id, $db)
		{
			$this->valid = false;
			if(!is_numeric($id))
				return;
			$this->id = $id;
			$this->db = $db;
			if($email = mysql_fetch_array($db->query("SELECT * FROM GroupEmails WHERE iID=$id")))
			{
				$this->to = $email['sTo'];
				$this->subject = new SuperString( $email['sSubject'] );
				$this->body = new SuperString( $email['sBody'] );
				$this->sender = $email['iSenderID'];
				$this->senddate = $email['dDate'];
				$this->semester = $email['iSemesterID'];
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
			if($x > 0  && $x < strlen($subj))
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
			$temp = explode('-', $this->senddate);
			return date('m/d/Y', mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]));
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
		
		function getSemester()
		{
			return $this->semester;
		}
		
		function delete()
		{
			$this->db->query("DELETE FROM GroupEmails WHERE iID=".$this->getID());
		}
		
		function updateDB()
		{
			$this->db->query("UPDATE GroupEmails SET sTo='".$this->getTo()."', sSubject='".$this->getSubjectDB()."', sBody='".$this->getBodyDB()."', iCategoryID=".$this->category." WHERE iID=".$this->id);
		}

		function getAttachments()
		{
			$query = $this->db->query("SELECT * FROM GroupEmailFiles WHERE iEmailID={$this->getID()}");
			$links = array();
			$i = 1;
			while($file = mysql_fetch_array($query))
			{
				$links[] = "<b>Attachment $i:</b> <a href='getattach.php?id={$file['iID']}&amp;email={$file['iEmailID']}'>{$file['sOrigName']}</a>";
				$i++;
			}
			return $links;
		}	

		function getAttachmentInfo()
		{
			$query = $this->db->query("SELECT * FROM GroupEmailFiles WHERE iEmailID={$this->getID()}");
			$files = array();
			while($file = mysql_fetch_array($query)) 
				$files[] = $file;
			return $files;
		}

		function hasAttachments()
		{
			$query = $this->db->query("SELECT * FROM GroupEmailFiles WHERE iEmailID={$this->getID()}");
			if(mysql_num_rows($query) > 0)
				return true;
			else
				return false;
		}
	}	
	
	function createGroupEmail($to, $subject, $body, $sender, $semester, $db)
	{
		$subj = new SuperString($subject);
		$bod = new SuperString($body);
		$db->query("INSERT INTO GroupEmails( sTo, sSubject, sBody, dDate, iSenderID, iSemesterID ) VALUES ( '".$to."', '".$subj->getDBString()."', '".$bod->getDBString()."', '".date("Y-m-d")."', $sender, $semester )");
		return new GroupEmail($db->insertID(), $db);
	}
}
?>
