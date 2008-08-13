<?php
	session_start();

	include_once( "classes/db.php" );
	include_once( "classes/person.php" );
	include_once( "classes/group.php" );
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
		
	if ( isset( $_GET['selectCategory'] ) ) {
		$_SESSION['selectedCategory'] = $_GET['selectCategory'];
	}
	
	if ( isset( $_SESSION['selectedCategory'] ) ){
		$currentCat = new Category( $_SESSION['selectedCategory'], $db );
		if(!$currentCat->getGroupID())
			$currentCat->setGroup($currentGroup->getID());
		if(!$currentCat->getSemester())
			$currentCat->setSemester($currentGroup->getSemester());
		if(!$currentCat->getGroupType())
			$currentCat->setType($currentGroup->getType());
	}
	else
		$currentCat = false;
		
	function printTR() {
		static $i=0;
		if ( $i )
			print "<tr class='shade'>";
		else
			print "<tr>";
		$i=!$i;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<!-- This web-based application is Copyrighted &copy; 2007 Interprofessional Projects Program, Illinois Institute of Technology -->

<html>
<head>
	<title>iGROUPS - Group Email</title>
	<style type="text/css">
		@import url("default.css");
		
		#container {
			padding:0;
		}
		
		#catbox {
			float:left;
			width:30%;
			margin:5px;
			padding:2px;
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

	</script>
</head>
<body>
	<div id="topbanner">
<?php
		print $currentGroup->getName();
?>
	</div>
<?php
	if ( isset( $_POST['createcat'] ) ) {
		
		createCategory( $_POST['catname'], $_POST['catdesc'], $currentGroup->getID(), $currentGroup->getType(), $currentGroup->getSemester(), $db );
?>
		<script type="text/javascript">
			showMessage("Category created");
		</script>
<?php
	}
	
	if ( isset( $_POST['editcat'] ) ) {
		$currentCat->setName( $_POST['newcatname'] );
		$currentCat->setDesc( $_POST['newcatdesc'] );
		$currentCat->updateDB();
	}
	
	if ( isset( $_POST['delcat'] ) ) {
		$currentCat->delete();
	}
	
	if ( isset( $_POST['delete'] ) && $_POST['delete']==1 && isset($_POST['email'])) {
		foreach( $_POST['email'] as $emailid => $val ) {
			$email = new Email( $emailid, $db );
			if ( $currentUser->isGroupModerator( $email->getGroup() ) )
				$email->delete();
		}
		if ( isset( $_POST['categories'] )) {
		foreach( $_POST['categories'] as $catid => $val ) {
			$category = new Category( $catid, $db );
			if ( $currentUser->isGroupModerator( $category->getGroup() ) ) {
				$emails = $category->getEmails();
				foreach ( $emails as $email ) {
					$email->setCategory(0);
					$email->updateDB();
				}
				$category->delete();
			}
		}
		}
?>
		<script type="text/javascript">
			showMessage("Selected items successfully deleted");
		</script>
<?php
	}
	
	if ( isset( $_POST['move'] ) ) {
		foreach( $_POST['email'] as $emailid => $val ) {
			$email = new Email( $emailid, $db );
			if ( $currentUser->isGroupModerator( $email->getGroup() ) ) {
				$email->setCategory($_POST['targetcategory']);
				$email->updateDB();
			}
		}
?>
		<script type="text/javascript">
			showMessage("Selected items successfully moved");
		</script>
<?php
	}	
?>
	<form method="post" action="email.php">
	<div id="container">
		<div id="catbox">
			<div id="columnbanner">
				Your categories:
			</div>
			<div id="menubar">
				<ul> <?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
					<li><a href="#" onClick="document.getElementById('create-category').style.visibility='visible';">Create Category</a></li>
					<?php
                                        if ( $currentUser->isGroupModerator( $currentGroup ) ) {
					?>
                                                <li><a href="#" onClick="document.getElementById('edit-category').style.visibility='visible';">Edit/Delete Category</a></li>
					<?php } ?>
				</ul>
				<?php } ?>
			</div>
			<div id="cats">
<?php
				$categories = $currentGroup->getGroupCategories();
				if ( $currentCat )
					print "<a href='email.php?selectCategory=0'><img src='img/folder.gif' border=0></a>&nbsp;<a href='email.php?selectCategory=0'>Uncategorized</a><br>";
				else
					print "<a href='email.php?selectCategory=0'><img src='img/folder-expanded.gif' border=0></a>&nbsp;<a href='email.php?selectCategory=0'><strong>Uncategorized</strong></a><br>";
				foreach ( $categories as $category ) {
					if ( $currentCat && $currentCat->getID() == $category->getID() )
						print "<a href='email.php?selectCategory=".$category->getID()."'><img src='img/folder-expanded.gif' border=0></a>&nbsp;<a href='email.php?selectCategory=".$category->getID()."'><strong>".$category->getName()."</strong></a><br>";
					else
						print "<a href='email.php?selectCategory=".$category->getID()."'><img src='img/folder.gif' border=0></a>&nbsp;<a href='email.php?selectCategory=".$category->getID()."'>".$category->getName()."</a><br>";
				}
?>
			</div>
		</div>
		<div id="emailbox">
<?php
			if ( $currentCat ) {
				$emails = $currentCat->getEmails();
				$name = $currentCat->getName();
			}
			else {
				$emails = $currentGroup->getGroupEmails();		
				$name = "Uncategorized";
			}
			
			print "<div id='columnbanner'>Contents of $name:</div>";
?>
			<div id="menubar">
			<?php if (!$currentUser->isGroupGuest($currentGroup)) { ?>
				<ul>
					<li><a href="#" onClick="document.getElementById('send-window').style.visibility='visible';">Send Email</a></li>
					<li><a href="#" onClick="window.location.href='searchemail.php';">Search Email</a></li>
<?php
					if ( $currentUser->isGroupModerator( $currentGroup ) ) {
?>
						<li><a href="#" onClick="document.getElementById('move-emails').style.visibility='visible';">Move Selected</a></li>
						<li><a href="#" onClick="document.getElementById('delete').value='1'; document.getElementById('delete').form.submit()">Delete Selected</a>
						<input type='hidden' id='delete' name='delete' value='0'></li>
<?php
					}
?>
				</ul>
			<?php } ?>
			</div>
			<div id="emails">
				<table width='100%'>
<?php				
				foreach ( $emails as $email ) {
					$author = $email->getSender();
					printTR();
					if ($email->hasAttachments()) 
						$img = '&nbsp;<img src="img/attach.gif">';
					else
						$img = '';
					print "<td colspan=2><a href='displayemail.php?id=".$email->getID()."' onClick='showEmail(".$email->getID()."); return false;'>".$email->getShortSubject()."</a>$img</td><td>".$author->getFullName()."</td><td>".$email->getDate()."</td><td><input type='checkbox' name='email[".$email->getID()."]'></td></tr>";
				}
?>
				</table>
			</div>
		</div>
	</div>
	<div id="move-emails" class="window">
		<div class="window-topbar">
			Move Emails
			<input class="close-button" type="button" onClick="document.getElementById('move-emails').style.visibility='hidden';">
		</div>
		<div class="window-content">
			Move emails to category:
			<select name="targetcategory"><option value="0">No Category</option>
<?php
			$categories = $currentGroup->getGroupCategories();
			foreach ( $categories as $category ) {
				print "<option value=".$category->getID().">".$category->getName()."</option>";
			}
?>
			</select><br>
			<input type="submit" name="move" value="Move Selected Emails">
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
		<iframe src="sendemail.php" width=100% height="500" frameborder="0">
		</iframe>
	</div>
	<div id="create-category" class="window">
		<div class="window-topbar">
			Create Category
			<input class="close-button" type="button" onClick="document.getElementById('create-category').style.visibility='hidden';">
		</div>
		<div class="window-content">
			<form method="post" action="email.php">
				Category Name: <input type="text" name="catname"><br>
				Category Description:<input type="text" name="catdesc"><br>
				<input type="submit" name="createcat" value="Create Category">
			</form>
		</div>
	</div>
	<div id="edit-category" class="window">
		<div class="window-topbar">
			Edit Category
			<input class="close-button" type="button" onClick="document.getElementById('edit-category').style.visibility='hidden';">
		</div>
		<div class="window-content">
			<form method="post" action="email.php">
<?php
				if ( $currentCat ) {
					print "Current Category Name: ".$currentCat->getName()."<br>";
					print "New Category Name: <input type='text' name='newcatname' value='".$currentCat->getName()."'><br>";
					print "New Category Description: <input type='text' name='newcatdesc' value='".$currentCat->getDesc()."'><br>";
					print '<input type="submit" name="editcat" value="Edit Category">';
					print '<input type="submit" name="delcat" value="Delete Category">';
				}
				else {
					print "You cannot edit the current active category.";
				}
?>
			</form>
		</div>
	</div>
</body>
</html>
