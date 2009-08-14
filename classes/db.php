<?php
if(!class_exists('dbConnection'))
{
	class dbConnection
	{
		var $conn;
		
		function dbConnection()
		{
			$db_name = 'igroups';
			$db_user = 'igr0upsUser';
			$db_pass = 'mxYwnc36';

			$this->conn = mysql_connect('localhost', $db_user, $db_pass) or
				die('Could not connect: '.mysql_error());
					
			mysql_select_db($db_name, $this->conn) or
				die('Could not select DB: '.mysql_error());
		}
		
		function query($query)
		{
			$query_db_result = mysql_query($query, $this->conn) or
				die("Invalid query:\n<br>\n$query<br>\n".mysql_error($this->conn));
			return $query_db_result;
		}
		
		function iknowQuery($query) //Deprecated
		{
			return $this->query($query);
		}
		
		function igroupsQuery($query) //Deprecated
		{
			return $this->query($query);
		}
	
		function insertID()
		{
			return mysql_insert_id($this->conn);
		}
		
		function igroupsInsertID() //Deprecated
		{
			return $this->insertID();
		}
		
		function iknowInsertID() //Deprecated
		{
		
		}
		
		function affectedRows()
		{
			return mysql_affected_rows($this->conn);
		}
	}
	
	function anchorTags($str)
	{
		$arr1 = array();
		$arr1[] = "&lt;a href";
		$arr1[] = "&lt;A HREF";
		$arr1[] = "&lt;/a";
		$arr1[] = "&lt;/A";
		$arr1[] = "&gt;";
		$arr1[] = "&quot;";
		$arr2 = array();
		$arr2[] = "<a onclick=\"window.open(this.href); return false;\" href";
		$arr2[] = "<a onclick=\"window.open(this.href); return false;\" href";
		$arr2[] = "</a";
		$arr2[] = "</a";
		$arr2[] = ">";
		$arr2[] = "\"";
		$arr1[] = "\n";
		$arr2[] = "<br />";
		return str_replace($arr1, $arr2, $str);
	}

	function anchorTagsNoBreaks($str)
	{
		$arr1 = array();
		$arr1[] = "&lt;a href";
		$arr1[] = "&lt;A HREF";
		$arr1[] = "&lt;/a";
		$arr1[] = "&lt;/A";
		$arr1[] = "&gt;";
		$arr1[] = "&quot;";
		$arr2 = array();
		$arr2[] = "<a onclick=\"window.open(this.href); return false;\" href";
		$arr2[] = "<a onclick=\"window.open(this.href); return false;\" href";
		$arr2[] = "</a";
		$arr2[] = "</a";
		$arr2[] = ">";
		$arr2[] = "\"";
		return str_replace($arr1, $arr2, $str);
	}
}
?>
