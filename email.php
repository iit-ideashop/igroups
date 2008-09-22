<?php
	include_once("checklogingroupless.php");
	include_once( "classes/group.php" );
	include_once( "classes/category.php" );
	include_once( "classes/email.php" );
	
	if(isset($_GET['replyid']) && is_numeric($_GET['replyid']))
	{
		$email = new Email($_GET['replyid'], $db);
		$currentGroup = new Group($email->getGroupID(), $email->getGroupType(), $email->getSemester(), $db);
		if(!$currentGroup->isGroupMember($currentUser))
			die("You are not a member of this group.");
		$_SESSION['selectedGroup'] = $email->getGroupID();
		$_SESSION['selectedGroupType'] = $email->getGroupType();
		$_SESSION['selectedSemester'] = $email->getSemester();
	}
	else if(isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']))
	{
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
		if(!$currentGroup->isGroupMember($currentUser))
		{
			unset($_SESSION['selectedGroup']);
			unset($_SESSION['selectedGroupType']);
			unset($_SESSION['selectedSemester']);
			setcookie('selectedGroup', '', time()-60);
			die("You are not a member of this group.");
		}
	}
	else if(isset($_COOKIE['selectedGroup']))
	{
		$group = explode(",", $_COOKIE['selectedGroup']);
		$currentGroup = new Group( $group[0],$group[1], $group[2], $db );
		if(!$currentGroup->isGroupMember($currentUser))
		{
			setcookie('selectedGroup', '', time()-60);
			die("You are not a member of this group.");
		}
		$_SESSION['selectedGroup'] = $group[0];
		$_SESSION['selectedGroupType'] = $group[1];
		$_SESSION['selectedSemester'] = $group[2];
		
	}
	else
		die("You have not selected a valid group.");
	
	if ( isset( $_GET['selectCategory'] ) ) {
		$_SESSION['selectedCategory'] = $_GET['selectCategory'];
	}
	
	if(isset($_SESSION['selectedCategory']) && $_SESSION['selectedCategory'] == 0)
	{
		unset($_SESSION['selectedCategory']);
		$currentCat = false;
	}
	else if ( isset( $_SESSION['selectedCategory'] ) ){
		$currentCat = new Category( $_SESSION['selectedCategory'], $db );
		if(!$currentCat->getGroupID())
			$currentCat->setGroup($currentGroup->getID());
		if(!$currentCat->getSemester())
			$currentCat->setSemester($currentGroup->getSemester());
		if(!$currentCat->getGroupType())
			$currentCat->setType($currentGroup->getType());
	}
	else
		$currentCat = false;
		
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class=\"shade\">";
		else
			print "<tr>";
		$i=!$i;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Group Email</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	<style type="text/css">		
		#container {
			padding:0;
		}
		
		#catbox {
			float:left;
			width:25%;
			margin:5px;
			padding:2px;
			border:1px solid #000;
		}
		
		#cats {
			width:100%;
			text-align:left;
			background-color: #fff;
			padding-top:5px;
		}
		
		#emailbox {
			float:left;
			margin:5px;
			padding:2px;
			width:55%;
			border:1px solid #000;
		}
		
		#emails {
			width:100%;
			text-align:left;
			background-color:#fff;
		}
		
		.menubar {
			background-color:#eeeeee;
			margin-bottom:5px;
			padding:3px;
		}
		
		.menubar li {
			padding:5px;
			display:inline;
		}
	</style>

<link rel="stylesheet" href="windowfiles/dhtmlwindow.css" type="text/css" />
<script type="text/javascript" src="windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript" src="speller/spellChecker.js"></script>
	<script type="text/javascript">
	//<![CDATA[
		function openSpellChecker() {
		var speller = new spellChecker();
		speller.checkTextAreas();
		}

		function toggleToDisplay() {
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

		function toggleSGDisplay() {
			box = document.getElementById('subgroups-table');
			switch (box.style.display) {
				case 'none':
					box.style.display='block';
					break;
				default:
					box.style.display='none';
					break;
			}
		}

		function toggleGuestDisplay() {
			guestbox = document.getElementById('guest-table');
			switch (guestbox.style.display) {
				case 'none':
					guestbox.style.display='block';
					break;
				default:
					guestbox.style.display='none';
					break;
			}
		}


		function checkedAll (id, checked) {
			var el = document.getElementByTagName("input");
			var guest = new RegEx("guest.", "i"), subg = new RegEx("subgroup.", "i");
			for(var i = 0; i < el.elements.length; i++) {
	  			if(el.elements[i].name != 'confidential' && !guest.test(el.elements[i].id + '.') && !subg.test(el.elements[i].id + '.'))
					el.elements[i].checked = checked;
			}
      		}

		function checkedAllGuest (id, checked) {
			var el = document.getElementsByTagName("input");
			var guest = new RegEx("guest.", "i");
			for(var i = 0; i < el.elements.length; i++) {
				if(guest.test(el.elements[i].id))
					el.elements[i].checked = checked;
			}
		}

		function sendinit() {
			guestbox = document.getElementById('guest-table');
			guestbox.style.display='none';
		}
			function fileAdd(num) {
				if (document.getElementById('files').childNodes.length == num) {
					var div = document.createElement('div');
					div.className = "stdBoldText";
					div.id = "file"+(num*1+1)+"div";
					div.innerHTML = "&nbsp;&nbsp;&nbsp;<label for=\"attachment"+(num*1+1)+"\">File "+(num*1+1)+":</label> <input type=\"file\" name=\"attachment"+(num*1+1)+"\" id=\"attachment"+(num*1+1)+"\" onchange=\"fileAdd("+(num*1+1)+");\" />";
					document.getElementById('files').appendChild(div);
				}
			}
		function copyCheckBoxes() {
			var emails = new Array();
			var inputs = document.getElementsByTagName('input');
			for ( var i=0; i < inputs.length; i++ ) {
				if ( inputs[i].type == "checkbox" && inputs[i].checked ) {
					values = inputs[i].name.split( /\x5b|\x5d/ );
					emails.push( values[1] );
				}
			}
			var emailInputs = document.getElementsByName( "emailMove" );
			for ( var i=0; i < emailInputs.length; i++ )
				emailInputs[i].value=emails;
		}
	//]]>
	</script>
</head>
<body>
<?php
require("sidebar.php");
	if ( isset( $_POST['send'] ) ) {
		if ( isset( $_POST['sendto'] )) {
		foreach ( $_POST['sendto'] as $id => $val ) {
			$person = new Person( $id, $db );
			$to[] = $person->getEmail();
			$names[] = $person->getFullName();
		}
		
		$tolist = join( ",", $to );
		$toNames = join( ", ", $names );
		}

		if (isset($_POST['sendtosubgroup'])) {
		foreach ($_POST['sendtosubgroup'] as $key => $val) {
			$subgroup = new SubGroup($key, $db);
			$people = $subgroup->getSubGroupMembers();
			foreach($people as $person) {
				$to[] = $person->getEmail();
			}
			$names[] = $subgroup->getName();
		}
		$tolist = join(",", $to);
		$toNames = join(", ", $names);
		}

		if (isset($_POST['sendtoguest'] )) {
		foreach ($_POST['sendtoguest'] as $id => $val) {
			$person = new Person($id, $db);
			$to[] = $person->getEmail();
			$names[] = $person->getFullName();
		}
		$tolist = join(",", $to);
		$toNames = join(", ", $names);
		}
		
		$headers = "From: ".$currentUser->getFullName()." <".$currentUser->getEmail().">\n";
		if ( isset($_POST['cc']) && ($_POST['cc'] != ''))
			$headers .= "Cc:".$_POST['cc']."\n";
		$mime_boundary=md5(time());
		$headers .= 'MIME-Version: 1.0'."\n";
		$headers .= 'Content-Type: multipart/mixed; boundary="'.$mime_boundary.'"'."\n";
		$headers .= 'Content-Transfer-Encoding: 8bit'."\n";
		$msg = "";
		for ($i=1; $i<=(count($_FILES)); $i++) {
		if ( $_FILES["attachment$i"]['size'] != 0 ) {
			$handle=fopen($_FILES["attachment$i"]['tmp_name'], 'rb');
			$f_contents=fread($handle, $_FILES["attachment$i"]['size']);
			$f_contents=chunk_split(base64_encode($f_contents));
			fclose($handle); 
			$filename = str_replace(' ', '_', $_FILES["attachment$i"]['name']);
			$msg .= "--".$mime_boundary."\n";
			$msg .= "Content-Type: {$_FILES["attachment$i"]['type']};\n name={$filename}\n";
			$msg .= "Content-Transfer-Encoding: base64"."\n";
			$msg .= "Content-Disposition: attachment; filename=".$filename."\n"."\n";
			$msg .= $f_contents."\n"."\n";
			if (!isset($_POST['confidential'])) {
				$db->igroupsQuery("INSERT INTO EmailFiles (iEmailID, sOrigName, sMimeType) VALUES (0, '$filename', '{$_FILES["attachment$i"]['type']}')");
				$id = $db->igroupsInsertID();
				$db->igroupsQuery("UPDATE EmailFiles SET sDiskName='$id.att' WHERE iID=$id");
				move_uploaded_file($_FILES["attachment$i"]['tmp_name'], "/files/igroups/emails/$id.att");
			}
		}
		}
		//$tmpstr = wordwrap($_POST['body'], 110);
		$body = new SuperString($_POST['body']);
		$msg .= "--".$mime_boundary."\n";
		$msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
		$msg .= "Content-Transfer-Encoding: 8bit"."\n"."\n";
		$msg .= anchorTags(htmlspecialchars($body->getString()))."\n"."\n"; 
		if ( !isset( $_POST['confidential'] ) ) {
			$msg .= "--".$mime_boundary."\n";
			$msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
			$msg .= "Content-Transfer-Encoding: 8bit"."\n"."\n";
			$msg .= '<p><a href="http://igroups.iit.edu/email.php?replyid=abxyqzta10">Click here to reply to this email.</a></p>'."\n"."\n";
		}
		$msg .= "--".$mime_boundary.'--'."\n";
		if ( isset( $_POST['confidential'] ) ) {
			$subj = new SuperString( $_POST['subject'] );
			mail( $tolist, "[".$currentGroup->getName()."] ".stripslashes($_POST['subject']), $msg, $headers );
		}
		else {
			if ($_POST['subject'] == "")
				$_POST['subject'] = "(no subject)";
			$newEmail = createEmail( $toNames, $_POST['subject'], $_POST['body'], $currentUser->getID(), $_POST['category'], $_POST['replyid'], $currentGroup->getID(), $currentGroup->getType(), $currentGroup->getSemester(), $db );
			$query = $db->igroupsQuery("UPDATE EmailFiles SET iEmailID={$newEmail->getID()} WHERE iEmailID=0");
			$msg = str_replace( "abxyqzta10", $newEmail->getID(), $msg );
			mail( $tolist, "[".$currentGroup->getName()."] ".stripslashes($_POST['subject']), $msg, $headers );
		}
		print "<script type=\"text/javascript\">var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Your email was sent successfully.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal')</script>";
	}
	else if (isset($_GET['replyid'])) {
		print "<script type=\"text/javascript\">var sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php?replyid=".$_GET['replyid']."', 'Send Reply', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";
	}
	else if (isset($_GET['forward'])) {
		print "<script type=\"text/javascript\">var sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php?forward=".$_GET['forward']."', 'Forward Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";
	}
	else if(isset($_GET['display'])) {
		print "<script type=\"text/javascript\">var viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$_GET['display']."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1', 'recal')</script>";
	}
?>
	<div id="content"><div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
<?php
	if ( isset( $_POST['createcat'] ) ) {
		
		createCategory( $_POST['catname'], $_POST['catdesc'], $currentGroup->getID(), $currentGroup->getType(), $currentGroup->getSemester(), $db );
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Category created.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if ( isset( $_POST['editcat'] ) ) {
		$currentCat->setName( $_POST['newcatname'] );
		$currentCat->setDesc( $_POST['newcatdesc'] );
		$currentCat->updateDB();
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Category edited.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if ( isset( $_POST['delcat'] ) && $currentCat && $currentCat->getID() != 1) {
		$currentCat->delete();
		unset($_SESSION['selectedCategory']);
		$currentCat = false;
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Category deleted.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	else if(isset($_POST['delcat'])) {
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>You cannot delete this category.</p>', 'Error', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}
	
	if ( isset( $_POST['delete'] ) && $_POST['delete']==1 && isset($_POST['email'])) {
		foreach( $_POST['email'] as $emailid => $val ) {
			$email = new Email( $emailid, $db );
			if ( $currentUser->isGroupModerator( $email->getGroup() ) )
				$email->delete();
		}
		if ( isset( $_POST['categories'] )) {
		foreach( $_POST['categories'] as $catid => $val ) {
			$category = new Category( $catid, $db );
			if ( $currentUser->isGroupModerator( $category->getGroup() ) ) {
				$emails = $category->getEmails();
				foreach ( $emails as $email ) {
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
	
	if ( isset( $_POST['emailMove'] ) && $_POST['emailMove'] != "") {
		$_POST['emailMove'] = explode( ",", $_POST['emailMove'] );
		foreach( $_POST['emailMove'] as $key => $val ) {
			$email = new Email( $val, $db );
			if ( $currentUser->isGroupModerator( $email->getGroup() ) ) {
				$email->setCategory($_POST['targetcategory']);
				$email->updateDB();
			}
		}
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Selected emails successfully moved.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
		</script>
<?php
	}	
?>
	<div id="container">
		<div id="catbox">
			<div class="columnbanner">
				Your categories:
			</div>
			<div class="menubar">
				<ul class="folderlist"> <?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
					<li><a href="#" onclick="ccatwin=dhtmlwindow.open('ccatbox', 'div', 'createCat', 'Create Category', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Create Category</a></li>
					<?php
					if ( $currentUser->isGroupModerator( $currentGroup ) && $currentCat && $currentCat->getID() != 1) {
					?>
						<li><a href="#" onclick="ecatwin=dhtmlwindow.open('ecatbox', 'div', 'editCat', 'Edit Category', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Edit/Delete Category</a></li>
					<?php } ?>
				</ul>
				<?php } ?>
			</div>
			<div id="cats">
<?php
				$categories = $currentGroup->getGroupCategories();
				if(!$currentCat)
					print "<a href=\"email.php?selectCategory=0\"><img src=\"img/folder-expanded.png\" style=\"border-style: none\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=0\"><strong>Uncategorized</strong></a><br /><a href=\"email.php?selectCategory=1\"><img src=\"img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=1\">IPRO Office Notices</a><br />";
				else if($currentCat->getID() == 1)
					print "<a href=\"email.php?selectCategory=0\"><img src=\"img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=0\">Uncategorized</a><br /><a href=\"email.php?selectCategory=1\"><img src=\"img/folder-expanded.png\" style=\"border-style: none\" alt=\"+\" title=\"Open folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=1\"><strong>IPRO Office Notices</strong></a><br />";
				else
					print "<a href=\"email.php?selectCategory=0\"><img src=\"img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=0\">Uncategorized</a><br /><a href=\"email.php?selectCategory=1\"><img src=\"img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=1\">IPRO Office Notices</a><br />";
				
				foreach ( $categories as $category ) {
					if ( $currentCat && $currentCat->getID() == $category->getID() )
						print "<a href=\"email.php?selectCategory=".$category->getID()."\"><img src=\"img/folder-expanded.png\" style=\"border-style: none\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=".$category->getID()."\"><strong>".htmlspecialchars($category->getName())."</strong></a><br />";
					else
						print "<a href=\"email.php?selectCategory=".$category->getID()."\"><img src=\"img/folder.png\" style=\"border-style: none\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href=\"email.php?selectCategory=".$category->getID()."\">".htmlspecialchars($category->getName())."</a><br />";
				}
?>
			</div>
		</div>
		<div id="emailbox">
<?php
			if ( $currentCat ) {
				$emails = $currentCat->getEmails();
				$name = htmlspecialchars($currentCat->getName());
				$desc = htmlspecialchars($currentCat->getDesc());
			}
			else {
				$emails = $currentGroup->getGroupEmails();		
				$name = "Uncategorized";
				$desc = "These emails are not in any particular category";
			}
			
			print "<div class=\"columnbanner\"><span id=\"boxtitle\">$name</span><br /><span id=\"boxdesc\">$desc</span></div>";
?>
			<form method="post" action="email.php"><fieldset><div class="menubar">
			<?php if (!$currentUser->isGroupGuest($currentGroup) && (!$currentCat || $currentCat->getID() != 1)) { ?>
				<ul class="folderlist">
					<li><a href="#" onclick="sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php', 'Send Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false">Send Email</a></li>
					<li><a href="#" onclick="window.location.href='searchemail.php';">Search Email</a></li>
<?php
					if ( $currentUser->isGroupModerator( $currentGroup ) && count($emails) > 0) {
						if(count($currentGroup->getGroupCategories()) > 0) {
?>
						<li><a href="#" onclick="movewin=dhtmlwindow.open('movebox', 'div', 'moveFrame', 'Move Email', 'width=200px,height=100px,left=600px,top=100px,resize=0,scrolling=0'); return false">Move Selected</a></li>
<?php
						}
?>
						<li><a href="#" onclick="document.getElementById('delete').value='1'; document.getElementById('delete').form.submit()">Delete Selected</a>
						<input type="hidden" id="delete" name="delete" value="0" /></li>
<?php
					}
?>
				</ul>
			<?php } ?>
			</div>
			<div id="emails">
				<table width="100%">
<?php				
				if(count($emails) > 0) { foreach ( $emails as $email ) {
					$author = $email->getSender();
					printTR();
					if ($email->hasAttachments()) 
						$img = '&nbsp;<img src="img/attach.png" alt="(Attachments)" style="border-style: none" title="Paper clip" />';
					else
						$img = '';
					print "<td colspan=\"2\"><a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->getID()."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">".htmlspecialchars($email->getShortSubject())."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td><td><input type=\"checkbox\" name=\"email[".$email->getID()."]\" /></td>";
					print "</tr>";
				} }
				else
					print "<tr><td>There are no emails in the selected category.</td></tr>";
?>
				</table>
			</div></fieldset></form>
		</div>
	</div>
<?php
	if (!$currentUser->isGroupGuest($currentGroup)) {	
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
	if ( $currentUser->isGroupModerator( $currentGroup ) && $currentCat) {
?>
		<div class="window-content" id="editCat" style="display: none">
			<form method="post" action="email.php"><fieldset>
<?php
				if ( $currentCat ) {
					print "Current Category Name: ".$currentCat->getName()."<br />";
					print "<label for=\"newcatname\">New Category Name:</label><input type=\"text\" name=\"newcatname\" id=\"newcatname\" value=\"".$currentCat->getName()."\" /><br />";
					print "<label for=\"newcatdesc\">New Category Description:</label><input type=\"text\" name=\"newcatdesc\" id=\"newcatdesc\" value=\"".$currentCat->getDesc()."\" /><br />";
					print '<input type="submit" name="editcat" value="Edit Category" />';
					print '<input type="submit" name="delcat" value="Delete Category" />';
				}
				else {
					print "You cannot edit the current active category.";
				}
?>
			</fieldset></form>
		</div>
<?php
	}
	if ( $currentUser->isGroupModerator( $currentGroup ) && count($emails) > 0) {
?>
		<div class="window-content" id="moveFrame" style="display: none">
<?php
			$categories = $currentGroup->getGroupCategories();
?>
			<form method="post" action="email.php"><fieldset>
			<label for="targetcategory">Move email to category:</label>
			<select name="targetcategory" id="targetcategory"><option value="0">Uncategorized</option>
<?php
			$categories = $currentGroup->getGroupCategories();
			foreach ( $categories as $category ) {
				print "<option value=\"".$category->getID()."\">".$category->getName()."</option>";
			}
			print "</select><input type=\"hidden\" name=\"emailMove\" />";
?>
			<br />
			<input type="button" name="move" value="Move Emails" onclick="copyCheckBoxes();this.form.submit()" /></fieldset></form></div>
<?php
	}
?>
</div>
</body>
</html>
