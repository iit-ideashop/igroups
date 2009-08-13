<?php
	include_once('globals.php');
	include_once('checklogin.php');
	ini_set('memory_limit', '16M');

	include_once('classes/subgroup.php');
	include_once('classes/category.php');
	include_once('classes/email.php');
		
	function peopleSort($array)
	{
		$newArray = array();
		foreach($array as $person)
			$newArray[$person->getCommaName()] = $person;
		ksort($newArray);
		return $newArray;
	}
	$query = $db->igroupsQuery('select sSig from Profiles where iPersonID='.$currentUser->getID());
	$arr = mysql_fetch_row($query);
	unset($query);
	$sig = $arr[0];
	unset($arr);
	if($sig)
		$sig = "\n\n\n--\n$sig";
		
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Send Email</title>
</head>
<body onload="sendinit()">
<?php
	$to = array();
	if(is_numeric($_GET['replyid']))
		$replyEmail = new Email($_GET['replyid'], $db);
	else if(is_numeric($_GET['forward']))
		$forwardEmail = new Email($_GET['forward'], $db);
	
	if(isset($_GET['to']))
	{
		$exp = explode(',', $_GET['to']);
		foreach($exp as $i)
		{
			if(is_numeric($i) && $i > 0 && $p = new Person($i, $db) && $currentGroup->isGroupMember($p))
				$to[$i] = $p;
		}
	}
?>
	<form method="post" action="email.php" enctype="multipart/form-data" id="mailform">
		<div id="to">		
			<a href="#" onclick="toggleToDisplay()">+</a><fieldset><legend>To:</legend>
			<table id="to-table" width="100%">
<?php
				$members = $currentGroup->getGroupMembers();
				$members = peopleSort($members);
				$i = 1;
				foreach($members as $person)
				{
					if($i == 1) 
						echo '<tr>';
					if((isset($_GET['replyid']) && $person->getID() == $replyEmail->getSenderID()) || array_key_exists($person->getID(), $to))
						echo "<td><input type=\"checkbox\" name=\"sendto[".$person->getID()."]\" id=\"sendto[".$person->getID()."]\" checked=\"checked\" /></td>";
					else
						echo "<td><input type=\"checkbox\" name=\"sendto[".$person->getID()."]\" id=\"sendto[".$person->getID()."]\" /></td>";
					
					echo "<td><label for=\"sendto[".$person->getID()."]\">".$person->getFullName()."</label></td>";
					if($i == 3)
					{
						echo "</tr>\n";
						$i = 1;
					}
					else
						$i++;
				}
?>			
			<tr><td colspan="2"><a href="javascript:checkedAll('mailform', true)">Check All</a> / <a href="javascript:checkedAll('mailform', false)">Uncheck All</a></td></tr>
			</table></fieldset>
		</div><br />
<?php
		$subgroups = $currentGroup->getSubGroups();
		if($subgroups)
		{
?>
			<div id="subgroups">
			<a href="#" onclick="toggleSGDisplay()">+</a><fieldset><legend>Subgroups:</legend>
			<table id="subgroups-table" width="100%">
<?php
			$i = 1;
			foreach ($subgroups as $subgroup)
			{
				if($i == 1)
					echo "<tr>";
				echo "<td><input type=\"checkbox\" id=\"subgroup".$subgroup->getID()."\" name=\"sendtosubgroup[".$subgroup->getID()."]\" />&nbsp;";
				echo "<label for=\"subgroup".$subgroup->getID()."\">".$subgroup->getName()."</label></td>";
				if($i == 3)
				{
					echo "</tr>\n";
					$i = 1;
				}
				else
					$i++;
			}
?>
			</table></fieldset>
		</div>
<?php 	
		}
?>
		<div id="guests">
<?php
	$members = $currentGroup->getGroupGuests();
	if(count($members) > 0)
	{
?>
			<br /><a href="#" onclick="toggleGuestDisplay()">+</a><fieldset><legend>Guests:</legend>
			<table id="guest-table" width="100%">
<?php
				$members = peopleSort($members);
				$i = 1;
				foreach($members as $person)
				{
					if($i == 1)
						echo '<tr>';
					echo "<td><input type=\"checkbox\" id=\"guest".$person->getID()."\" name=\"sendtoguest[".$person->getID()."]\" /></td>";
					echo "<td><label for=\"guest".$person->getID()."\">".$person->getFullName()."</label></td>";
					if($i == 3)
					{
						echo "</tr>\n";
						$i = 1;
					}
					else
						$i++;
				}

?>
			<tr><td colspan="2"><a href="javascript:checkedAllGuest('mailform', true)">Check All</a> / <a href="javascript:checkedAllGuest('mailform', false)">Uncheck All</a></td></tr>
			</table></fieldset>
		</div>
<?php } ?>		
		<br />
		<table>
			<tr><td><label for="cc">CC:</label></td><td><input type="text" size="50" name="cc" id="cc" /></td></tr>
<?php
		if(isset($replyEmail)) 
			echo "<tr><td><label for=\"subject\">Subject:</label></td><td><input type=\"text\" size=\"50\" name=\"subject\" id=\"subject\" value=\"RE: {$replyEmail->getSubjectHTML()}\" /></td></tr>";
		else if(isset($forwardEmail)) 
			echo "<tr><td><label for=\"subject\">Subject:</label></td><td><input type=\"text\" size=\"50\" name=\"subject\" id=\"subject\" value=\"FW: {$forwardEmail->getSubjectHTML()}\" /></td></tr>";
		else
			echo "<tr><td><label for=\"subject\">Subject:</label></td><td><input type=\"text\" size=\"50\" name=\"subject\" id=\"subject\" /></td></tr>";
?>
			<tr><td><input type="checkbox" name="confidential" id="confidential" /></td><td><label for="confidential">Keep confidential? (if checked, will not be stored in iGROUPS)</label></td></tr>
			<tr><td><label for="category">Category</label></td><td><select name="category" id="category"><option value="0">No Category</option>
<?php
			$categories = $currentGroup->getGroupCategories();
			foreach($categories as $category)
				echo "<option value=\"".$category->getID()."\">".$category->getName()."</option>\n";
?>
			</select></td></tr>
			<tr><td>Attachments:</td></tr>
			<tr><td colspan="2">
			<div id="files"><div class="stdBoldText" id="file1div">&nbsp;&nbsp;&nbsp;<label for="attachment1">File 1:</label> <input type="file" name="attachment1" id="attachment1" onchange="fileAdd(1);" /></div></div></div>
		<span onclick="fileAdd(document.getElementById('files').childNodes.length);" style="color:#00F;text-decoration:underline;cursor:pointer;">Click here to add another file.</span>
			</td></tr>
			<tr><td colspan="2"><label for="body">Body:</label></td></tr>
<?php
		if(isset($replyEmail)) 
			echo "<tr><td colspan=\"2\"><textarea name=\"body\" id=\"body\" cols=\"54\" rows=\"10\">$sig\n\n\n----Original E-mail Follows----\n{$replyEmail->getReplyBody()}</textarea></td></tr>";
		else if(isset($forwardEmail)) 
			echo "<tr><td colspan=\"2\"><textarea name=\"body\" id=\"body\" cols=\"54\" rows=\"10\">$sig\n\n\n----Original E-mail Follows----\n{$forwardEmail->getReplyBody()}</textarea></td></tr>";
		else
			echo "<tr><td colspan=\"2\"><textarea name=\"body\" id=\"body\" cols=\"54\" rows=\"10\">$sig</textarea></td></tr>";
?>
			<tr><td colspan="2" align="center"><input type="button" value="Spell Check" onclick="openSpellChecker();" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="send" value="Send Email" /></td></tr>
		</table>
<?php
		if(isset($_GET['replyid']))
			echo "<input type=\"hidden\" name=\"replyid\" value=\"".intval($_GET['replyid'])."\" />";
		else
			echo "<input type=\"hidden\" name=\"replyid\" value=\"0\" />";
		if(isset($_GET['forward']))
			echo "<input type=\"hidden\" name=\"forwardid\" value=\"".intval($_GET['forward'])."\" />";
		else
			echo "<input type=\"hidden\" name=\"forwardid\" value=\"0\" />";
?>
	</fieldset></form>
</body></html>
