<?php
	echo "<link rel=\"stylesheet\" href=\"";
	if(strstr(__FILE__,"admin/") || strstr(__FILE__,"iknow/") || strstr(__FILE__,"dboard/"))
		echo "../";
	echo "default.css\" type=\"text/css\" />\n";
	echo "<style type=\"text/css\">\n";
	$query = $db->igroupsQuery("select distinct sKey from Appearance where sCSSAttribute is not null");
	while($row = mysql_fetch_row($query))
	{
		echo $row[0]." {\n";
		$query2 = $db->igroupsQuery("select sCSSAttribute, sValue from Appearance where sKey='".$row[0]."' and sCSSAttribute is not null");
		while($row2 = mysql_fetch_array($query2))
		{
			echo "\t".$row2['sCSSAttribute'].": ".$row2['sValue'].";\n";
		}
		echo "}\n";
	}
?>
</style>
