<?php
	include_once('globals.php');

	require('doctype.php');
	require('appearance.php');
	echo "<link rel=\"stylesheet\" href=\"skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
	foreach($altskins as $altskin)
		echo "<link rel=\"alternate stylesheet\" href=\"skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>
<title><?php echo $appname; ?> - Reimbursement Instructions</title>
</head><body style="font-family: arial, helvetica, verdana, sans-serif; font-size: .8em;">
<h2>Reimbursement Instructions</h2>
<p>You will need to submit original copies of receipts for all expenses you incur related to your IPRO project in order to be reimbursed. In addition to receipts, more information is required for some expenses:</p>
<ul>
    <li><strong>Online purchases.</strong> You must also include a copy of your bank statement showing the expense being charged to your account.</li>
    <li><strong>Air travel.</strong> You must submit both a copy of your bank statement showing the airfare being charged to your account and boarding passes for all flights taken.</li>
    <li><strong>Mileage.</strong> Receipts are not necessary, but you must submit a print out of your route on <a href="http://maps.google.com/" title="Google Maps">Google Maps</a> or a similar website to calculate the number of miles you drove. Currently mileage is being reimbursed at $.585 per mile.</li>
</ul>
<p>Only expenses previously approved through the iGroups budget system will be reimbursed.</p>
<p>Please submit all required information to the <strong>IPRO Office (room 4C7, 3424 South State Street)</strong> with your name, email address, mailing address, IPRO number, and an explanation of the expense.</p>
<p>Expenses under $150 will be reimbursed in cash through the Cashier's Office in the Main Building once you obtain the proper form from the IPRO Office.</p>
<p>Expenses over $150 are reimbursed by check, which will be mailed by IIT's Accounting department. Reimbursement generally takes 2-3 weeks.</p>
<p>If you have questions, please contact Jennifer Keplinger, IPRO Program Coordinator, at <a href="mailto:keplinger@iit.edu" title="Email Jennifer Keplinger">keplinger@iit.edu</a>.</p>
</body>
</html>
