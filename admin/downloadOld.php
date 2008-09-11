<?php
	session_start();
	#error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	require_once('../classes/db.php');
	require_once('../classes/person.php');
	
	$sMetaTag = '';
	$sPageTitle = "iKNOW File Download";
	$fileErr = '';
	$db = new dbConnection();
	#first determine whether the file exists or not, then determine whether the user has the clearance to read that file

	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], "@") === FALSE)
			$userName = $_COOKIE['userID']."@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
	}
	else
		die("You are not logged in.");
		 
	if ( !$currentUser->isAdministrator() )
		die("You must be an administrator to access this page.");

	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		 
	if ( !$currentUser->isAdministrator() )
		die("You must be an administrator to access this page.");

	if (isset($_GET['file']) && ($_GET['file'] != '')) {
		$qFindFile = "select iNuggetID, sDiskName, sOrigName from NuggetFiles where iID = " . $_GET['file'];
		$rFindFile = $db->igroupsquery($qFindFile, $db);
		if (mysql_num_rows($rFindFile) > 0) {
			list($nuggID, $diskName, $origName) = mysql_fetch_row($rFindFile);
			printDownloadResponse($diskName, $origName);
		} else {
			$fileErr = 'No such file in the repository';
		}
	} else {
		$fileErr = 'There was an error processing your request.  Please report this error to the administrator.';
	}


function printBody() {
	global $dbConn;
	global $fileErr;
	print $fileErr;
}

function printDownloadResponse($diskName, $origName) {
	$sFullLocation = '/files/iknow/' . $diskName;
	header("Content-Type: application/octet-stream");
	header("Content-Length: " . filesize($sFullLocation));
	header("Content-Disposition: attachment; filename=\"$origName\"");
	header("Cache-Control: no-store,no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-type: application/force-download");
	
	$handle = fopen($sFullLocation, "rb");
	fpassthru($handle);
	fclose($handle);
	
	if(!isset($_SESSION['iUserID']))
		$logID = -1;
	else
		$logID = $_SESSION['iUserID'];

	//LogEvent(3,$logID,$origName);
}
?>
