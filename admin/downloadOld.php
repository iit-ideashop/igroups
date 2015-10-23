<?php
	require_once('../globals.php');
	require_once('checkadmin.php');
	$sMetaTag = '';
	$sPageTitle = 'iKNOW File Download';
	$fileErr = '';
	#first determine whether the file exists or not, then determine whether the user has the clearance to read that file

	if(isset($_GET['file']) && ($_GET['file'] != ''))
	{
		$qFindFile = "select iNuggetID, sDiskName, sOrigName from NuggetFiles where iID = " . $_GET['file'];
		$rFindFile = $db->query($qFindFile, $db);
		if(mysql_num_rows($rFindFile) > 0)
		{
			list($nuggID, $diskName, $origName) = mysql_fetch_row($rFindFile);
			printDownloadResponse($diskName, $origName);
		}
		else
			$fileErr = 'No such file in the repository';
	}
	else
		$fileErr = 'There was an error processing your request.  Please report this error to the administrator.';


function printBody()
{
	global $dbConn;
	global $fileErr;
	print $fileErr;
}

function printDownloadResponse($diskName, $origName)
{
	global $disk_prefix;
	$sFullLocation = $disk_prefix.'/iknowfiles/'.$diskName;
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
