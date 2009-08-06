<?php
	if(isset($currentUser) && $currentUser && isset($db))
	{
		$lynyrdskynyrd = mysql_fetch_row($db->igroupsQuery('select sName from Skins where iID in (select iSkin from People where iID='.$currentUser->getID().')'));
		if(!$lynyrdskynyrd)
		{
			$lynyrdskynyrd = mysql_fetch_row($db->igroupsQuery('select sName from Skins where bDefault=1'));
			$alts = $db->igroupsQuery('select sName from Skins where bDefault=0 and bPublic=1');
		}
		else
			$alts = $db->igroupsQuery('select sName from Skins where iID not in (select iSkin from People where iID='.$currentUser->getID().') and bPublic=1');
	}
	else if(isset($db))
	{
		$lynyrdskynyrd = mysql_fetch_row($db->igroupsQuery('select sName from Skins where bDefault=1'));
		$alts = $db->igroupsQuery('select sName from Skins where bDefault=0 and bPublic=1');
	}
	else
		die('No database connection');
	$skin = $lynyrdskynyrd[0];
	$altskins = array();
	while($row = mysql_fetch_row($alts))
		$altskins[] = $row[0];
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
?>
