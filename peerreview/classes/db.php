<?php
require_once('customize.php');
if(!class_exists('dbConnection'))
{
	if(!function_exists('errorPage'))
	{
		function errorPage($title, $desc, $response)
		{
			global $currentUser;
			
			session_start();
			$title = htmlspecialchars($title);
			$desc = htmlspecialchars($desc);
			$serverarr = array();
			foreach($_SERVER as $key => $val)
				$serverarr[$key] = is_string($val) ? htmlspecialchars($val) : $val;
			set_include_path(get_include_path().PATH_SEPARATOR.'./includes'.PATH_SEPARATOR.'../includes'.PATH_SEPARATOR.'../../includes');
			$responses = array(400 => 'Bad Request', 401 => 'Authorization Required', 403 => 'Forbidden', 404 => 'Not Found', 500 => 'Internal Server Error');
			if(!$responses[$response])
				$response = 500;
			header("HTTP/1.1 $response {$responses[$response]}");
			$accept = $_SERVER['HTTP_ACCEPT'];
			$ua = $_SERVER['HTTP_USER_AGENT'];

			if(!isset($_GET['html']) && (isset($_GET['xhtml']) || stristr($ua, 'W3C_Validator') !== false || (isset($accept) && stristr($accept, 'application/xhtml+xml') !== false)))
			{
				global $st, $checked, $disabled, $selected, $contenttype;
				header('Content-Type: application/xhtml+xml');
				$contenttype = 'application/xhtml+xml';
				$st = ' /';
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
				echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n";
				echo "<head>\n<meta http-equiv=\"Content-Type\" content=\"$contenttype; charset=utf-8\"$st>\n";
			}
			else
			{
				global $st, $checked, $disabled, $selected, $contenttype;
				$contenttype = 'text/html';
				$st = '';
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
				echo "<html lang=\"en\">\n";
				echo "<head>\n<meta http-equiv=\"Content-Type\" content=\"$contenttype; charset=utf-8\"$st>\n";
			}

			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$GLOBALS['rootdir']}style.css\"$st>\n";
			echo "<title>{$GLOBALS['systemname']} - Fatal Error</title>\n";
			include('sidebar.php');
			echo "<div id=\"content\">\n";
			echo "<h1>Fatal Error - $response {$responses[$response]}</h1>\n";
			echo "<h2>$title</h2>\n";
			echo "<p>{$GLOBALS['systemname']} cannot perform the operation you requested, for the following reason: $desc</p>\n";
			echo "<p>If you wish to receive support for this problem, please email <a href=\"mailto:{$GLOBALS['adminemail']}\">{$GLOBALS['adminemail']}</a> with the information below, along with any additional information that you feel is relevant.</p>\n";
			echo "<ul>\n";
			echo "<li><b>Requested URI</b>: {$serverarr['REQUEST_URI']}</li>\n";
			echo "<li><b>Referring page</b>: {$serverarr['HTTP_REFERER']}</li>\n";
			echo "<li><b>User Agent</b>: {$serverarr['HTTP_USER_AGENT']}</li>\n";
			if(isset($_SESSION['uID']))
				echo '<li><b>User ID</b>: '.htmlspecialchars($_SESSION['uID'])."</li>\n";
			echo "<li><b>Error title:</b> $title</li>\n";
			echo "<li><b>Error description:</b> $desc</li>\n";
			echo "<li><b>HTTP response code:</b> $response</li>\n";
			echo "</ul>\n";
			echo '</div></body></html>';
			die();
		}
	}
	
	class dbConnection
	{
		var $dbConn, $dbname;
		
		//EDIT THESE VARIABLES
		function dbConnection()
		{
			global $db_user, $db_pass;
			$db_name = 'prv2'; //The name of your database
			$this->dbname = $db_name;

			$this->dbConn = mysql_connect('localhost', $db_user, $db_pass) or errorPage('Could Not Connect To Database', mysql_error(), 500);
			mysql_select_db($db_name, $this->dbConn) or errorPage('Could Not Select Database', mysql_error(), 500);
		}
		
		function query($query)
		{
			$query_db_result = mysql_query($query, $this->dbConn) or die("Invalid query:\n<br />\n$query<br />\n".mysql_error($this->dbConn));
			return $query_db_result;
		}
		
		function igroupsQuery($query)
		{
			if(!mysql_select_db('igroups', $this->dbConn))
				return false;
			$result = mysql_query($query, $this->dbConn);
			mysql_select_db($this->dbname, $this->dbConn) or die('Fatal error, could not reset database');
			return $result;
		}
		
		function insertID()
		{
			return mysql_insert_id($this->dbConn);
		}
	}
}
?>
