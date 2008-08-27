<?php
require_once( "classes/db.php" );
require_once( "classes/file.php" );
require_once( "classes/folder.php" );

$db = new dbConnection();
$files = $db->igroupsQuery( "SELECT iID FROM Files" );
while ( $row = mysql_fetch_row( $files ) ) {
	$file = new File( $row[0], $db );
	if ( $file->isInTrash() == true )
		print $file->getID()." ".$file->getName()."<br>";
}
?>
