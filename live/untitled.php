<?php
include_once("classes/db.php");
$db = new dbConnection();

$listmap = $db->igroupsQuery( "SELECT * FROM FileListMap" );

while ( $map = mysql_fetch_array( $listmap ) ) {
	$list = $db->igroupsQuery( "SELECT * FROM FileLists WHERE iID=".$map['iListID'] );
	if ( $listinfo = mysql_fetch_array( $list ) ) {
		$db->igroupsQuery( "UPDATE Folders SET iParentFolderID=".$listinfo['iBaseFolder']." WHERE iID=".$map['iFolderID'] );
	}
}