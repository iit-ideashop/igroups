<?php
	session_start();
	ini_set("memory_limit", "16M");

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
	include_once( "classes/subgroup.php" );
	include_once( "classes/category.php" );
	include_once( "classes/email.php" );
	
	$db = new dbConnection();
	
	if ( isset( $_SESSION['userID'] ) ) 
		$currentUser = new Person( $_SESSION['userID'], $db );
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- This web-based application is Copyrighted &copy; 2008 Interprofessional Projects Program, Illinois Institute of Technology -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
<title>iGroups - Send Email</title>
<link rel="stylesheet" href="default.css" type="text/css" />
	
</head>
<body onload="sendinit()">
<?php
	if (isset($_GET['replyid'])) {
		$replyEmail = new Email($_GET['replyid'], $db);
	}
	else if (isset($_GET['forward'])) {
		$forwardEmail = new Email($_GET['forward'], $db);
	}
?>
	<form method="post" action="email.php" enctype="multipart/form-data" id="mailform">
		<div id="to">		
			<a href="#" onclick="toggleToDisplay()">+</a><fieldset><legend>To:</legend>
			<table id="to-table" width="100%">
<?php
				$members = $currentGroup->getGroupMembers();
				$members = peopleSort( $members );
				$i=1;
				foreach ( $members as $person ) {
					if ( $i == 1 ) 
						print "<tr>";
					if(isset($_GET['replyid']) && $person->getID() == $replyEmail->getSenderID())
						print "<td><input type=\"checkbox\" name=\"sendto[".$person->getID()."]\" id=\"sendto[".$person->getID()."]\" checked=\"checked\" /></td>";
					else
						print "<td><input type=\"checkbox\" name=\"sendto[".$person->getID()."]\" id=\"sendto[".$person->getID()."]\" /></td>";
					
					print "<td><label for=\"sendto[".$person->getID()."]\">".$person->getFullName()."</label></td>";
					if ( $i == 3) {
						print "</tr>";
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
		if ($subgroups) {
?>
		<div id="subgroups">
			<a href="#" onclick="toggleSGDisplay()">+</a><fieldset><legend>Subgroups:</legend>
			<table id="subgroups-table" width="100%">
<?php
			$i=1;
			foreach ($subgroups as $subgroup) {
				if ($i == 1)
					print "<tr>";
				print "<td><input type=\"checkbox\" id=\"subgroup".$subgroup->getID()."\" name=\"sendtosubgroup[".$subgroup->getID()."]\" />&nbsp;";
				print "<label for=\"subgroup".$subgroup->getID()."\">".$subgroup->getName()."</label></td>";
				if ($i == 3) {
					print "</tr>";
					$i=1;
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
	if (count($members) > 0) {
?>
                        <br /><a href="#" onclick="toggleGuestDisplay()">+</a><fieldset><legend>Guests:</legend>
                        <table id="guest-table" width="100%">
<?php
                                $members = peopleSort( $members );
                                $i=1;
                                foreach ( $members as $person ) {
                                        if ( $i == 1 )
                                                print "<tr>";
                                        print "<td><input type=\"checkbox\" id=\"guest".$person->getID()."\" name=\"sendtoguest[".$person->getID()."]\" /></td>";
                                        print "<td><label for=\"guest".$person->getID()."\">".$person->getFullName()."</label></td>";
                                        if ( $i == 3) {
                                                print "</tr>";
                                                $i = 1;
                                        }
                                        else
                                                $i++;
                                }

?>
			<tr><td colspan=2><a href="javascript:checkedAllGuest('mailform', true)">Check All</a> / <a href="javascript:checkedAllGuest('mailform', false)">Uncheck All</a></td></tr>
                        </table></fieldset>
                </div>
<?php } ?>		
		<br />
		<table>
			<tr><td><label for="cc">CC:</label></td><td><input type="text" size="50" name="cc" id="cc" /></td></tr>
<?php
		if (isset($replyEmail)) 
			print "<tr><td>Subject:</td><td><input type=\"text\" size=\"50\" name=\"subject\" value=\"RE: {$replyEmail->getSubjectHTML()}\" /></td></tr>";
		else if (isset($forwardEmail)) 
			print "<tr><td>Subject:</td><td><input type=\"text\" size=\"50\" name=\"subject\" value=\"FW: {$forwardEmail->getSubjectHTML()}\" /></td></tr>";
		else
			print "<tr><td>Subject:</td><td><input type=\"text\" size=\"50\" name=\"subject\" /></td></tr>";
?>
			<tr><td><input type="checkbox" name="confidential" id="confidential" /></td><td><label for="confidential">Keep confidential? (if checked, will not be stored in iGROUPS)</label></td></tr>
			<tr><td><label for="category">Category</label></td><td><select name="category" id="category"><option value="0">No Category</option>
<?php
			$categories = $currentGroup->getGroupCategories();
			foreach ( $categories as $category ) {
				print "<option value=\"".$category->getID()."\">".$category->getName()."</option>";
			}
?>
			</select></td></tr>
			<tr><td>Attachments:</td></tr>
			<tr><td colspan="2">
			<div id="files"><div class="stdBoldText" id="file1div">&nbsp;&nbsp;&nbsp;<label for="attachment1">File 1:</label> <input type="file" name="attachment1" id="attachment1" onchange="fileAdd(1);" /></div></div></div>
                <span onclick="fileAdd(document.getElementById('files').childNodes.length);" style="color:#00F;text-decoration:underline;cursor:pointer;">Click here to add another file.</span>
			</td></tr>
			<tr><td colspan="2"><label for="body">Body:</label></td></tr>
<?php
		if (isset($replyEmail)) 
			print "<tr><td colspan=\"2\"><textarea name=\"body\" id=\"body\" cols=\"54\" rows=\"10\">\n\n\n----Original E-mail Follows----\n{$replyEmail->getReplyBody()}</textarea></td></tr>";
		else if (isset($forwardEmail)) 
			print "<tr><td colspan=\"2\"><textarea name=\"body\" id=\"body\" cols=\"54\" rows=\"10\">\n\n\n----Original E-mail Follows----\n{$forwardEmail->getReplyBody()}</textarea></td></tr>";
		else
			print "<tr><td colspan=\"2\"><textarea name=\"body\" id=\"body\" cols=\"54\" rows=\"10\"></textarea></td></tr>";
?>
			<tr><td colspan="2" align="center"><input type="button" value="Spell Check" onclick="openSpellChecker();" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="send" value="Send Email" /></td></tr>
		</table>
<?php
		if ( isset( $_GET['replyid'] ) )
			print "<input type=\"hidden\" name=\"replyid\" value=\"".intval($_GET['replyid'])."\" />";
		else
			print "<input type=\"hidden\" name=\"replyid\" value=\"0\" />";
		if ( isset( $_GET['forward'] ) )
			print "<input type=\"hidden\" name=\"forwardid\" value=\"".intval($_GET['forward'])."\" />";
		else
			print "<input type=\"hidden\" name=\"forwardid\" value=\"0\" />";
?>
	</fieldset></form>
</body>
</html>
