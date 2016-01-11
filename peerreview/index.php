<?php
if(isset($_GET['logout']))
{
	session_start();
	unset($_SESSION['uID']);
	unset($_SESSION['uType']);
	setcookie('userID', '', time()-3600);
	setcookie('password', '', time()+-3600);
	session_destroy();
}
include_once('classes/person.php');
include_once('classes/db.php');
session_start();
$db = new dbConnection();
include_once('includes/header.php');
?>
<title><?php echo $GLOBALS['systemname']; ?></title>
<?php
include_once('includes/sidebar.php');
?>

<div id="content">
<?php
/*	$rand = mysql_fetch_array($db->igroupsQuery("select * from RandomQuotes order by rand() limit 1"));
	$randid = $rand['iID'];
	$ext = $rand['sExtension'];
	$title = htmlspecialchars($rand['sTitle']);
	$desc = htmlspecialchars($rand['sDesc']);
	echo "<div id=\"rand\"><div id=\"randphotobox\"><img src=\"http://ipro.iit.edu/home/images/students1/photos/$randid.$ext\" alt=\"Random photo\" id=\"randphoto\"$st></div><br$st>\n";
	echo "<span id=\"randtitle\">$title</span><br$st>\n<span id=\"randdesc\">$desc</span>";
	echo "</div>\n";
*/
?>

<div id="features">
<div>
<p>Students can:</p>
<ul>
<li>take surveys</li>
<li>edit surveys</li>
<li>compare results</li>
</ul>
</div>

<div>
<p>Faculty can:</p>
<ul>
<li>view status</li>
<li>download reports</li>
<li>email reports</li>
</ul>
</div></div>
</div></body></html>
