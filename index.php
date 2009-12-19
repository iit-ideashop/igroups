<?php
	session_start();

	include_once('globals.php');
	include_once('classes/announcement.php');
	include_once('classes/person.php');

  /************************************* Important ! *************************/
  /* this if statement here is necessary for the side bar menu to display correctly */
  if(!isset($_SESSION['activateDefaultMenu']) && (basename ( $_SERVER['PHP_SELF'] ))=="index.php"){
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

	echo "<title>$appname 2.3</title>\n";
	$announcementResults = $db->query('SELECT iID FROM News ORDER BY iID DESC');
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
		while($row = mysql_fetch_row($announcementResults))
		{
			$announcement = new Announcement($row[0], $db);
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
<style type="text/css">
html, body, h1, h2, h3, h4, h5, h6, p, ol, ul, li, pre, code, address, variable, form, fieldset, blockquote {
    padding: 0;
    margin: 0;
    font-size: 100%;
    font-weight: normal;
}

img, fieldset {
    border: 0;
}

ol {
    margin-left: 0em;
    list-style: decimal;
}

ul {
    margin-left: 0em;
    list-style: none;
}

body{
	background:#fff;
	font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
	font-size:11px;
	color:#333;
	margin:0;
	padding:0;
	text-align:left;
	direction:ltr;
	margin-top: 20px;
}

#mainContainer {
  	width: 900px;
	margin: 0 auto;
}

#mainheader {
	width:100%;
	border: none;
	border-top: 1px dashed #CCC;
	border-bottom: 1px solid #ffd3d3;
	padding-top: 5px;
}

img #igroupsLogo{
	float: left;
}

.links {
	float:right;
	display: inline-block;
	vertical-align: top;
}

#externallinks {
	float: right;
}

#content wrapper{
	overflow: hidden;
}
#mainContent {
	float: right;
	width: 100%;
}

#externallinks li {
	float: left;
	padding: 10px 10px 0 0;
}

#externallinks li a{
	color: #616161;
	text-decoration: none;
	border-bottom: 1px dashed #CCC ;
	
}

#mainNavigation {
	margin-top: 77px;
	margin-right: 0px;
}

#mainNavigation li {
	float: left;
	padding: 5px 10px 5px 10px;
	margin-left: 4px;
	border: 1px solid #ffd3d3;
	background-color: #ffd3d3;
}


#mainNavigation li a {
	text-decoration: none;
	font-size: 14px;
	font-weight: bold;
	text-transform: uppercase;
	font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
	color: #c30000;
}

#mainNavigation li:hover {
	background-color: #FFF;
	
	border-bottom: 1px solid #FFF;
}

#uniSidebar {
	foat: left;
	padding-top: 50px;
}

#uniSidebar #subnavigation {
	width: 180px;
	margin-top: 0px;
	foat: left;
	border-top: 3px solid #ff6666;/* #ff8585;*/
	border-bottom: 3px solid #ff6666;
	border-right: 1px solid #CCC;
}

#uniSidebar #subnavigation li {
	border-top: 1px dashed #CCC;
	padding: 8px;
}
	
#uniSidebar #subnavigation li:hover {
	background-color: #e8e8e8;	
}

#uniSidebar #subnavigation li:first-child{
	border: none;
}

#uniSidebar #subnavigation li a {
	text-decoration: none;
	font-size: 1.0em;
	font-weight: 600;
	color: #585858;
}

#mainContent {
	float: right;
	border-right: 1px solid #CCC;
	width: 686px;
	height: 500px;
	margin-top: 0px;
	padding-top: 20px;
	padding-bottom: 20px;
}

#footer {
	clear: both;
	border: none;
	border-top: 1px solid #CCC;
	padding-top: 10px;
	height: 50px;
}

#footer #copyright {
	float: left;
	font-weight: 600;
	color:#585858;
}

#footer #department {
	float: right;
}

#footer a {
	color: #c30000;
	text-decoration: none;
}
</style>
<?php
	}
	else if($numAnnounce == 1)
	{
		$row = mysql_fetch_row($announcementResults);
		$firstAnnouncement = new Announcement($row[0], $db);
	}
	echo "</head>\n";
  /* end head */
  
  /*start body */
  echo "<body>\n";
	?>
		
  <!-- ****************** new layout code starts here ****************** -->
	<!-- start main container -->
  <div id="mainContainer">
			<!-- start main header -->
			<div id="mainheader">
					<!-- iGroups logo -->
					<img id="igroupsLogo" src="skins/Red/img/iGroupslogo.png" alt="iGroups Logo" />
					<!-- end logo -->
						
					<!-- start container for both external and internal links -->
					<div class="links">
			    	
							<!-- external links -->	
							<ul id="externallinks">
								<li><a href="http://sloth.iit.edu/~iproadmin/peerreview/">Peer Review</a></li>
								<li><a href="http://ipro.iit.edu">IPRO Website</a></li>
								<li><a href="login.php?logout=true" title="Logout">Logout</a></li>
							</ul>
							<!-- end external links -->
							
							<!-- internal links/main navigation --> 
							<ul id="mainNavigation">
						 		<li><a href="index.php" id="home">Home</a></li>
								<li><a href="contactinfo.php">My Profile</a></li>
								<li><a href="iknow/main.php">Browse Nuggets</a>&nbsp;</li>
								<li><a href="usernuggets.php">Groups' Nuggets</a></li>
								<li><a href="help/index.php">Help</a></li>
								<li><a href="needhelp.php">Contact Us</a></li>
							</ul>
							<!-- end internal links -->
						</div>
						<!-- end internal/external links container -->
			
	 			</div>
				<!-- end main header -->

				<div id="contentWrapper">			
				<!-- begin main content -->
		   	<div id="mainContent" >
<?php  
  /* start inner content */
	echo "<div id=\"content\"><div id=\"container\"><div id=\"top\">\n";
	echo "<h1>Welcome to $appname</h1>\n";
	echo "<p>$appname is designed to support all communication, scheduling, and collaboration activities of IPRO teams. Through the use of $appname you can send/receive e-mail messages, store/retrieve files, access/update a team calendar, and view a complete history of a team's activities since the creation of the team in $appname. Welcome to $appname, a team management tool developed by IIT students.</p>\n";
	
if(!$currentUser)
		echo "<p>To use $appname simply enter your username and password in the login pane to the left. Your initial username is the first part of your IIT email address, or your entire email address if you do not have or use an IIT email. Your initial password is the first part of your email address (text appearing before the @). If you are a first-time user, please change your password upon entry to $appname (from the My Profile page).</p>";
	echo "</div>\n";
	
	$rand = mysql_fetch_array($db->query("select * from RandomQuotes order by rand() limit 1"));
	$randid = $rand['iID'];
	$ext = $rand['sExtension'];
	$title = htmlspecialchars($rand['sTitle']);
	$desc = htmlspecialchars($rand['sDesc']);
	echo "<div id=\"left\"><div id=\"randphotobox\"><img src=\"http://ipro.iit.edu/home/images/students1/photos/$randid.$ext\" alt=\"Random photo\" id=\"randphoto\" /></div><br />\n";
	echo "<span id=\"randtitle\">$title</span><br />\n<span id=\"randdesc\">$desc</span>";
	echo "</div>\n";
	
	$heading = $numAnnounce ? $firstAnnouncement->getHeadingHTML() : "Welcome to $appname";
	$body = $numAnnounce ? $firstAnnouncement->getBodyHTML() : 'There are no announcements';
	echo "<div class=\"right\"><div class=\"box\">\n";
	echo "<span class=\"box-header\">Announcements</span>\n";
	echo "<div class=\"announcement-heading\" id=\"announcehead\">$heading</div>\n";
	echo "<div class=\"announcement-body\" id=\"announcebody\">$body</div>\n";
	if($numAnnounce > 1)
		echo "<a href=\"javascript:announcements.navigation('prev')\">Prev</a> <a href=\"javascript:announcements.navigation('pause')\">Pause</a> <a href=\"javascript:announcements.navigation('next')\">Next</a>\n";
	echo "</div></div>\n";
	
	echo "<div class=\"right\"><div class=\"box\">\n";
	echo "<span class=\"box-header\">$appname Knowledge Management</span>\n";
	echo "<div class=\"announcement-heading\">Guest to $appname</div>\n";
	echo "<div class=\"announcement-body\">You can <a href=\"iknow/main.php\">search and browse</a> IPRO team deliverables.</div>\n";
	echo "</div></div>\n";
  /* end content */
?>	
</div>

<!-- start copyright statement -->
<p id="copyright">Copyright &copy; 2009 Illinois Institute of Technology Interprofessional Projects Program. All Rights Reserved.</p>
<!-- end copyright statement -->
</div><!-- end content -->
</div><!-- end mainContent -->
<?php
	require('sidebar.php');
?>
</div>
		<!-- end contentWrapper -->
		
		<!--begin footer -->
		<div id="footer">
			<!-- start copyright statement -->
			<p id="copyright">iGroups &copy; 2009 &nbsp;<a href="http://www.ipro.iit.edu">Interprofessional Projects Program</a> </p> <p id="department"> <a href="http://iit.edu">Illinois Institute of Technology</a></p>
			<!-- end copyright statement -->
		</div>
		<!-- end footer -->
		
	</div> 
	<!-- end container -->

</body>
</html>
