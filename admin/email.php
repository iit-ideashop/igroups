<?php
	session_start();
	ini_set("memory_limit", "16M");

	include_once( "../classes/db.php" );
	include_once( "../classes/person.php" );
	include_once( "../classes/group.php" );
	include_once( "../classes/category.php" );
	include_once( "../classes/groupemail.php" );
	include_once( "../classes/semester.php" );	

	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
	else
		die("You are not logged in.");
		
	if ( !$currentUser->isAdministrator() )
               die("You must be an administrator to access this page.");

        if ( isset( $_POST['selectSemester'] ) ) {
                $_SESSION['selectedIPROSemester'] = $_POST['semester'];
        }
	else {
		$query = $db->iknowQuery("SELECT iID FROM Semesters WHERE bActiveFlag=1");
		$row = mysql_fetch_row($query);
		$_SESSION['selectedIPROSemester'] = $row[0];
	}

        $currentSemester = new Semester( $_SESSION['selectedIPROSemester'], $db );
	
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}

	function groupSort( $array ) {
                $newArray = array();
                foreach ( $array as $group ) {
                        $newArray[$group->getName()] = $group;
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
	<title>iGROUPS - Group Email</title>
	<style type="text/css">
		@import url("../default.css");
		
		#container {
			padding:0;
		}
		
		#catbox {
			float:left;
			width:30%;
			margin:5px;
			padding:8px;
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
			width:64%;
			border:1px solid #000;
		}
		
		#emails {
			width:100%;
			text-align:left;
			background-color:#fff;
		}
		
		#menubar {
			background-color:#eeeeee;
			margin-bottom:5px;
			padding:3px;
		}
		
		#menubar li {
			padding:5px;
			display:inline;
		}
		
		ul {
			list-style:none;
			padding:0;
			margin:0;
		}

		.window {
			width:500px;
			background-color:#FFF;
			border: 1px solid #000;
			visibility:hidden; 
			position:absolute;
			left:20px;
			top:20px;
		}
		
		.window-topbar {
			padding-left:5px;
			font-size:14pt;
			color:#FFF;
			background-color:#C00;
		}
		
		.window-content {
			padding:5px;
		}
	</style>
	<script language="javascript" type="text/javascript">
		function showEmail(id, pagey, screeny ) {
			document.getElementById('emailFrame').src='displayemail.php?id='+id;
			document.getElementById('email-window').style.visibility = 'visible';
			document.getElementById('email-window').style.top = (document.documentElement.scrollTop+20)+"px";
			return false;
		}
		
		function showMessage( msg ) {
			msgDiv = document.createElement("div");
			msgDiv.id="messageBox";
			msgDiv.innerHTML=msg;
			document.body.insertBefore( msgDiv, null );
			window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
		}
		
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
	<script language="javascript" type="text/javascript" src="../speller/spellChecker.js">
        </script>

        <!-- Call a function like this to handle the spell check command -->
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

                function checkedAll (id, checked) {
                        var el = document.getElementById(id);
                        for (var i = 0; i < el.elements.length; i++) {
                                el.elements[i].checked = checked;
                        }
                }
        </script>
</head>
<body>
	<div id="topbanner">
<?php
		print "E-mail Groups";
?>
	</div>
<?php
	
	if ( isset( $_POST['delete'] ) && $_POST['delete']==1 && isset($_POST['email'])) {
		foreach( $_POST['email'] as $emailid => $val ) {
			$email = new GroupEmail( $emailid, $db );
			$email->delete();
		}
?>
		<script type="text/javascript">
			showMessage("Selected items successfully deleted");
		</script>
<?php
	}

	if ( isset( $_POST['send'] ) ) {
                $to = array();
		$toString = "";

                foreach ( $_POST['sendto'] as $id => $val ) {
                        $group = new Group( $id, 0, $currentSemester->getID(), $db );
			$toString .= $group->getName() . ', ';
                        $people = $group->getGroupMembers();
                        foreach ( $people as $person ) {
                                if ( !in_array( $person->getEmail(), $to ))
                                        $to[] = $person->getEmail();
                        }
                }
		$toString = substr($toString, 0, strlen($toString)-2);
                $tolist = join( ",", $to );

                $headers = "From:".$currentUser->getEmail()."\n"."Bcc: $tolist\n";
                if ( isset($_POST['cc'] ) )
                        $headers .= "Cc:".$_POST['cc']."\n";
                $mime_boundary = md5(time());
                $headers .= 'MIME-Version: 1.0'."\n";
                $headers .= 'Content-Transfer-Encoding: 7-bit'."\n";
                $headers .= "Content-Type: multipart/mixed; boundary=".$mime_boundary."\n";
                $filename = str_replace(' ', '_', $_FILES['attachment']['name']);
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
                }
                }
                $tmpstr = wordwrap($_POST['body'], 100);
                $subj = new SuperString( $_POST['subject'] );
                $body = new SuperString( $tmpstr );
                $msg .= "--".$mime_boundary."\n";
                $msg .= "Content-Type: text/html; charset=iso-8859-1"."\n";
                $msg .= "Content-Transfer-Encoding: 7bit"."\n"."\n";
                $msg .= $body->getHTMLString()."\n"."\n";
                $msg .= "--".$mime_boundary."--"."\n"."\n";
		$err = mail( "", "[IPRO Office Notice] ".stripslashes($_POST['subject']), $msg, $headers );
		$err = 1;
                if ($err)
                print "<p>Your email was successfully sent.</p>";
                else
                print "<p>There was a problem with your e-mail. Please contact iproadmin.</p>";
		createEmail($toString, $subj->getString(), $body->getString(), $currentUser->getID(), $currentSemester->getID(), $db);
        }

	$emails = array();
        $query = $db->igroupsQuery("SELECT iID FROM GroupEmails WHERE iSemesterID={$currentSemester->getID()} ORDER BY dDate DESC");
        while ($row = mysql_fetch_row($query)) {
                $emails[] = new GroupEmail($row[0], $db);
        }

?>
	<form method="post" action="email.php">
	<div id="container">
		<div id="catbox">
			<div id="columnbanner">
				Select Semester:
			</div>
			<div id="semesters" align='center'>
			<br>
                        <select name="semester">
<?php
                        $semesters = $db->iknowQuery( "SELECT iID FROM Semesters ORDER BY iID DESC" );
                        while ( $row = mysql_fetch_row( $semesters ) ) {
                                $semester = new Semester( $row[0], $db );
                                if (isset($currentSemester) && $semester->getID() == $currentSemester->getID())
                                        print "<option value=".$semester->getID()." selected>".$semester->getName()."</option>";
                                else
                                        print "<option value=".$semester->getID().">".$semester->getName()."</option>";
                        }
?>
                        </select>
                        <input type="submit" name="selectSemester" value="Select Semester">
			</div>
		</div>
		<div id="emailbox">
<?php			
			print "<div id='columnbanner'>Mass Emails:</div>";
?>
			<div id="menubar">
				<ul>
					<li><a href="#" onClick="document.getElementById('send-window').style.visibility='visible';">Send Email</a></li>
					<li><a href="#" onClick="window.location.href='searchemail.php';">Search Email</a></li>
						<li><a href="#" onClick="document.getElementById('delete').value='1'; document.getElementById('delete').form.submit()">Delete Selected</a>
						<input type='hidden' id='delete' name='delete' value='0'></li>
				</ul>
			</div>
			<div id="emails">
				<table width='100%'>
<?php				
				foreach ( $emails as $email ) {
					$author = $email->getSender();
					printTR();
					print "<td colspan=2><a href='displayemail.php?id=".$email->getID()."' onClick='showEmail(".$email->getID()."); return false;'>".$email->getShortSubject()."</a></td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td><td><input type='checkbox' name='email[".$email->getID()."]'></td></tr>";
				}
?>
				</table>
			</div>
		</div>
	</div>
	</form>
	<div id="email-window" class="window">
		<div class="window-topbar">
			View Email
			<input class="close-button" type="button" onClick="document.getElementById('email-window').style.visibility='hidden';">
		</div>
		<iframe id="emailFrame" width=100% height="500" frameborder="0">
		</iframe>
	</div>
	<div id="send-window" class="window">
		<div class="window-topbar">
			Send Email
			<input class="close-button" type="button" onClick="document.getElementById('send-window').style.visibility='hidden';">
		</div>
		<form method="post" action="email.php" enctype="multipart/form-data" id="email">
                <div id="to">
                        <a href="#" onClick="toggleToDisplay()">+</a> To:
                        <table id="to-table" width='100%'>
<?php
                                $groups = $currentSemester->getGroups();
                                $groups = groupSort( $groups );
                                $i=0;
                                foreach ( $groups as $group ) {
                                        if ( $i == 0 )
                                                print "<tr>";
                                        print "<td><input type='checkbox' name='sendto[".$group->getID()."]' checked>";
                                        print "&nbsp;".$group->getName()."</td>";
                                        if ( $i == 2 ) {
                                                print "</tr>";
						$i = 0;
					}
					else
						$i++;
                                }
?>
                        <tr><td colspan=4><a href="javascript:checkedAll('email', true)">Check All</a> / <a href="javascript:checkedAll('email', false)">Uncheck All</a>
                        </td></tr></table>
                </div><br />
                <table>
                        <tr><td>CC:</td><td><input type="text" size=50 name="cc" /></td></tr>
                        <tr><td>Subject:</td><td><input type="text" size=50 name="subject" /></td></tr>
                        <tr><td>Attachments:</td></tr>
                        <tr><td colspan='2'>
                        <div id='files'><?php
                        //$a = 1;
                        ?><div class="stdBoldText" id='file1div'>&nbsp;&nbsp;&nbsp;File 1: <input type="file" name="attachment1" onChange='fileAdd(1);'></div><?php
                ?></div></div>
                <span onclick='fileAdd(document.getElementById("files").childNodes.length);' style='color:#00F;text-decoration:underline;cursor:pointer;'>Click here to add another file.</span>
                        </td></tr>
                        <tr><td colspan=2>Body:</td></tr>
                        <tr><td colspan=2><textarea name="body" cols="54" rows="10"></textarea></td></tr>
                        <tr><td colspan=2 align="center"><input type='button' value='Spell Check' onClick="openSpellChecker();">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="send" value="Send Email" /></td></tr>
                </table>
        </form>
	</div>
</body>
</html>
