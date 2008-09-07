<?php
	session_start();
	ini_set(”memory_limit”,”16M”);

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/subgroup.php" );
	include_once( "classes/category.php" );
	include_once( "classes/email.php" );
	
	$db = new dbConnection();
	
	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else if(isset($_COOKIE['userID']) && isset($_COOKIE['password']) && isset($_COOKIE['selectedGroup']))
	{
		if(strpos($_COOKIE['userID'], "@") === FALSE)
			$userName = $_COOKIE['userID']."@iit.edu";
		else
			$userName = $_COOKIE['userID'];
		$user = $db->iknowQuery("SELECT iID,sPassword FROM People WHERE sEmail='".$userName."'");
		if(($row = mysql_fetch_row($user)) && (md5($_COOKIE['password']) == $row[1]))
		{
			$_SESSION['userID'] = $row[0];
			$currentUser = new Person($row[0], $db);
			$group = explode(",", $_COOKIE['selectedGroup']);
			$_SESSION['selectedGroup'] = $group[0];
			$_SESSION['selectedGroupType'] = $group[1];
			$_SESSION['selectedSemester'] = $group[2];
		}
	}
	else
		die("You are not logged in.");
	if ( isset($_SESSION['selectedGroup']) && isset($_SESSION['selectedGroupType']) && isset($_SESSION['selectedSemester']) )
		$currentGroup = new Group( $_SESSION['selectedGroup'], $_SESSION['selectedGroupType'], $_SESSION['selectedSemester'], $db );
	else
		die("You have not selected a valid group.");
		
	if ( !$currentUser->isGroupMember( $currentGroup ) )
		die("You are not a member of this group." );
		
	function peopleSort( $array ) {
		$newArray = array();
		foreach ( $array as $person ) {
			$newArray[$person->getCommaName()] = $person;
		}
		ksort( $newArray );
		return $newArray;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Send Email</title>
	<link href="default.css" rel="stylesheet" type="text/css">
	<script language="javascript" type="text/javascript" src="speller/spellChecker.js">
	</script>
	<script language="javascript" type="text/javascript">
		function openSpellChecker() {
		var speller = new spellChecker();
		speller.spellCheckAll();
		}
	</script>
	<script>
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
	  		if (el.elements[i].name != 'confidential') {
			if (el.elements[i].id != 'guest' && el.elements[i].id != 'subgroup') {
			el.elements[i].checked = checked;
			}
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

		function init() {
			guestbox = document.getElementById('guest-table');
			guestbox.style.display='none';
		}

	</script>
	<script>
                        function fileAdd(num) {
                                if (document.getElementById('files').childNodes.length == num) {
                                        var div = document.createElement('div');
                                        div.className = "stdBoldText";
                                        div.id = "file"+(num*1+1)+"div";
                                        div.innerHTML = "&nbsp;&nbsp;&nbsp;File "+(num*1+1)+": <input type='file' name='attachment"+(num*1+1)+"' onChange='fileAdd("+(num*1+1)+");'>";
                                        document.getElementById('files').appendChild(div);
                                }
                        }
        </script>
</head>
<body onload="init()">
<?php		
	if ( isset( $_POST['send'] ) ) {
		$to = array();
		$names = array();
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
		$tmpstr = wordwrap($_POST['body'], 100);
		$body = new SuperString($tmpstr);
		$msg .= "--".$mime_boundary."\n";
		$msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
		$msg .= "Content-Transfer-Encoding: 8bit"."\n"."\n";
		$msg .= $body->getHTMLString()."\n"."\n"; 
		if ( !isset( $_POST['confidential'] ) )
			$msg .= '<p><a href="http://igroups.iit.edu/reply.php?replyTo=abxyqzta10">Click here to reply to this email.</a></p>'."\n"."\n";
		$msg .= "--".$mime_boundary.'--'."\n"."\n";
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
		print "<p>Your email was successfully sent.  You can use the form below to send additional emails.</p>";
	}
?>
	<form method="post" action="sendemail.php" enctype="multipart/form-data" id="mailform">
		<div id="to">		
			<a href="#" onClick="toggleToDisplay()">+</a> To:
			<table id="to-table" width='100%'>
<?php
				$members = $currentGroup->getGroupMembers();
				$members = peopleSort( $members );
				$i=1;
				foreach ( $members as $person ) {
					if ( $i == 1 ) 
						print "<tr>";
					print "<td><input type='checkbox' name='sendto[".$person->getID()."]'></td>";
					
print "<td>".$person->getFullName()."</td>";
					if ( $i == 3) {
						print "</tr>";
						$i = 1;
					}
					else
						$i++;
				}
				
?>			
			<tr><td colspan=2><a href="javascript:checkedAll('mailform', true)">Check All</a> / <a href="javascript:checkedAll('mailform', false)">Uncheck All</a></td></tr>
			</table>
		</div><br>
		<div id="subgroups">
			<a href='#' onClick="toggleSGDisplay()">+</a> Subgroups:
			<table id="subgroups-table" width='100%'>
<?php
			$subgroups = $currentGroup->getSubGroups();
			$i=1;
			foreach ($subgroups as $subgroup) {
				if ($i == 1)
					print "<tr>";
				print "<td><input type='checkbox' id='subgroup' name='sendtosubgroup[".$subgroup->getID()."]'>&nbsp;";
				print $subgroup->getName()."</td>";
				if ($i == 3) {
					print "</tr>";
					$i=1;
				}
				else
					$i++;
			}
?>
			</table>
		</div>
		<div id="guests">
<?php
	$members = $currentGroup->getGroupGuests();
	if (count($members) > 0) {
?>
                        <br><a href="#" onClick="toggleGuestDisplay()">+</a> Guests:
                        <table id="guest-table" width='100%'>
<?php
                                $members = peopleSort( $members );
                                $i=1;
                                foreach ( $members as $person ) {
                                        if ( $i == 1 )
                                                print "<tr>";
                                        print "<td><input type='checkbox' id='guest' name='sendtoguest[".$person->getID()."]'></td>";
                                        print "<td>".$person->getFullName()."</td>";
                                        if ( $i == 3) {
                                                print "</tr>";
                                                $i = 1;
                                        }
                                        else
                                                $i++;
                                }

?>
			<tr><td colspan=2><a href="javascript:checkedAllGuest('mailform', true)">Check All</a> / <a href="javascript:checkedAllGuest('mailform', false)">Uncheck All</a></td></tr>
                        </table>
                </div>
<?php } ?>		
		<br />
		<table>
			<tr><td>CC:</td><td><input type="text" size=50 name="cc"></td></tr>
			<tr><td>Subject:</td><td><input type="text" size=50 name="subject"></td></tr>
			<tr><td><input type="checkbox" name="confidential"></td><td>Keep confidential? (if checked, will not be stored in iGROUPS)</td></tr>
			<tr><td>Category</td><td><select name="category"><option value="0">No Category</option>
<?php
			$categories = $currentGroup->getGroupCategories();
			foreach ( $categories as $category ) {
				print "<option value=".$category->getID().">".$category->getName()."</option>";
			}
?>
			</select></td></tr>
			<tr><td>Attachments:</td></tr>
			<tr><td colspan='2'>
			<div id='files'><?php
                        //$a = 1;
                        ?><div class="stdBoldText" id='file1div'>&nbsp;&nbsp;&nbsp;File 1: <input type="file" name="attachment1" onChange='fileAdd(1);'></div><?php
                ?></div>
                <!--<span onclick='fileAdd(document.getElementById("files").childNodes.length);' style='color:#00F;text-decoration:underline;cursor:pointer;'>Click here to add another file.</span>--></div>
			</td></tr>
			<tr><td colspan=2>Body:</td></tr>
			<tr><td colspan=2><textarea name="body" cols="54" rows="10"></textarea></td></tr>
			<tr><td colspan=2 align="center"><input type='button' value='Spell Check' onClick="openSpellChecker();">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="send" value="Send Email" /></td></tr>
		</table>
<?php
		if ( isset( $_GET['replyid'] ) )
			print "<input type='hidden' name='replyid' value=".intval($_GET['replyid']).">";
		else
			print "<input type='hidden' name='replyid' value=0>";
?>
	</form>
</body>
</html>
