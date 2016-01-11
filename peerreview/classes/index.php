<?php
        include_once('db.php');
        if(stristr($_SERVER['REQUEST_URI'], 'index.php') === false)
		errorPage('Directory Listings Forbidden', 'Directory listings are not allowed', 403);
	else
		errorPage('Not Found', 'That file was not found', 404);
?>
