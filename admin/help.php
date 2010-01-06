<?php
	include_once('../globals.php');
	include_once('checkadmin.php');
	include_once('../classes/helpcat.php');
	include_once('../classes/knownissue.php');
	
	//------Start of Code for Form Processing-----------------------//
	$edit = array();
	$editkeys = array('C', 'T', 'I');
	foreach($editkeys as $key)
		$edit[$key] = 0;
	if(isset($_GET['edit']))
	{
		$fl = substr($_GET['edit'], 0, 1);
		$id = substr($_GET['edit'], 1);
		if(in_array($fl, $editkeys) && is_numeric($id))
			$edit[$fl] = floor($id);
	}
	else if(isset($_GET['del']))
	{
		$fl = substr($_GET['del'], 0, 1);
		$id = substr($_GET['del'], 1);
		if(in_array($fl, $editkeys) && is_numeric($id))
		{
			switch($fl)
			{
				case 'C':
					$cat = new HelpCategory($id, $db);
					if($cat->isValid())
					{
						if($cat->delete())
							$message = 'Category successfully deleted';
						else
							$message = 'Failed to delete category';
					}
					break;
				case 'T':
					$topic = new HelpTopic($id, $db);
					if($topic->isValid())
					{
						if($topic->delete())
							$message = 'Topic successfully deleted';
						else
							$message = 'Failed to delete topic';
					}
					break;
				case 'I':
					$issue = new KnownIssue($id, $db);
					if($issue->isValid())
					{
						if($issue->delete())
							$message = 'Issue successfully deleted';
						else
							$message = 'Failed to delete issue';
					}
					break;
			}
		}
	}
	else if(isset($_POST['category']))
	{
		$id = $_POST['whelpcatid'];
		if($id && is_numeric($id))
		{ //Edit
			$cat = new HelpCategory($id, $db);
			if($cat->isValid())
				$ok = $cat->setTitle($_POST['whelpcattitle']);
			else
				$ok = false;
			$message = ($ok ? 'Category successfully edited' : 'Failed to edit category');
		}
		else
		{ //New
			$cat = createHelpCategory($_POST['whelpcattitle'], $db);
			$message = ($cat ? 'Category successfully created' : 'Failed to create category');
		}
	}
	else if(isset($_POST['topic']))
	{
		$id = $_POST['whelptopicid'];
		$newcat = new HelpCategory($_POST['whelptopiccat'], $db);
		if(!$newcat->isValid())
			$message = 'Category assignment invalid: You must choose a category.';
		else if($id && is_numeric($id))
		{ //Edit
			$top = new HelpTopic($id, $db);
			if($top->isValid())
			{
				$ok1 = $top->setTitle($_POST['whelptopictitle']);
				$ok2 = $top->setText($_POST['whelptopictext']);
				$ok3 = $top->assignTo($newcat);
				$ok = $ok1 && $ok2 && $ok3;
			}
			else
				$ok = false;
			$message = ($ok ? 'Topic successfully edited' : 'Failed to edit topic');
		}
		else
		{ //New
			$top = createHelpTopic($_POST['whelptopictitle'], $_POST['whelptopictext'], $newcat, $db);
			$message = ($top ? 'Topic successfully created' : 'Failed to create topic');
		}
	}
	else if(isset($_POST['knownissue']))
	{
		$id = $_POST['wknownissueid'];
		if($id && is_numeric($id))
		{ //Edit
			$ki = new KnownIssue($id, $db);
			if($ki->isValid())
			{
				$ok1 = $ki->setIssue($_POST['wtheissue']);
				$ok2 = $ki->setResolved($_POST['wresolved'] ? true : false);
				$ok = $ok1 && $ok2;
			}
			else
				$ok = false;
			$message = ($ok ? 'Known issue successfully edited' : 'Failed to edit known issue');
		}
		else
		{ //New
			$iss = createKnownIssue($_POST['wtheissue'], $db);
			$message = ($iss ? 'Known issue successfully created' : 'Failed to create known issue');
		}
	}
	
	//---------Start XHTML Output-----------------------------------//
	
	require('../doctype.php');
	require('../iknow/appearance.php');

	echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<script type="text/javascript" src="../windowfiles/dhtmlwindow.js">
/***********************************************
* DHTML Window Widget- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for legal use.
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<script type="text/javascript">
//<![CDATA[
	function hideAllTopics()
	{
		var head = document.getElementsByTagName('head')[0];         
		var cssNode = document.createElement('style');
		cssNode.type = 'text/css';
		cssNode.innerHTML = 'div.helptopic { display: none; }';
		head.appendChild(cssNode);
<?php
	if(isset($_GET['new']))
	{
		if($_GET['new'] == 'C')
			echo "\t\tvar win=dhtmlwindow.open('newcatbox', 'div', 'whelpcat', 'Create Category', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0', 'recal');\n";
		else if($_GET['new'] == 'T')
			echo "\t\tvar win=dhtmlwindow.open('newtopicbox', 'div', 'whelptopic', 'Create Topic', 'width=700px,height=300px,left=300px,top=100px,resize=0,scrolling=0', 'recal');\n";
		else if($_GET['new'] == 'I')
			echo "\t\tvar win=dhtmlwindow.open('newissuebox', 'div', 'wknownissue', 'Create Known Issue', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0', 'recal');\n";
	}
	else foreach($edit as $key => $val)
	{
		if($val)
		{
			if($key == 'C')
				echo "\t\tvar win=dhtmlwindow.open('editbox', 'div', 'whelpcat', 'Edit Category', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0', 'recal');\n";
			else if($key == 'T')
				echo "\t\tvar win=dhtmlwindow.open('editbox', 'div', 'whelptopic', 'Edit Topic', 'width=700px,height=300px,left=300px,top=100px,resize=0,scrolling=0', 'recal');\n";
			else if($key == 'I')
				echo "\t\tvar win=dhtmlwindow.open('editbox', 'div', 'wknownissue', 'Edit Issue', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0', 'recal');\n";
			break;
		}
	}
?>
	}

	function toggle(id)
	{
		document.getElementById(id).style.display = (document.getElementById(id).style.display == 'block') ? 'none' : 'block';
	}
//]]>
</script>
<title><?php echo $appname;?> - Help Center Management</title>
</head>
<body onload="hideAllTopics()">
<?php
	
	 /**** begin html head *****/
   require('htmlhead.php'); 
  //starts main container
  /****end html head content ****/
	
	echo "<h1>Help Center Management</h1>\n";
	echo "<p>Here, you can add, remove, and edit topics that appear in the $appname Help Center.</p>\n";
	
	echo "<h2>Create a new...</h2>\n";
	if($edit['C'])
		echo "<p><a href=\"help.php?new=C\">Category</a> - ";
	else
		echo "<p><a href=\"#\" onclick=\"win=dhtmlwindow.open('newcatbox', 'div', 'whelpcat', 'Create Category', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Category</a> - ";
	if($edit['T'])
		echo "<a href=\"help.php?new=T\">Topic</a> - ";
	else
		echo "<a href=\"#\" onclick=\"win=dhtmlwindow.open('newtopicbox', 'div', 'whelptopic', 'Create Topic', 'width=700px,height=300px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Topic</a> - ";
	if($edit['I'])
		echo "<a href=\"help.php?new=I\">Known Issue</a></p>\n";
	else
		echo "<a href=\"#\" onclick=\"win=dhtmlwindow.open('newissuebox', 'div', 'wknownissue', 'Create Known Issue', 'width=350px,height=150px,left=300px,top=100px,resize=0,scrolling=0'); return false\">Known Issue</a></p>\n";
	echo "<h2>Help Topics</h2>\n";
	$categories = getAllHelpCategories($db);
	if(count($categories)) 
	{
		echo "<ul>\n";
		foreach($categories as $catid => $cat)
		{
			$title = htmlspecialchars(stripslashes($cat->getTitle()));
			echo "\t<li>$title [<a href=\"help.php?edit=C$catid\">Edit</a>]";
			$topics = $cat->getAllTopics();
			if(count($topics))
			{
				echo "<ul>\n";
				foreach($topics as $tid => $topic)
				{
					$ttitle = htmlspecialchars(stripslashes($topic->getTitle()));
					$ttext = stripslashes($topic->getText());
					echo "\t\t<li><a href=\"#\" onclick=\"toggle('HT{$topic->getID()}')\">$ttitle</a> [<a href=\"help.php?edit=T$tid\">Edit</a>]<div id=\"HT{$topic->getID()}\" class=\"helptopic\"><br />$ttext</div></li>\n";
				}
				echo "\t</ul>\n";
			}
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
	else
		echo "<p>No topics found.</p>\n";
	
	echo "<h2>Known Issues</h2>\n";
	$issues = getAllIssues($db);
	if(count($issues)) 
	{
		echo "<ul>\n";
		foreach($issues as $iid => $issue)
		{
			echo "\t<li>{$issue->getIssue()} [<a href=\"help.php?edit=I$iid\">Edit</a>]</li>\n";
		}
		echo "</ul>\n";
	}
	else
		echo "<p>No issues found.</p>\n";
	
	//---------DHTML Windows-----------------------------------//
	if($edit['C'])
	{
		$cat = new HelpCategory($edit['C'], $db);
		$cattitlevalue = htmlspecialchars(stripslashes($cat->getTitle()));
	}
	echo "<div id=\"whelpcat\" class=\"window-content\">\n";
		if($edit['C'])
			echo "<p><a href=\"help.php?del=C{$edit['C']}\">Delete this category</a> (cannot be undone!)</p>\n";
		echo "<form action=\"help.php\" method=\"post\" id=\"whelpcatform\"><fieldset>\n";
		echo "<label>Title: <input type=\"text\" name=\"whelpcattitle\" id=\"whelpcattitle\" value=\"$cattitlevalue\" /></label><br />\n";
		echo "<input type=\"hidden\" name=\"whelpcatid\" id=\"whelpcatid\" value=\"{$edit['C']}\" /><input type=\"submit\" name=\"category\" value=\"Submit\" /> <input type=\"reset\" /></fieldset></form>\n";
	echo "</div>\n";
	if($edit['T'])
	{
		$top = new HelpTopic($edit['T'], $db);
		$toptitlevalue = htmlspecialchars(stripslashes($top->getTitle()));
		$toptext = htmlspecialchars(stripslashes($top->getText()));
		$topcatid = $top->getCategory()->getID();
	}
	echo "<div id=\"whelptopic\" class=\"window-content\">\n";
		if($edit['T'])
			echo "<p><a href=\"help.php?del=T{$edit['T']}\">Delete this topic</a> (cannot be undone!)</p>\n";
		echo "<form action=\"help.php\" method=\"post\" id=\"whelptopicform\"><fieldset>\n";
		echo "<label>Title: <input type=\"text\" name=\"whelptopictitle\" id=\"whelptopictitle\" value=\"$toptitlevalue\" /></label><br />\n";
		echo "<label>Category: <select id=\"whelptopiccat\" name=\"whelptopiccat\"><option value=\"0\">Select a category</option>\n";
		if(count($categories)) foreach($categories as $id => $cat)
		{
			$title = htmlspecialchars(stripslashes($cat->getTitle()));
			echo "<option value=\"$id\"".($topcatid == $id ? ' selected="selected"' : '').">$title</option>\n";
		}
		echo "</select></label><br />\n";
		echo "<label>Text: <textarea name=\"whelptopictext\" id=\"whelptopictext\" rows=\"10\" cols=\"80\">$toptext</textarea></label><br />\n";
		echo "<input type=\"hidden\" name=\"whelptopicid\" id=\"whelptopicid\" value=\"{$edit['T']}\" /><input type=\"submit\" name=\"topic\" value=\"Submit\" /> <input type=\"reset\" /></fieldset></form>\n";
		echo "<p>Note: The Text field allows XHTML. It does no checking for XML correctness; you must do that yourself. Headings should start at level 3 (&lt;h3&gt;).</p>\n";
	echo "</div>\n";
	$res = false;
	if($edit['I'])
	{
		$iss = new KnownIssue($edit['I'], $db);
		$theissue = htmlspecialchars(stripslashes($iss->getIssue()));
		$res = $iss->isResolved();
	}
	echo "<div id=\"wknownissue\" class=\"window-content\">\n";
		if($edit['I'])
			echo "<p><a href=\"help.php?del=I{$edit['I']}\">Delete this issue</a> (cannot be undone!)</p>\n";
		echo "<form action=\"help.php\" method=\"post\" id=\"wknownissueform\"><fieldset>\n";
		echo "<label>Issue: <input type=\"text\" name=\"wtheissue\" id=\"wtheissue\" value=\"$theissue\" /></label><br />\n";
		if($edit['I'])
			echo "<label><input type=\"checkbox\" name=\"wresolved\" id=\"wresolved\"".($res ? ' checked="checked"' : '')." /> Resolved</label><br />\n";
		echo "<input type=\"hidden\" name=\"wknownissueid\" id=\"wknownissueid\" value=\"{$edit['I']}\" /><input type=\"submit\" name=\"knownissue\" value=\"Submit\" /> <input type=\"reset\" /></fieldset></form>\n";
	echo "</div>\n";
?>


<?php
 	/**** begin html footer*****/
  //include rest of html layout file
  require('htmlfoot.php');
  // ends main container
  /****** end html footer*****/
?>
</body></html>
