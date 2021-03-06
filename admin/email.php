<?php
	include_once('../globals.php');
	include_once('../classes/config.php');
	include_once('checkadmin.php');
	ini_set('memory_limit', '16M');

	include_once('../classes/group.php');
	include_once('../classes/category.php');
	include_once('../classes/groupemail.php');
	include_once('../classes/semester.php');	
	include_once('../classes/email.php');

	if(isset($_POST['selectSemester']))
		$_SESSION['selectedIPROSemester'] = $_POST['semester'];
	else
	{
		$query = $db->query('SELECT iID FROM Semesters WHERE bActiveFlag=1');
		$row = mysql_fetch_row($query);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}

	$currentSemester = new Semester($_SESSION['selectedIPROSemester'], $db);

	function groupSort($array)
	{
		$newArray = array();
		foreach($array as $group)
			$newArray[$group->getName()] = $group;
		ksort($newArray);
		return $newArray;
	}

	if(isset($_GET['sort']) && is_numeric($_GET['sort']))
		$_SESSION['emailSort'] = $_GET['sort'];
	
	if(!isset($_SESSION['emailSort']))
		$_SESSION['emailSort'] = -3;
	
	//----------Start XHTML Output----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/email.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/email.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname;?> - Group Email</title>
<style type="text/css">
.window {
	display: none;
}
</style>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript" src="../speller/spellChecker.js"></script>
<script type="text/javascript">
//<![CDATA[	
	function showMessage(msg)
	{
		msgDiv = document.createElement("div");
		msgDiv.id="messageBox";
		msgDiv.innerHTML=msg;
		document.body.insertBefore(msgDiv, null);
		window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
	}
	
	function fileAdd(num)
	{
		if (document.getElementById('files').childNodes.length == num)
		{
			var div = document.createElement('div');
			div.className = "stdBoldText";
			div.id = "file"+(num*1+1)+"div";
			div.innerHTML = "&nbsp;&nbsp;&nbsp;File "+(num*1+1)+": <input type='file' name='attachment"+(num*1+1)+"' onchange='fileAdd("+(num*1+1)+");' />";
			document.getElementById('files').appendChild(div);
		}
	}
	
	function openSpellChecker()
	{
		var speller = new spellChecker();
		speller.spellCheckAll();
	}
	
	function toggleToDisplay()
	{
		tobox = document.getElementById('to-table');
		switch (tobox.style.display) {
			case 'none':
				tobox.style.display='block';
				break;
			default:
				tobox.style.display='none';
				break;
		}
	}

	function checkedAll (id, checked)
	{
		var el = document.getElementById(id);
		for(var i = 0; i < el.elements.length; i++)
			el.elements[i].checked = checked;
	}
//]]>
</script>
</head>
<body>
<?php
		 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
	echo "<div id=\"topbanner\">E-mail Groups</div>\n";
	
	if(isset($_POST['delete']) && $_POST['delete'] == 1 && isset($_POST['email']))
	{
		foreach($_POST['email'] as $emailid => $val)
		{
			$email = new GroupEmail( $emailid, $db );
			$email->delete();
		}
		$message = 'Selected items successfully deleted';
	}

	if(isset($_POST['send']))
	{
		$to = array();
		$toString = '';

		foreach($_POST['sendto'] as $id => $val)
		{
			$group = new Group($id, 0, $currentSemester->getID(), $db);
			$toString .= $group->getName().', ';
			$people = $group->getGroupMembers();
			foreach($people as $person)
			{
				if(!in_array($person->getEmail(), $to))
					$to[] = $person->getEmail();
			}
		}
		$toString = substr($toString, 0, strlen($toString)-2);
		$tolist = join(',', $to);

		// per spec, header field separation is \r\n, but sendmail expects native breaks
 		// (and mail clients generally don't mind)
		$headers = "From:".$email_from."\n"."Reply-To:".$currentUser->getEmail()."\n"."Bcc: $tolist\n";
		if(isset($_POST['cc']))
			$headers .= "Cc:{$_POST['cc']}\n";
		$mime_boundary = md5(time());
		$headers .= 'MIME-Version: 1.0'."\n";
		$headers .= 'Content-Transfer-Encoding: 7-bit'."\n";
		$headers .= "Content-Type: multipart/mixed; boundary=".$mime_boundary."\n";
		$filename = str_replace(' ', '_', $_FILES['attachment']['name']);
		$msg = '';
		$diskname = array();
		for($i = 1; $i <= count($_FILES); $i++)
		{
			if($_FILES["attachment$i"]['size'] != 0)
			{
				$handle = fopen($_FILES["attachment$i"]['tmp_name'], 'rb');
				$f_contents = fread($handle, $_FILES["attachment$i"]['size']);
				$f_contents = chunk_split(base64_encode($f_contents));
				fclose($handle);
				$filename = str_replace(' ', '_', $_FILES["attachment$i"]['name']);
				$msg .= "--".$mime_boundary."\n";
				$msg .= "Content-Type: {$_FILES["attachment$i"]['type']};\n name={$filename}\n";
				$msg .= "Content-Transfer-Encoding: base64"."\n";
				$msg .= "Content-Disposition: attachment; filename=".$filename."\n"."\n";
				$msg .= $f_contents."\n"."\n";
				$db->query("INSERT INTO GroupEmailFiles (iEmailID, sOrigName, sMimeType) VALUES (0, '$filename', '{$_FILES["attachment$i"]['type']}')");
				$id = $db->insertID();
				$diskname[$i] = "G$id.att";
				$db->query("UPDATE GroupEmailFiles SET sDiskName='".$diskname[$i]."' WHERE iID=$id");
				move_uploaded_file($_FILES["attachment$i"]['tmp_name'], "$disk_prefix/emails/G$id.att");
			}
		}
		//$tmpstr = wordwrap($_POST['body'], 100);
		if($_POST['subject'] == '')
			$subject = '(no subject)';
		else
			$subject = $_POST['subject'];
		$subj = new SuperString($subject);
		$body = new SuperString($_POST['body']);
		$msg .= "--$mime_boundary\n";
		$msg .= "Content-Type: text/html; charset=iso-8859-1\n";
		$msg .= "Content-Transfer-Encoding: 7bit\n\n";
		$msg .= anchorTags(htmlspecialchars($body->getString()))."\n\n";
		$msg .= "--$mime_boundary--\n\n";
		$err = mail('', '[IPRO Office Notice] '.stripslashes($_POST['subject']), $msg, $headers);
		$err = 1;
		if($err)
			echo "<p>Your email was successfully sent.</p>";
		else
			echo "<p>There was a problem with your e-mail. Please contact iproadmin.</p>";
		$newEmail = createGroupEmail($toString, $subj->getString(), $body->getString(), $currentUser->getID(), $currentSemester->getID(), $db);
		$query = $db->query("UPDATE GroupEmailFiles SET iEmailID={$newEmail->getID()} WHERE iEmailID=0");
		foreach($_POST['sendto'] as $id => $val)
		{
			$group = new Group($id, 0, $currentSemester->getID(), $db );
			$copyEmail = createEmail($group->getName(), $subj->getString(), $body->getString(), $currentUser->getID(), 1, 0, $group->getID(), $group->getType(), $group->getSemester(), $db);
			for($i = 1; $i <= (count($_FILES)); $i++)
			{
				if($_FILES["attachment$i"]['size'] != 0)
				{
					$filename = str_replace(' ', '_', $_FILES["attachment$i"]['name']);
					$db->query("INSERT INTO EmailFiles (iEmailID, sOrigName, sMimeType, sDiskName) VALUES ({$copyEmail->getID()}, '$filename', '{$_FILES["attachment$i"]['type']}', '$diskname[$i]')");
				}
			}
		}
	}

	$emails = array();
	$add = str_replace('Emails', 'GroupEmails', decodeEmailSort($_SESSION['emailSort']));
	$query = $db->query("SELECT GroupEmails.iID, People.sFName, People.sLName FROM GroupEmails inner join People on GroupEmails.iSenderID=People.iID WHERE GroupEmails.iSemesterID={$currentSemester->getID()}".$add);
	while($row = mysql_fetch_row($query))
		$emails[] = new GroupEmail($row[0], $db);
?>
	<form method="post" action="email.php"><fieldset>
	<div id="container"><div id="emailbox">
	<div id="emailboxheader">Mass Emails for semester:
	<span id="semesters">
	<select name="semester">
<?php
	$semesters = $db->query("SELECT iID FROM Semesters ORDER BY iID DESC");
	while($row = mysql_fetch_row($semesters))
	{
		$semester = new Semester( $row[0], $db );
		if(isset($currentSemester) && $semester->getID() == $currentSemester->getID())
			echo "<option value=\"".$semester->getID()."\" selected=\"selected\">".$semester->getName()."</option>";
		else
			echo "<option value=\"".$semester->getID()."\">".$semester->getName()."</option>";
	}
?>
	</select>
	<input type="submit" name="selectSemester" value="Select Semester" />
	</span></div>
	<div id="menubar">
	<ul class="ema">
	<li><a href="#" onclick="sendwin=dhtmlwindow.open('sendbox', 'div', 'send-window', 'Send Email', 'width=550px,height=800px,left=300px,top=100px,resize=1,scrolling=1'); return false">Send Email</a></li>
	<li><a href="#" onclick="window.location.href='searchemail.php';">Search Email</a></li>
	<li><a href="#" onclick="document.getElementById('delete').value='1'; document.getElementById('delete').form.submit()">Delete Selected</a>
	<input type="hidden" id="delete" name="delete" value="0" /></li>
	</ul>
	</div><div id="emails">
<?php
	if(count($emails) > 0)
	{
		echo "<table width=\"85%\">\n";
		echo "<tr class=\"sortbar\">\n";
		if($_SESSION['emailSort'] == 1)
			echo "<td colspan=\"2\"><a href=\"email.php?sort=-1\" title=\"Sort this descendingly\">Subject <img src=\"../skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
		else if($_SESSION['emailSort'] == -1)
			echo "<td colspan=\"2\"><a href=\"email.php?sort=1\" title=\"Sort this ascendingly\">Subject <img src=\"../skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
		else
			echo "<td colspan=\"2\"><a href=\"email.php?sort=1\" title=\"Sort by subject\">Subject</a>";
		if($_SESSION['emailSort'] == 2)
			echo "<td><a href=\"email.php?sort=-2\" title=\"Sort this descendingly\">Author <img src=\"../skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
		else if($_SESSION['emailSort'] == -2)
			echo "<td><a href=\"email.php?sort=2\" title=\"Sort this ascendingly\">Author <img src=\"../skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
		else
			echo "<td><a href=\"email.php?sort=2\" title=\"Sort by author\">Author</a>";
		if($_SESSION['emailSort'] == 3)
			echo "<td><a href=\"email.php?sort=-3\" title=\"Sort this descendingly\">Date <img src=\"../skins/$skin/img/down.png\" alt=\"V\" title=\"Sorted in ascending order\" /></a>";
		else if($_SESSION['emailSort'] == -3)
			echo "<td><a href=\"email.php?sort=3\" title=\"Sort this ascendingly\">Date <img src=\"../skins/$skin/img/up.png\" alt=\"^\" title=\"Sorted in descending order\" /></a>";
		else
			echo "<td><a href=\"email.php?sort=-3\" title=\"Sort by date\">Date</a>";
		echo "<td></td></tr>\n";
		
		foreach($emails as $email)
		{
			$author = $email->getSender();
			printTR();
			if($email->hasAttachments()) 
				$img = '&nbsp;<img src="../skins/'.$skin.'/img/attach.png" alt="(Attachments)" title="Paper clip" />';
			else
				$img = '';
			echo "<td colspan=\"2\"><a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->getID()."', 'View Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">".$email->getShortSubject()."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td><td><input type='checkbox' name='email[".$email->getID()."]' /></td></tr>";
		}
		echo "</table>\n";
	}
?>
	</div></div></div></fieldset></form>
	<div id="send-window" class="window">
	<form method="post" action="email.php" enctype="multipart/form-data" id="email"><fieldset>
	<div id="to">
	<a href="#" onclick="toggleToDisplay()">+</a> To:
	<table id="to-table" width="85%">
<?php
	$groups = $currentSemester->getGroups();
	$groups = groupSort($groups);
	$i = 0;
	foreach($groups as $group)
	{
		if(!$i)
			echo '<tr>';
		echo "<td><input type=\"checkbox\" name=\"sendto[".$group->getID()."]\" id=\"sendto".$group->getID()."\" checked=\"checked\" />";
		echo "&nbsp;<label for=\"sendto".$group->getID()."\">".$group->getName()."</label></td>";
		if($i == 2)
		{
			echo "</tr>\n";
			$i = 0;
		}
		else
			$i++;
	}
	if($i != 0)
		echo "</tr>\n";
?>
<tr><td colspan="4"><a href="javascript:checkedAll('email', true)">Check All</a> / <a href="javascript:checkedAll('email', false)">Uncheck All</a>
</td></tr></table>
</div><br />
<table>
<tr><td><label for="cc">CC:</label></td><td><input type="text" size="50" name="cc" id="cc" /></td></tr>
<tr><td><label for="subject">Subject:</label></td><td><input type="text" size="50" name="subject" id="subject" /></td></tr>
<tr><td>Attachments:</td></tr>
<tr><td colspan="2">
<div id="files"><div class="stdBoldText" id="file1div">&nbsp;&nbsp;&nbsp;<label for="attachment1">File 1:</label><input type="file" name="attachment1" id="attachment1" onchange="fileAdd(1);" /></div></div>
<span onclick="fileAdd(document.getElementById('files').childNodes.length);" style="color:#00F;text-decoration:underline;cursor:pointer;">Add another file.</span>
</td></tr>
<tr><td colspan="2"><label for="body">Body:</label></td></tr>
<tr><td colspan="2"><textarea name="body" id="body" cols="54" rows="10"></textarea></td></tr>
<tr><td colspan="2" align="center"><input type="button" value="Spell Check" onclick="openSpellChecker();" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="send" value="Send Email" /></td></tr>
</table></fieldset></form></div>

<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>

</body></html>
