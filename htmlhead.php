<!-- this is the head for all pages, revised design -->	
<!-- begin main container -->
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
							<?php if (isset($_SESSION['userID']) && !$_GET['logout']){
								require('main_navigation.html')
							?>
							<!-- end internal links -->
						</div>
						<!-- end internal/external links container -->
			
	 			</div>
				<!-- end main header -->

				<div id="contentWrapper">		
        <!-- begin sidebar -->
				<?php
					require('sidebar.php');
				?>
        <!-- end sidebar -->
	
				<!-- begin main content -->
		   	<div id="mainContent" >
			
