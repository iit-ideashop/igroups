<?php
require_once('config.php');
$accept = $_SERVER['HTTP_ACCEPT'];
$ua = $_SERVER['HTTP_USER_AGENT'];

function setXHTML()
{
	global $st, $checked, $disabled, $selected, $contenttype;
	header('Content-Type: application/xhtml+xml');
	$contenttype = 'application/xhtml+xml';
	$st = ' /';
	$checked = 'checked="checked"';
	$disabled = 'disabled="disabled"';
	$selected = 'selected="selected"';
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n";
	echo "<head>\n<meta http-equiv=\"Content-Type\" content=\"$contenttype; charset=utf-8\"$st>\n";
}

function setHTML()
{
	global $st, $checked, $disabled, $selected, $contenttype;
	$contenttype = 'text/html';
	$st = '';
	$checked = 'checked';
	$disabled = 'disabled';
	$selected = 'selected';
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
	echo "<html lang=\"en\">\n";
	echo "<head>\n<meta http-equiv=\"Content-Type\" content=\"$contenttype; charset=utf-8\"$st>\n";
}
if(isset($_GET['html']))
	setHTML();
else if(isset($_GET['xhtml']))
	setXHTML();
else if(isset($accept) && isset($ua))
	(stristr($accept, 'application/xhtml+xml') !== false || stristr($ua, 'W3C_Validator') !== false) ? setXHTML() : setHTML();
else if(stristr($ua, 'W3C_Validator') !== false)
	setXHTML();
else
	setHTML();
if(isset($_POST['resetPW']) || isset($_POST['changePW']))
{
	echo "<title>IPRO Program - Setting Password</title>\n</head><body>\n<div><img src=\"header.png\" alt=\"Interprofessional Projects Program\"$st></div>\n";
	echo "<h1>Setting Passwords...</h1>\n";
	$i = 0;
	if(isset($_POST['changePW']))
	{
		if(!isset($_POST['password']))
			die('<p>You must enter your current password to change passwords. Press back and try again.</p></body></html>');
		$db = mysql_connect('localhost', $db_user, $db_pass) or die('<p>Could not connect to database</p></body></html>');
		if(strpos($_POST['email'], "@") === FALSE) 
			$_POST['email'] .= "@iit.edu";
		$em = mysql_real_escape_string($_POST['email']);
		$pw = md5($_POST['password']);
		mysql_select_db('igroups', $db);
		if(!mysql_num_rows(mysql_query("select iID from People where sEmail='$em' and sPassword='$pw'")))
		{
			mysql_select_db('prv2', $db);
			if(!mysql_num_rows(mysql_query("select id from People where email='$em' and password='$pw'")))
			{
			//	mysql_select_db('proposals', $db);
			//	if(!mysql_num_rows(mysql_query("select id from users where email='$em' and password='$pw'")))
					die('<p>Either the password for this account was incorrect, or there is no account with that email address in our system. Please press Back and re-enter your original password. If you do not know your current password, please <a href="index.php?reset=1">reset your password</a>.</p></body></html>');
			}
		}
		mysql_select_db('igroups', $db);
		if(isset($_POST['newpw1']) && $_POST['newpw1'] == $_POST['newpw2'])
			$pw = $_POST['newpw1'];
		else if(!isset($_POST['newpw1']))
			die('<p>You must enter a password. Press Back and try again.</p></body></html>');
		else
			die('<p>Your new password and confirmation password do not match. Press Back and try again.</p></body></html>');
		$md5pw = md5($pw);
		echo "<ul>\n";
		$q = mysql_query("select iID from People where sEmail='".mysql_real_escape_string($_POST['email'])."'");
		if(mysql_num_rows($q))
		{
			while($row = mysql_fetch_row($q))
				mysql_query("update People set sPassword='$md5pw' where iID=".$row[0]);
			echo "<li>iGroups...Updated</li>\n";
			$i++;
		}
		mysql_select_db('prv2', $db);
		$q = mysql_query("select id from People where email='".mysql_real_escape_string($_POST['email'])."'");
		if(mysql_num_rows($q))
		{
			while($row = mysql_fetch_row($q))
				mysql_query("update People set password='$md5pw' where id=".$row[0]);
			echo "<li>Peer Review...Updated</li>\n";
			$i++;
		}
/*
		mysql_select_db('proposals', $db);
		$q = mysql_query("select id from users where email='".mysql_real_escape_string($_POST['email'])."'");
		if(mysql_num_rows($q))
		{
			while($row = mysql_fetch_row($q))
				mysql_query("update users set password='$md5pw' where id=".$row[0]);
			echo "<li>Proposals...Updated</li>\n";
			$i++;
		}
*/
		if($i)
			echo "</ul><p>Your password was successfully changed.</p>\n";
		else
			echo "<li>There is no iGroups or Peer Review account with that email address.</li></ul>\n";
	}
	else if(isset($_POST['resetPW']))
	{
		if(strpos($_POST['email'], "@") === FALSE) 
			$_POST['email'] .= "@iit.edu";
		$pw = "";
		for($a=0; $a<8; $a++)
			$pw .= chr(rand(65, 90));
		$md5pw = md5($pw);
		echo "<ul>\n";
		$db = mysql_connect('localhost', $db_user, $db_pass) or die('<p>Could not connect to database</p></body></html>');
		mysql_select_db('igroups', $db);
		$q = mysql_query("select iID from People where sEmail='".mysql_real_escape_string($_POST['email'])."'");
		if(mysql_num_rows($q))
		{
			while($row = mysql_fetch_row($q))
				mysql_query("update People set sPassword='$md5pw' where iID=".$row[0]);
			echo "<li>iGroups...Updated</li>\n";
			$i++;
		}
		mysql_select_db('prv2', $db);
		$q = mysql_query("select id from People where email='".mysql_real_escape_string($_POST['email'])."'");
		if(mysql_num_rows($q))
		{
			while($row = mysql_fetch_row($q))
				mysql_query("update People set password='$md5pw' where id=".$row[0]);
			echo "<li>Peer Review...Updated</li>\n";
			$i++;
		}
	/*	mysql_select_db('proposals', $db);
		$q = mysql_query("select id from users where email='".mysql_real_escape_string($_POST['email'])."'");
		if(mysql_num_rows($q))
		{
			while($row = mysql_fetch_row($q))
				mysql_query("update users set password='$md5pw' where id=".$row[0]);
			echo "<li>Proposals...Updated</li>\n";
			$i++;
		}
        */
		if($i)
		{
			echo "</ul><p>Your password was successfully reset. You will receive your new password in an email within the next ten minutes. We strongly recommend that you <a href=\"index.php\">change your password</a> after receiving the reset password.</p>\n";
			mail( $_POST['email'], "Your IPRO password has been reset", "Your new password for all IPRO-related systems is:\n$pw\nPasswords are case-sensitive. We strongly recommend that you change your password at this time. You can change your password at $my_url\n\nThe password reset request was generated by\nIP address: ".$_SERVER['REMOTE_ADDR']."\nUser agent: ".$_SERVER['HTTP_USER_AGENT']."\nDate: ".date("D M j G:i:s T Y", $_SERVER['REQUEST_TIME']), "From: ".$email_from );
		}
		else
			echo "</ul><p>There is no iGroups, Peer Review, or Proposals account with that email address.</p>\n";
	}
	else
		die('<p>An unknown error occurred. Please go back and try again.</p></body></html>');
	echo '<p>Return to: <a href="'.$igroups_url.'">iGroups</a>&#160;&#183;&#160;<a href="'.$peerrev_url.'">Peer Review System</a></p></body></html>';
}
else if($_GET['reset'])
{
	echo "<title>IPRO Program - Reset Password</title>\n</head><body>\n<div><img src=\"header.png\" alt=\"Interprofessional Projects Department\"$st></div>\n";
	echo "<h1>Password Reset Form</h1>\n";
	echo "<p>This form will reset your password for any IPRO accounts you may have, including the following systems: iGroups and Peer Review. You will receive your new password via email. If you know your password and would like to change it, please go to the <a href=\"index.php\">change password form</a>.</p>\n";
	echo "<form action=\"index.php\" method=\"post\"><fieldset><legend>Reset Password</legend>\n<label>Email: <input type=\"text\" name=\"email\"$st></label><br$st>\n";
	echo "<input type=\"submit\" name=\"resetPW\" value=\"Reset Password\"$st>\n</fieldset></form></body></html>";
}
else
{
	echo "<title>IPRO Program - Change Password</title>\n</head><body>\n<div><img src=\"header.png\" alt=\"Interprofessional Projects Department\"$st></div>\n";
	echo "<h1>Password Change Form</h1>\n";
	echo "<p>This form will change your password for any IPRO accounts you may have, including the following systems: iGroups and Peer Review. This form requires your current password from iGroups or Peer Review. If you do not know your current password, please <a href=\"index.php?reset=1\">reset your password</a> first.</p>\n";
	echo "<form action=\"index.php\" method=\"post\"><fieldset><legend>Change Password</legend>\n";
	echo "<label>Email: <input type=\"text\" name=\"email\"$st></label><br$st>\n";
	echo "<label>Current password: <input type=\"password\" name=\"password\"$st></label><br$st>\n";
	echo "<label>New password: <input type=\"password\" name=\"newpw1\"$st></label><br$st>\n";
	echo "<label>Repeat new password: <input type=\"password\" name=\"newpw2\"$st></label><br$st>\n";
	echo "<input type=\"submit\" name=\"changePW\" value=\"Change Password\"$st>\n";
	echo "</fieldset></form></body></html>\n";
}
?>
