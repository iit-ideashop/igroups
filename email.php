<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/category.php" );
	include_once( "classes/email.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else if ( isset($_POST['login'] ) && isset($_GET['replyid']) ) {
		
		if ( strpos( $_POST['username'], "@" ) === FALSE ) 
			$_POST['username'] .= "@iit.edu";
			
		$user = $db->iknowQuery( "SELECT iID,sPassword FROM People WHERE sEmail='".$_POST['username']."'" );
		
		if ( ( $row = mysql_fetch_row( $user ) ) && ( md5($_POST['password']) == $row[1] ) ) {
			$_SESSION['userID'] = $row[0];
			$email = new Email($_GET['replyid'], $db);
			$_SESSION['selectedGroup'] = $email->getGroupID();
			$_SESSION['selectedGroupType'] = $email->getGroupType();
			$_SESSION['selectedSemester'] = $email->getSemester();
			if ( isset( $_POST["remember"] ) ) {
				setcookie( "iUserID", $_SESSION['userID'], time()+1209600 );
			}
		}
		else {
			$errorMsg = "Invalid username or password";
		}
	}
	else if(isset($_GET['replyid']))
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Log In</title>
<link rel="stylesheet" href="default.css" type="text/css" />
</head><body>
<h1>Login</h1>
<?php
		if ( isset( $errorMsg ) ) {
			print $errorMsg."<br />";
		}
		print "<form method=\"post\" action=\"email.php?replyid=".$_GET['replyid']."\">";
?>
			User name: <input name="username" type="text" /><br />
			Password: <input name="password" type="password" /><br />
			<input type='checkbox' name='remember' /> Remember me?<br />
			<input type="submit" name="login" value="Login" />
		</form>
</body></html>
<?php
	die();
	}
	else
		die("You are not logged in.");
		 
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );

	else
		die("You have not selected a valid group.");
		
	if ( isset( $_GET['selectCategory'] ) ) {
		$_SESSION['selectedCategory'] = $_GET['selectCategory'];
	}
	
	if ( isset( $_SESSION['selectedCategory'] ) ){
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
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
		
		ul.emailul {
			list-style:none;
			padding:0;
			margin:0;
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

<script language="javascript" type="text/javascript" src="speller/spellChecker.js">
	</script>
	<script language="javascript" type="text/javascript">
	<!--
		function openSpellChecker() {
		var speller = new spellChecker();
		speller.spellCheckAll();
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
			var el = document.getElementById(id);
			for (var i = 0; i < el.elements.length; i++) {
	  			if (el.elements[i].name != 'confidential' && el.elements[i].id != 'guest' && el.elements[i].id != 'subgroup') {
					el.elements[i].checked = checked;
				}
			}
      		}

		function checkedAllGuest (id, checked) {
			var el = document.getElementById(id);
                        for (var i = 0; i < el.elements.length; i++) {
                        if (el.elements[i].id == 'guest') {
                        el.elements[i].checked = checked;
                        }
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
                                        div.innerHTML = "&nbsp;&nbsp;&nbsp;File "+(num*1+1)+": <input type='file' name='attachment"+(num*1+1)+"' onchange='fileAdd("+(num*1+1)+");' />";
                                        document.getElementById('files').appendChild(div);
                                }
                        }
	//-->
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
		$tmpstr = wordwrap($_POST['body'], 110);
		$body = new SuperString($tmpstr);
		$msg .= "--".$mime_boundary."\n";
		$msg .= "Content-Type: text/plain; charset=iso-8859-1"."\n";
		$msg .= "Content-Transfer-Encoding: 8bit"."\n"."\n";
		$msg .= $body->getString()."\n"."\n"; 
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
	}
	
	if ( isset( $_POST['delcat'] ) ) {
		$currentCat->delete();
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
	
	if ( isset( $_POST['move'] ) ) {
		$email = new Email( $_POST['email'], $db );
		if ( $currentUser->isGroupModerator( $email->getGroup() ) ) {
			$email->setCategory($_POST['targetcategory']);
			$email->updateDB();
		}
?>
		<script type="text/javascript">
			var successwin=dhtmlwindow.open('successbox', 'inline', '<p>Selected item successfully moved.</p>', 'Success', 'width=125px,height=10px,left=300px,top=100px,resize=0,scrolling=0', 'recal');
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
				<ul class="emailul"> <?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
					<li><a href="#" onclick="ccatwin=dhtmlwindow.open('ccatbox', 'div', 'createCat', 'Create Category', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Create Category</a></li>
					<?php
                                        if ( $currentUser->isGroupModerator( $currentGroup ) ) {
					?>
                                                <li><a href="#" onclick="ecatwin=dhtmlwindow.open('ecatbox', 'div', 'editCat', 'Edit Category', 'width=250px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false">Edit/Delete Category</a></li>
					<?php } ?>
				</ul>
				<?php } ?>
			</div>
			<div id="cats">
<?php
				$categories = $currentGroup->getGroupCategories();
				if ( $currentCat )
					print "<a href='email.php?selectCategory=0'><img src=\"img/folder.png\" border=\"0\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href='email.php?selectCategory=0'>Uncategorized</a><br />";
				else
					print "<a href='email.php?selectCategory=0'><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href='email.php?selectCategory=0'><strong>Uncategorized</strong></a><br />";
				foreach ( $categories as $category ) {
					if ( $currentCat && $currentCat->getID() == $category->getID() )
						print "<a href='email.php?selectCategory=".$category->getID()."'><img src=\"img/folder-expanded.png\" border=\"0\" alt=\"-\" title=\"Open folder\" /></a>&nbsp;<a href='email.php?selectCategory=".$category->getID()."'><strong>".$category->getName()."</strong></a><br />";
					else
						print "<a href='email.php?selectCategory=".$category->getID()."'><img src=\"img/folder.png\" border=\"0\" alt=\"+\" title=\"Folder\" /></a>&nbsp;<a href='email.php?selectCategory=".$category->getID()."'>".$category->getName()."</a><br />";
				}
?>
			</div>
		</div>
		<div id="emailbox">
<?php
			if ( $currentCat ) {
				$emails = $currentCat->getEmails();
				$name = $currentCat->getName();
			}
			else {
				$emails = $currentGroup->getGroupEmails();		
				$name = "Uncategorized";
			}
			
			print "<div class='columnbanner'>Contents of $name:</div>";
?>
			<form method="post" action="email.php"><div class="menubar">
			<?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
				<ul class="emailul">
					<li><a href="#" onclick="sendwin=dhtmlwindow.open('sendbox', 'ajax', 'sendemail.php', 'Send Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false">Send Email</a></li>
					<li><a href="#" onclick="window.location.href='searchemail.php';">Search Email</a></li>
<?php
					if ( $currentUser->isGroupModerator( $currentGroup ) ) {
?>
						<li><a href="#" onclick="document.getElementById('delete').value='1'; document.getElementById('delete').form.submit()">Delete Selected</a>
						<input type='hidden' id='delete' name='delete' value='0' /></li>
<?php
					}
?>
				</ul>
			<?php } ?>
			</div>
			<div id="emails">
				<table width='100%'>
<?php				
				foreach ( $emails as $email ) {
					$author = $email->getSender();
					printTR();
					if ($email->hasAttachments()) 
						$img = '&nbsp;<img src="img/attach.png" alt="(Attachments)" border="0" title="Paper clip" />';
					else
						$img = '';
					print "<td colspan='2'><a href=\"#\" onclick=\"viewwin=dhtmlwindow.open('viewbox', 'ajax', 'displayemail.php?id=".$email->getID()."', 'Display Email', 'width=650px,height=600px,left=300px,top=100px,resize=1,scrolling=1'); return false\">".str_replace("&", "&amp;", $email->getShortSubject())."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td><td><input type='checkbox' name='email[".$email->getID()."]' /></td><td><a href=\"#\" onclick=\"movewin=dhtmlwindow.open('movebox', 'ajax', 'move.php?id=".$email->getID()."', 'Move Email', 'width=200px,height=100px,left=600px,top=100px,resize=0,scrolling=0'); return false\">Move</a></td></tr>";
				}
?>
				</table>
			</div></form>
		</div>
	</div>
		
		<div class="window-content" id="createCat" style="display: none">
			<form method="post" action="email.php">
				Category Name: <input type="text" name="catname" /><br />
				Category Description:<input type="text" name="catdesc" /><br />
				<input type="submit" name="createcat" value="Create Category" />
			</form>
		</div>
		<div class="window-content" id="editCat" style="display: none">
			<form method="post" action="email.php">
<?php
				if ( $currentCat ) {
					print "Current Category Name: ".$currentCat->getName()."<br />";
					print "New Category Name: <input type='text' name='newcatname' value='".$currentCat->getName()."' /><br />";
					print "New Category Description: <input type='text' name='newcatdesc' value='".$currentCat->getDesc()."' /><br />";
					print '<input type="submit" name="editcat" value="Edit Category" />';
					print '<input type="submit" name="delcat" value="Delete Category" />';
				}
				else {
					print "You cannot edit the current active category.";
				}
?>
			</form>
	</div></div>
</body>
</html>
