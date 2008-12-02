<?php
	if(isset($currentUser) && $currentUser && isset($db))
	{
		$lynyrdskynyrd = mysql_fetch_row($db->igroupsQuery('select sName from Skins where iID in (select iSkin from People where iID='.$currentUser->getID().')'));
		if(!$lynyrdskynyrd)
			$lynyrdskynyrd = mysql_fetch_row($db->igroupsQuery('select sName from Skins where bDefault=1'));
	}
	else if(isset($db))
		$lynyrdskynyrd = mysql_fetch_row($db->igroupsQuery('select sName from Skins where bDefault=1'));
	else
		die('No database connection');
	$skin = $lynyrdskynyrd[0];
	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" />\n";
?>
