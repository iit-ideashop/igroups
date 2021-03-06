<?php
	session_start();

	require_once('globals.php');
	include_once('classes/announcement.php');
	include_once('classes/person.php');
  /************************************* Important ! *************************/
  /* this if statement here is necessary for the side bar menu to display correctly */
  if(!isset($_SESSION['activateDefaultMenu']) && (basename( $_SERVER['PHP_SELF'] ))=="index.php"){
			$_SESSION['activateDefaultMenu'] = 1;
  }
  /************************************ end important menu code *************/

	if(isset($_SESSION['userID']))
		$currentUser = new Person($_SESSION['userID'], $db);
	else
		$currentUser = false;
	
	if($currentUser && !$currentUser->isValid())
	{
		$currentUser = false;
		unset($_SESSION['userID']);
	}
	
	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/index.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/index.css\" type=\"text/css\" title=\"$altskin\" />\n";

	echo "<title>$appname $appversion</title>\n";
	$announcementResults = $db->query('SELECT iID FROM News WHERE dExpire >= CURDATE() ORDER BY iID DESC');
	$numAnnounce = mysql_num_rows($announcementResults);
	if($numAnnounce > 1)
	{
?>

<?php 
/* include all necessary javascript files */
/* TODO: place all links to scripts in includes file */
require('scripts.php'); 
?>

<script type="text/javascript">
//<![CDATA[
	announcements = new announcementObj();
<?php
		$firstAnnouncement = null;
		while($row = mysql_fetch_row($announcementResults))
		{
			$announcement = new Announcement($row[0], $db);
			//echo "console.log('row : ".$row[0]."');\n";
			if($announcement && !$announcement->isExpired())
			{
				echo "\tannouncements.add(".$announcement->getID().", \"".$announcement->getHeadingHTML()."\", \"".$announcement->getBodyHTML()."\");\n";
				if(!isset($firstAnnouncement))
					$firstAnnouncement = $announcement;
			}
		}
?>
	announcements.doCycle();
//]]>
</script>
<?php
	}
	else if($numAnnounce == 1)
	{
		$row = mysql_fetch_row($announcementResults);
		$firstAnnouncement = new Announcement($row[0], $db);
		if ($firstAnnouncement->isExpired) {
			$firstAnnouncement = null;
		}
	}
	echo "</head>\n";/* end head */
  
  
  
  echo "<body>\n";/*start body */

  /**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/

  
	echo "<div id=\"top\">\n";/* start inner content */
	echo "<h1>Welcome to $appname</h1>\n";
	echo "<p>$appname is designed to support all communication, scheduling, and collaboration activities of IPRO teams. Through the use of $appname you can send/receive e-mail messages, store/retrieve files, access/update a team calendar, and view a complete history of a team's activities since the creation of the team in $appname. Welcome to $appname, a team management tool developed by IIT students.</p>\n";
	
	if(!$currentUser)
		echo "<p>To use $appname simply enter your username and password in the login pane to the left. Your initial 	username is the first part of your IIT email address, or your entire email address if you do not have or use an IIT email. Your initial password is the first part of your email address (text appearing before the @). If you are a first-time user, please change your password upon entry to $appname (from the My Profile page).</p>";
	echo "</div>\n";
	
	$rand = mysql_fetch_array($db->query("select * from RandomQuotes order by rand() limit 1"));
	$randid = $rand['iID'];
	$ext = $rand['sExtension'];
	$title = htmlspecialchars($rand['sTitle']);
	$desc = htmlspecialchars($rand['sDesc']);
	echo "<div id=\"right\"><div id=\"randphotobox\"><img src=\"images/students1/photos/$randid.$ext\" alt=\"Random photo\" id=\"randphoto\" /></div><br />\n";
	echo "<span id=\"randtitle\">$title</span><br />\n<span id=\"randdesc\">$desc</span>";
	echo "</div>\n";
	
	$heading = $numAnnounce ? $firstAnnouncement->getHeadingHTML() : "Welcome to $appname";
	$body = $numAnnounce ? $firstAnnouncement->getBodyHTML() : 'There are no announcements';
	echo "<div class=\"left\"><div class=\"box\">\n";
	echo "<span class=\"box-header\">Announcements</span>\n";
	echo "<div class=\"announcement-heading\" id=\"announcehead\">$heading</div>\n";
	echo "<div class=\"announcement-body\" id=\"announcebody\">$body</div>\n";
	if($numAnnounce > 1)
		echo "<a href=\"javascript:announcements.navigation('prev')\">Prev</a> <a href=\"javascript:announcements.navigation('pause')\">Pause</a> <a href=\"javascript:announcements.navigation('next')\">Next</a>\n";
	echo "</div></div>\n";
	
	echo "<div class=\"left\"><div class=\"box\">\n";
	echo "<span class=\"box-header\">$appname Knowledge Management</span>\n";
	echo "<div class=\"announcement-heading\">Guest to $appname</div>\n";
	echo "<div class=\"announcement-body\">You can <a href=\"iknow/main.php\">search and browse</a> IPRO team deliverables.</div>\n";
	echo "</div></div>\n";
  /* end content */
	echo "<br class=\"clearboth\">";
  
  //include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?>	
</body>
</html>
