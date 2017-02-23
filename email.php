<?php
	include_once('globals.php');
	include_once('checklogingroupless.php');
	include_once('classes/group.php');
	include_once('classes/category.php');
	include_once('classes/email.php');
	
	//------Start of Code for Form Processing-----------------------//
	
	if(isset($_GET['replyid']) && is_numeric($_GET['replyid']))
	{
		$email = new Email($_GET['replyid'], $db);
		$currentGroup = new Group($email->getGroupID(), $email->getGroupType(), $email->getSemester(), $db);
		if(!$currentGroup->isGroupMember($currentUser))
			errorPage('Group Credentials Required', 'You are not a member of this group', 403);
		$_SESSION['selectedGroup'] = $email->getGroupID();
		$_SESSION['selectedGroupType'] = $email->getGroupType();
		$_SESSION['selectedSemester'] = $email->getSemester();
	}
	else if(isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) && is_numeric($_SESSION['selectedGroup']) && is_numeric($_SESSION['selectedGroupType']) && is_numeric($$_SESSION['selectedSemester']))
	{
		$currentGroup = new Group($_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db);
		if(!$currentGroup->isGroupMember($currentUser))
		{
			unset($_SESSION['selectedGroup']);
			unset($_SESSION['selectedGroupType']);
			unset($_SESSION['selectedSemester']);
			setcookie('selectedGroup', '', time()-60);
			errorPage('Group Credentials Required', 'You are not a member of this group', 403);
		}
	}
	else if(isset($_COOKIE['selectedGroup']))
	{
		$group = explode(',', $_COOKIE['selectedGroup']);
		$currentGroup = new Group($group[0],$group[1], $group[2], $db);
		if(!$currentGroup->isGroupMember($currentUser))
		{
			setcookie('selectedGroup', '', time()-60);
			errorPage('Group Credentials Required', 'You are not a member of this group', 403);
		}
		$_SESSION['selectedGroup'] = $group[0];
		$_SESSION['selectedGroupType'] = $group[1];
		$_SESSION['selectedSemester'] = $group[2];
	}
	else
		errorPage('Invalid Email ID', 'The email ID provided is invalid', 400);
	
	if(isset($_GET['selectCategory']) && is_numeric($_GET['selectCategory']))
		$_SESSION['selectedCategory'] = $_GET['selectCategory'];
	
	if(isset($_SESSION['selectedCategory']) && $_SESSION['selectedCategory'] == 0)
	{
		unset($_SESSION['selectedCategory']);
		$currentCat = false;
	}
	else if(isset($_SESSION['selectedCategory']) && is_numeric($_SESSION['selectedCategory']))
	{
		$currentCat = new Category($_SESSION['selectedCategory'], $db);
		if(!$currentCat->getGroupID())
			$currentCat->setGroup($currentGroup->getID());
		if(!$currentCat->getSemester())
			$currentCat->setSemester($currentGroup->getSemester());
		if(!$currentCat->getGroupType())
			$currentCat->setType($currentGroup->getType());
		if($currentCat->getGroupID() != $currentGroup->getID())
		{
			unset($_SESSION['selectedCategory']);
			$currentCat = false;
		}
	}
	else
		$currentCat = false;
	
	if(isset($_GET['sort']) && is_numeric($_GET['sort']))
		$_SESSION['emailSort'] = $_GET['sort'];
	
	if(!isset($_SESSION['emailSort']))
		$_SESSION['emailSort'] = -3;
	
	//------End of Code for Form Processing-------------------------//
	//------Start XHTML Output--------------------------------------//

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/email.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/email.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Email</title>
<script type="text/javascript" src="ChangeLocation.js"></script>
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript" src="speller/spellChecker.js"></script>
<script type="text/javascript" src="Email.js"></script>
</head>
<body>
<?php
	
/**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/	

	if(isset($_POST['send']))
	{
		if(isset($_POST['sendto']))
		{
			foreach($_POST['sendto'] as $id => $val)
			{
				$person = new Person($id, $db);
				$to[] = $person->getEmail();
				$names[] = $person->getFullName();
			}
		
			$tolist = join(',', $to);
			$toNames = join(', ', $names);
		}

		if(isset($_POST['sendtosubgroup']))
		{
			foreach($_POST['sendtosubgroup'] as $key => $val)
			{
				$subgroup = new SubGroup($key, $db);
				$people = $subgroup->getSubGroupMembers();
				foreach($people as $person)
					$to[] = $person->getEmail();
				$names[] = $subgroup->getName();
			}
			$tolist = join(',', $to);
			$toNames = join(', ', $names);
		}

		if(isset($_POST['sendtoguest']))
		{
			foreach($_POST['sendtoguest'] as $id => $val)
			{
				$person = new Person($id, $db);
				$to[] = $person->getEmail();
				$names[] = $person->getFullName();
			}
			$tolist = join(',', $to);
			$toNames = join(', ', $names);
		}
		
		$headers = "From: ".$currentUser->getFullName()." <".$currentUser->getEmail().">\n";
		if(isset($_POST['cc']) && ($_POST['cc'] != ''))
			$headers .= "Cc:".$_POST['cc']."\n";
		$mime_boundary = md5(time());
		$headers .= 'MIME-Version: 1.0'."\n";
		$headers .= 'Content-Type: multipart/mixed; boundary="'.$mime_boundary.'"'."\n";
		$headers .= 'Content-Transfer-Encoding: 8bit'."\n";
		$msg = '';
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
				if(!isset($_POST['confidential']))
				{
					$db->query("INSERT INTO EmailFiles (iEmailID, sOrigName, sMimeType) VALUES (0, '$filename', '{$_FILES["attachment$i"]['type']}')");
					$id = $db->insertID();
					$db->query("UPDATE EmailFiles SET sDiskName='$id.att' WHERE iID=$id");
					move_uploaded_file($_FILES["attachment$i"]['tmp_name'], "$disk_prefix/emails/$id.att");
				}
			}
		}
		//$tmpstr = wordwrap($_POST['body'], 110);
		$body = new SuperString($_POST['body']);
		$msg .= "--".$mime_boundary."\n";
		$msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
		$msg .= "Content-Transfer-Encoding: 8bit"."\n"."\n";
		$msg .= anchorTags(htmlspecialchars($body->getString()))."\n"."\n"; 
		if(!isset($_POST['confidential']))
		{
			$msg .= "--".$mime_boundary."\n";
			$msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
			$msg .= "Content-Transfer-Encoding: 8bit"."\n"."\n";
			$msg .= "<p><a href=\"$appurl/email.php?replyid=abxyqzta10\">Click here to reply to this email.</a></p>\n\n";
		}
		$msg .= "--".$mime_boundary.'--'."\n";
		if(isset($_POST['confidential']))
		{
			$subj = new SuperString( $_POST['subject'] );
			mail($tolist, "[".$currentGroup->getName()."] ".stripslashes($_POST['subject']), $msg, $headers);
		}
		else
		{
			if($_POST['subject'] == '')
				$_POST['subject'] = '(no subject)';
			$newEmail = createEmail($toNames, $_POST['subject'], $_POST['body'], $currentUser->getID(), $_POST['category'], $_POST['replyid'], $currentGroup->getID(), $currentGroup->getType(), $currentGroup->getSemester(), $db);
			$query = $db->query("UPDATE EmailFiles SET iEmailID={$newEmail->getID()} WHERE iEmailID=0");
			$msg = str_replace('abxyqzta10', $newEmail->getID(), $msg);
			mail($tolist, "[".$currentGroup->getName()."] ".stripslashes($_POST['subject']), $msg, $headers);
		}
		echo "<script type=\"text/javascript\">var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Your email was sent successfully.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal')</script>";
	}
	else if(isset($_GET['to']))
		echo "<script type=\"text/javascript\">var sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php?to=".$_GET['to']."', 'Send Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";
	else if(isset($_GET['replyid']))
		echo "<script type=\"text/javascript\">var sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php?replyid=".$_GET['replyid']."', 'Send Reply', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";
	else if(isset($_GET['forward']))
		echo "<script type=\"text/javascript\">var sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php?forward=".$_GET['forward']."', 'Forward Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";
	else if(isset($_GET['display']))
		echo "<script type=\"text/javascript\">var viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$_GET['display']."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";

	echo "<div id=\"content\">\n";
	if(isset($_POST['createcat']))
	{	
		createCategory($_POST['catname'], $_POST['catdesc'], $currentGroup->getID(), $currentGroup->getType(), $currentGroup->getSemester(), $db);
?>
		<script type="text/javascript">
			var successwin = dhtmlwindow.open('successbox', 'inline', '<p>Category created.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if(isset($_POST['editcat']))
	{
		$currentCat->setName($_POST['newcatname']);
		$currentCat->setDesc($_POST['newcatdesc']);
		$currentCat->updateDB();
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Category edited.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if(isset($_POST['delcat'] ) && $currentCat && $currentCat->getID() != 1)
	{
		$currentCat->delete();
		unset($_SESSION['selectedCategory']);
		$currentCat = false;
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Category deleted.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	else if(isset($_POST['delcat']))
	{
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>You cannot delete this category.</p>', 'Error', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if(isset( $_POST['delete']) && $_POST['delete']==1 && isset($_POST['email']))
	{
		foreach($_POST['email'] as $emailid => $val)
		{
			$email = new Email($emailid, $db);
			if($currentUser->isGroupModerator($email->getGroup()))
				$email->delete();
		}
		if(isset( $_POST['categories']))
		{
			foreach($_POST['categories'] as $catid => $val)
			{
				$category = new Category($catid, $db);
				if($currentUser->isGroupModerator($category->getGroup()))
				{
					$emails = $category->getEmails();
					foreach($emails as $email)
					{
						$email->setCategory(0);
						$email->updateDB();
					}
					$category->delete();
				}
			}
		}
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Selected items successfully deleted.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if(isset($_POST['emailMove']) && $_POST['emailMove'] != '')
	{
		$_POST['emailMove'] = explode(',', $_POST['emailMove']);
		foreach($_POST['emailMove'] as $key => $val)
		{
			$email = new Email($val, $db);
			$email->setCategory($_POST['targetcategory']);
			$email->updateDB();
		}
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Selected emails successfully moved.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}	
?>
	<div id="container"><div id="catbox">
	<div class="columnbanner">Your categories:</div>
<?php 
	if(!$currentUser->isGroupGuest($currentGroup))
	{
		echo "<div class=\"menubar\"><ul class=\"folderlist\">\n";
		echo "<li><a href=\"#\" onclick=\"ccatwin=dhtmlwindow.open('ccatbox', 'div', 'createCat', 'Create Category', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Create Category</a></li>\n";
		if($currentUser->isGroupModerator($currentGroup) && $currentCat && $currentCat->getID() != 1)
			echo "<li><a href=\"#\" onclick=\"ecatwin=dhtmlwindow.open('ecatbox', 'div', 'editCat', 'Edit Category', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Edit/Delete Category</a></li>\n";
		echo "</ul>\n</div>\n";
	}
	echo "<div id=\"cats\">\n";
	$categories = $currentGroup->getGroupCategories();
	if(!$currentCat)
		echo "<a href=\"email.php?selectCategory=0\"><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=0\"><strong>Uncategorized</strong></a><br /><a href=\"email.php?selectCategory=1\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=1\">IPRO Office Notices</a><br />\n";
	else if($currentCat->getID() == 1)
		echo "<a href=\"email.php?selectCategory=0\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=0\">Uncategorized</a><br /><a href=\"email.php?selectCategory=1\"><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"+\" title=\"Open folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=1\"><strong>IPRO Office Notices</strong></a><br />\n";
	else
		echo "<a href=\"email.php?selectCategory=0\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=0\">Uncategorized</a><br /><a href=\"email.php?selectCategory=1\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=1\">IPRO Office Notices</a><br />\n";
	
	foreach($categories as $category)
	{
		if($currentCat && $currentCat->getID() == $category->getID())
			echo "<a href=\"email.php?selectCategory=".$category->getID()."\"><img src=\"skins/$skin/img/folder-expanded.png\" style=\"border-style: none\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=".$category->getID()."\"><strong>".htmlspecialchars($category->getName())."</strong></a><br />\n";
		else
			echo "<a href=\"email.php?selectCategory=".$category->getID()."\"><img src=\"skins/$skin/img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=".$category->getID()."\">".htmlspecialchars($category->getName())."</a><br />\n";
	}
	echo "</div></div><div id=\"emailbox\">\n";
	
	if($currentCat)
	{
		$emails = $currentCat->getEmailsSortedBy($_SESSION['emailSort']);
		$name = htmlspecialchars($currentCat->getName());
		$desc = htmlspecialchars($currentCat->getDesc());
	}
	else
	{
		$emails = $currentGroup->getGroupEmailsSortedBy($_SESSION['emailSort']);		
		$name = 'Uncategorized';
		$desc = 'These emails are not in any particular category';
	}
	
	echo "<div class=\"columnbanner\"><span id=\"boxtitle\">$name</span><br /><span id=\"boxdesc\">$desc</span></div>\n";
	echo "<form method=\"post\" action=\"email.php\"><fieldset><div class=\"menubar\">\n";
	if(!$currentCat || $currentCat->getID() != 1)
	{
		echo "<ul class=\"folderlist\">\n";
		if(!$currentUser->isGroupGuest($currentGroup))
			echo "<li><a href=\"sendemail.php\" onclick=\"sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php', 'Send Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Send Email</a></li>\n";
		echo "<li><a href=\"#\" onclick=\"searchwin=dhtmlwindow.open('searchbox', 'div', 'searchFrame', 'Search Group Emails', 'width=300px,height=200px,left=300px,top=100px,resize=1,scrolling=1'); return false\">Search Email</a></li>\n";
		if (!$currentUser->isGroupGuest($currentGroup) && count($emails) > 0)
		{
			if(count($currentGroup->getGroupCategories()) > 0)
				echo "<li><a href=\"#\" onclick=\"movewin=dhtmlwindow.open('movebox', 'div', 'moveFrame', 'Move Email', 'width=200px,height=100px,left=600px,top=100px,resize=0,scrolling=0'); return false\">Move Selected</a></li>\n";
			if($currentUser->isGroupModerator($currentGroup))
				echo "<li><a href=\"#\" onclick=\"document.getElementById('delete').value='1'; document.getElementById('delete').form.submit()\">Delete Selected</a><input type=\"hidden\" id=\"delete\" name=\"delete\" value=\"0\" /></li>\n";
		}
		echo "</ul>\n";
	}
	echo "</div><div id=\"emails\">\n<table width=\"100%\">\n<tr class=\"sortbar\">\n";
	if($_SESSION['emailSort'] == 1)
		echo "<td colspan=\"2\"><a href=\"email.php?sort=-1\" title=\"Sort this descendingly\">Subject &#x2193;</a></td>";
	else if($_SESSION['emailSort'] == -1)
		echo "<td colspan=\"2\"><a href=\"email.php?sort=1\" title=\"Sort this ascendingly\">Subject &#x2191;</a></td>";
	else
		echo "<td colspan=\"2\"><a href=\"email.php?sort=1\" title=\"Sort by subject\">Subject</a></td>";
	if($_SESSION['emailSort'] == 2)
		echo "<td><a href=\"email.php?sort=-2\" title=\"Sort this descendingly\">Author &#x2193;</a></td>";
	else if($_SESSION['emailSort'] == -2)
		echo "<td><a href=\"email.php?sort=2\" title=\"Sort this ascendingly\">Author &#x2191;</a></td>";
	else
		echo "<td><a href=\"email.php?sort=2\" title=\"Sort by author\">Author</a></td>";
	if($_SESSION['emailSort'] == 3)
		echo "<td><a href=\"email.php?sort=-3\" title=\"Sort this descendingly\">Date &#x2193;</a></td>";
	else if($_SESSION['emailSort'] == -3)
		echo "<td><a href=\"email.php?sort=3\" title=\"Sort this ascendingly\">Date &#x2191;</a></td>";
	else
		echo "<td><a href=\"email.php?sort=-3\" title=\"Sort by date\">Date</a></td>";
	
	echo "<td></td></tr>\n";
			
	if(count($emails) > 0)
	{
		foreach($emails as $email)
		{
			$author = $email->getSender();
			printTR();
			if ($email->hasAttachments()) 
				$img = '&nbsp;<img src="skins/'.$skin.'/img/attach.png" alt="(Attachments)" style="border-style: none" title="Paper clip" />';
			else
				$img = '';
			echo "<td colspan=\"2\"><a href=\"displayemail.php?id={$email->getID()}\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->getID()."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">".htmlspecialchars($email->getShortSubject())."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDateDB()."</td><td><input type=\"checkbox\" name=\"email[".$email->getID()."]\" /></td>";
			echo "</tr>\n";
		} 
	}
	else
		echo "<tr><td>There are no emails in the selected category.</td></tr>\n";
	echo "</table>\n</div></fieldset></form>\n</div>";
  echo " <br class=\"clearleft\" />";
  echo "</div>\n";
	
	if(!$currentUser->isGroupGuest($currentGroup))
	{
?>
		<div class="window-content" id="createCat" style="display: none">
			<form method="post" action="email.php"><fieldset>
				<label for="catname">Category Name:</label><input type="text" name="catname" id="catname" /><br />
				<label for="catdesc">Category Description:</label><input type="text" name="catdesc" id="catdesc" /><br />
				<input type="submit" name="createcat" value="Create Category" />
			</fieldset></form>
		</div>
<?php
	}
	if($currentUser->isGroupModerator($currentGroup) && $currentCat)
	{
?>
		<div class="window-content" id="editCat" style="display: none">
		<form method="post" action="email.php"><fieldset>
<?php
		if($currentCat)
		{
			echo "Current Category Name: ".$currentCat->getName()."<br />";
			echo "<label for=\"newcatname\">New Category Name:</label><input type=\"text\" name=\"newcatname\" id=\"newcatname\" value=\"".$currentCat->getName()."\" /><br />";
			echo "<label for=\"newcatdesc\">New Category Description:</label><input type=\"text\" name=\"newcatdesc\" id=\"newcatdesc\" value=\"".$currentCat->getDesc()."\" /><br />";
			echo '<input type="submit" name="editcat" value="Edit Category" />';
			echo '<input type="submit" name="delcat" value="Delete Category" />';
		}
		else
			echo "You cannot edit the current active category.";
?>
		</fieldset></form>
		</div>
<?php
	}
	if(count($emails) > 0)
	{
		echo "<div class=\"window-content\" id=\"moveFrame\" style=\"display: none\">\n";
		$categories = $currentGroup->getGroupCategories();
?>
		<form method="post" action="email.php"><fieldset>
		<label for="targetcategory">Move email to category:</label>
		<select name="targetcategory" id="targetcategory"><option value="0">Uncategorized</option>
<?php
		$categories = $currentGroup->getGroupCategories();
		foreach($categories as $category)
			echo "<option value=\"".$category->getID()."\">".$category->getName()."</option>";
		echo "</select><input type=\"hidden\" name=\"emailMove\" />";
?>
		<br />
		<input type="button" name="move" value="Move Emails" onclick="copyCheckBoxes();this.form.submit()" /></fieldset></form></div>
<?php
	}
?>
	</div><div class="window-content" id="searchFrame" style="display: none">
	<form method="post" action="searchemail.php"><fieldset><legend>Search Group Emails</legend>
	<label for="subjectSearch">Subject:</label>&nbsp;<input type="text" name="subjectSearch" id="subjectSearch" /><br />
	<label for="bodySearch">Body:</label>&nbsp;<input type="text" name="bodySearch" id="bodySearch" /><br />
	<label for="senderSearch">Sender:</label>&nbsp;<select name="senderSearch" id="senderSearch"><option value="-1">Any</option>
<?php
	$people = $currentGroup->getGroupMembers();
	$iid = '';
	$i = 0;
	foreach($people as $person)
	{
		if($i)
			$iid .= ',';
		$i++;
		$iid .= $person->getID();
	}
	$query = $db->query("select iID, sLName, sFName from People where iID in ($iid) order by sLName, sFName");
	while($row = mysql_fetch_row($query))
		echo "<option value=\"".$row[0]."\">".$row[1].", ".$row[2]."</option>\n";
?>			
	</select><br />
	<label for="categorySearch">Category:</label>&nbsp;<select name="categorySearch" id="categorySearch"><option value="-1">Any</option><option value="0">Uncategorized</option>
<?php
	$categories = $currentGroup->getGroupCategories();
	foreach($categories as $category)
		echo "<option value=\"".$category->getID()."\">".$category->getName()."</option>\n";
?>
	</select><br />
	<input type="submit" name="search" /></fieldset></form>
	<p style="font-size: smaller"><strong>Note:</strong> Subject and body search terms use <a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" onclick="window.open(this.href); return false;">implied boolean logic</a>.</p>
</div>
<?php
//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>	
</body></html>
