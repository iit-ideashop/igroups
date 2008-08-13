<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/category.php" );
	include_once( "classes/email.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_COOKIE["iUserID"] ) && !isset($_POST['login']) ) {
		$_SESSION['userID'] = $_COOKIE["iUserID"];
		setcookie( "iUserID", $_SESSION['userID'], time()+1209600 );
	}
	
	if ( isset($_POST['login'] ) ) {
		
		if ( strpos( $_POST['username'], "@" ) === FALSE ) 
			$_POST['username'] .= "@iit.edu";
			
		$user = $db->iknowQuery( "SELECT iID,sPassword FROM People WHERE sEmail='".$_POST['username']."'" );
		
		if ( ( $row = mysql_fetch_row( $user ) ) && ( md5($_POST['password']) == $row[1] ) ) {
			$_SESSION['userID'] = $row[0];
			if ( isset( $_POST["remember"] ) ) {
				setcookie( "iUserID", $_SESSION['userID'], time()+1209600 );
			}
		}
		else {
			$errorMsg = "Invalid username or password";
		}
	}
	
	if ( isset( $_POST['forward'] ) )
		$_GET['forward'] = $_POST['forward'];
	
	if ( isset( $_SESSION['userID'] ) ) {
		$currentUser = new Person( $_SESSION['userID'], $db );
		$replyEmail = new Email( $_GET['forward'], $db );
		
		if ( !$replyEmail )
			die("You are attempting to forward an email that does not exist." );
		
		$currentGroup = $replyEmail->getGroup();

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

			function checkedAll (id, checked) {
                 	       var el = document.getElementById(id);
                        	for (var i = 0; i < el.elements.length; i++) {
                        	if (el.elements[i].name != 'confidential') {
                        		el.elements[i].checked = checked;}
                        }
                }
		</script>
	</head>
	<body>
	<h3>Forwarding an iGroups E-mail</h3>
<?php		
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
			$headers = "From: ".$currentUser->getFullName()." <".$currentUser->getEmail().">\r\n";
			//$headers .= "Reply-to: <".$currentUser->getEmail().">\n";
			//$headers .= "Return-path: <".$currentUser->getEmail().">\n";
			if ( strlen( $_POST['cc'] != 0 ) )
				$headers .= "Cc:".$_POST['cc']."\r\n";
			$mime_boundary=md5(time());
                	$headers .= 'MIME-Version: 1.0'."\r\n";
                	$headers .= 'Content-Transfer-Encoding: 7-bit'."\r\n";
                	$headers .= "Content-Type: multipart/mixed; boundary=".$mime_boundary."\n";
			$msg = "";
			if ($replyEmail->hasAttachments()) {
			$files = $replyEmail->getAttachmentInfo();
			
			//Insert attachments
                        for ($i=0; $i<(count($files)); $i++) {
                        $handle=fopen("/files/igroups/emails/{$files[$i]['sDiskName']}", 'rb');
                        $f_contents=fread($handle, filesize("/files/igroups/emails/{$files[$i]['sDiskName']}"));
                        $f_contents=chunk_split(base64_encode($f_contents));
                        fclose($handle);
                        $filename = $files[$i]['sOrigName'];
                        $msg .= "--".$mime_boundary."\n";
                        $msg .= "Content-Type: {$files[$i]['sMimeType']};\n name={$filename}\n";
                        $msg .= "Content-Transfer-Encoding: base64"."\n";
                        $msg .= "Content-Disposition: attachment; filename=".$filename."\n"."\n";
                        $msg .= $f_contents."\n"."\n";
                        if (!isset($_POST['confidential'])) {
                                $db->igroupsQuery("INSERT INTO EmailFiles (iEmailID, sDiskName, sOrigName, sMimeType) VALUES (0, '{$files[$i]['sDiskName']}', '$filename', '{$files[$i]['sMimeType']}')");
                                //$id = $db->igroupsInsertID();
                                //$db->igroupsQuery("UPDATE EmailFiles SET sDiskName='$id.att' WHERE iID=$id");
                                //move_uploaded_file($_FILES["attachment$i"]['tmp_name'], "/files/igroups/emails/$id.att");
                        }
                }
		}
			$tmpstr = wordwrap($_POST['body'], 100);
                	$body = new SuperString($tmpstr);
                	$msg .= "--".$mime_boundary."\n";
                	$msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
                	$msg .= "Content-Transfer-Encoding: 7-bit"."\n"."\n";
               	        $msg .= $body->getHTMLString()."\n"."\n";
			if ( $_POST['confidential'] == 'on' ) {
				$subj = new SuperString( $_POST['subject'] );
				//$body = new SuperString( $_POST['body'] );
				mail( $tolist, "[".$currentGroup->getName()."] ".$subj->getHTMLString(), $msg, $headers );
			}
			else {
				$newEmail = createEmail( $toNames, $_POST['subject'], $_POST['body'], $currentUser->getID(), $_POST['category'], $_POST['forward'], $currentGroup->getID(), $currentGroup->getType(), $currentGroup->getSemester(), $db );
				$query = $db->igroupsQuery("UPDATE EmailFiles SET iEmailID={$newEmail->getID()} WHERE iEmailID=0");
				$replyLink = "<p><a href='http://igroups.iit.edu/reply.php?replyTo=".$newEmail->getID()."'>Click here to reply to this email.</a></p>";
				mail( $tolist, "[".$currentGroup->getName()."] ".$newEmail->getSubjectHTML(), $msg.$replyLink, $headers );
			}
			die( "<p>Your email was successfully sent.</p>" );
		}
?>
		<form method="post" action="forward.php" enctype="multipart/form-data" id="mailform">
			<div id="to">		
				<a href="#" onClick="toggleToDisplay()">+</a> To:
				<table id="to-table">
<?php
					$members = $currentGroup->getGroupMembers();
					$members = peopleSort( $members );
					$i=false;
					foreach ( $members as $person ) {
						if ( !$i ) 
							print "<tr>";
						print "<td><input type='checkbox' name='sendto[".$person->getID()."]'></td>";
						print "<td>".$person->getFullName()." &lt;".$person->getEmail()."&gt;</td>";
						if ( $i )
							print "</tr>";
						$i = !$i;
					}
?>				
			<tr><td colspan=2><a href="javascript:checkedAll('mailform', true)">Check All</a> / <a href="javascript:checkedAll('mailform', false)">Uncheck All</a></td></tr>
				</table>
			</div><br />
			<table>
				<tr><td>CC:</td><td><input type="text" size=45 name="cc" /></td></tr>
<?php
				print "<tr><td>Subject:</td><td><input type='text' size=45 name='subject' value='FWD: ".$replyEmail->getSubjectHTML()."'/></td></tr>";
?>
				<tr><td><input type="checkbox" name="confidential"></td><td>Keep confidential? (if checked, will not be stored in iGROUPS)</td></tr>
				
<?php
				print "<input type='hidden' name='category' value=".$replyEmail->getCategoryID().">";
?>
				<tr><td>Attachment:</td><td><input type="file" name="attachment"></td></tr>
				<tr><td colspan=2>Body:</td></tr>
				<tr><td colspan=2>
<?php
				print "<textarea name='body' cols='60' rows='10'>\n\n\n----Begin Forwarded Message----\n\n".$replyEmail->getBody()."</textarea>";
?>
				</td></tr>
				<tr><td colspan=2 align="center"><input type="submit" name="send" value="Send Email" /></td></tr>
			</table>
<?php
			print "<input type='hidden' name='forward' value=".$_GET['forward'].">";
?>
		</form>
	</body>
	</html>
<?php
	}
	else {
	
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	   "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<title>iGROUPS - Send Email</title>
		<link href="default.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<h1>Login</h1>
<?php
		if ( isset( $errorMsg ) ) {
			print $errorMsg."<br>";
		}
?>
		<form method="post" action="forward.php">
			User name: <input name="username" type="text" /><br />
			Password: <input name="password" type="password" /><br />
			<input type='checkbox' name='remember'> Remember me?<br>
<?php
			print "<input type='hidden' name='forward' value=".$_GET['forward'].">";
?>
			<input type="submit" name="login" value="Login" />
		</form>
	</body>
	</html>
<?php
	}
?>
