<?php
echo "</head><body".(isset($onload) ? " onload=\"$onload\"" : '').">\n";
if(isset($onload))
	unset($onload);
echo "<div id=\"sidebar\">\n";
echo "<img src=\"{$GLOBALS['rootdir']}img/logo.gif\" alt=\"{$GLOBALS['systemname']}\" title=\"{$GLOBALS['systemname']}\"$st>\n";

if($_SESSION['loginError'])
{
	echo "<p style=\"font-weight: bold\">Invalid username or password.</p>\n";
	unset($_SESSION['loginError']);
}
if(!isset($_SESSION['uID']))
{
?>

<form method="post" action="<?php echo $GLOBALS['rootdir']; ?>status.php"><fieldset><legend>Login</legend>
<label>Email Address:<br<?php echo $st; ?>><input type="text" name="username" size="20" maxlength="50"<?php echo $st; ?>></label><br<?php echo $st; ?>>
<label>Password:<br<?php echo $st; ?>><input type="password" name="password" size="20" maxlength="50"<?php echo $st; ?>></label><br<?php echo $st; ?>>
<label><input type="checkbox" name="remember" value="true"<?php echo $st; ?>> Remember Me</label><br<?php echo $st; ?>>
<input type="submit" name="login" value="Login"<?php echo $st; ?>>
</fieldset></form>
<?php
}
else
{
	$currentUser = new Person($_SESSION['uID'], $db);
	echo "<strong>Welcome, ".htmlspecialchars($currentUser->getFirstName())."</strong><br$st>\n";

	if($currentUser->isAdministrator() || $_SESSION['uType'] == 2)
	{
		echo "<p class=\"listhead\">Administrative Tools</p>\n";
		echo "<ul>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}status.php\">System Status</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/adminsurvey.php\">Admin Surveys</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/groups.php\">Manage Groups</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/criteria.php\">Manage Default Criteria</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/ccriteria.php\">Manage Custom Criteria</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/messages.php\">Manage Messages</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/reports.php\">Reports</a></li>\n";
		echo "</ul>\n";
	}
	else if($currentUser->isFaculty() || $_SESSION['uType'] == 1)
	{
		echo "<p class=\"listhead\">Faculty Tools</p>\n";
		echo "<ul>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/adminsurvey.php\">View Status</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/groups.php\">Manage Groups</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/ccriteria.php\">Manage Survey Items</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}admin/reports.php\">Reports</a></li>\n";
		echo "</ul>\n";
	}
	else
	{
		echo "<p class=\"listhead\">Survey Tools</p>\n";
		echo "<ul>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}status.php\">Survey Status</a></li>\n";
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}viewall.php?uID={$currentUser->getID()}\">All Reviews</a></li>\n";
		echo "</ul>\n";
	}
	
	echo "<p class=\"listhead\">User Tools</p>\n<ul>\n";
	if($currentUser->getFirstName() == "Sample" || $currentUser->getFirstName() == "Student")
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}index.php?logout=true\">Logout from Demo</a></li>\n";
	else
	{
		echo "\t<li><a href=\"{$GLOBALS['rootdir']}index.php?logout=true\">Logout</a></li>\n";
		echo "\t<li><a href=\"https://igroups.iit.edu/userpassword/index.php\">Change Password</a></li>\n";
	}
	echo "</ul>\n";
	
}
echo "<hr$st><ul>\n";
if(!isset($_SESSION['uID']))
	echo "\t<li><a href=\"https://igroups.iit.edu/userpassword/index.php?reset=1\">Reset Password</a></li>\n";
echo "\t<li><a href=\"{$GLOBALS['rootdir']}help.php\">Help</a></li>\n";
echo "\t<li><a href=\"mailto:{$GLOBALS['adminemail']}\">Email Administrator</a></li>\n";
if(!isset($_SESSION['uID']) || (!$currentUser->isFaculty() && $_SESSION['uType'] != 1))
	echo "\t<li><a href=\"{$GLOBALS['rootdir']}docs/student.pdf\">Student Manual (PDF)</a></li>\n";
if(!isset($_SESSION['uID']) || (!$currentUser->isStudent() && $_SESSION['uType'] != 0))
	echo "\t<li><a href=\"{$GLOBALS['rootdir']}docs/faculty.pdf\">Faculty Manual (PDF)</a></li>\n";
?>
</ul>
<hr<?php echo $st; ?>>
<p>Return to <a href="http://igroups.iit.edu">iGroups</a> &#183; <a href="http://ipro.iit.edu">IPRO Website</a></p>
<hr<?php echo $st; ?>>
<p id="copyright">&#169; 2009 IPRO Program, Illinois Institute of Technology</p>
</div>
<?php
	if(isset($message))
		echo "<div id=\"messageBox\">$message</div>\n";
	else if(isset($messages))
	{
		echo "<div id=\"messageBox\"><ul>\n";
		foreach($messages as $message)
			echo "<li>$message</li>\n";
		echo "</ul></div>\n";
	}	
?>
<div id="header">
<span class="pr_title"><?php echo $GLOBALS['systemname'] ?> |</span> <span class="tagline">Quick Reviewing Process</span>
</div>
