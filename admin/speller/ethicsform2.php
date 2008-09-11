<?php

$db = mysql_connect('sloth.iit.edu', 'terran4000', 't3rran4000');
	if (!$db) {
		die("Connection error: ".mysql_connect_error());
		return false;
	}

mysql_select_db('iknow');

$ipros = array();
$query = mysql_query("SELECT p.sIITID, p.sName from Projects p, Semesters s, ProjectSemesterMap m where p.iID = m.iProjectID and m.iSemesterID = s.iID AND s.bActiveFlag=1 ORDER BY p.sIITID");
while ($result = mysql_fetch_array($query))
	$ipros[] = $result;

$query = mysql_query("SELECT sSemester FROM Semesters WHERE bActiveFlag=1");
$curSem = mysql_fetch_row($query);

if (isset($_POST['submit'])) {
mysql_select_db('ipro');

mysql_query("INSERT INTO ethics VALUES (\"{$_POST['ipro']}\", \"{$curSem[0]}\", curdate(), \"{$_POST['pressure1']}\", \"{$_POST['risk1']}\", \"{$_POST['pressure2']}\", \"{$_POST['risk2']}\", \"{$_POST['pressure3']}\", \"{$_POST['risk3']}\", \"{$_POST['pressure4']}\", \"{$_POST['risk4']}\", \"{$_POST['pressure5']}\", \"{$_POST['risk5']}\", \"{$_POST['pressure6']}\", \"{$_POST['risk6']}\", \"{$_POST['pressure7']}\", \"{$_POST['risk7']}\", \"{$_POST['pressure8']}\", \"{$_POST['risk8']}\", \"{$_POST['pressure9']}\", \"{$_POST['risk9']}\", \"{$_POST['pressure10']}\", \"{$_POST['risk10']}\", \"{$_POST['pressure11']}\", \"{$_POST['risk11']}\", \"{$_POST['pressure12']}\", \"{$_POST['risk12']}\", \"{$_POST['pressure13']}\", \"{$_POST['risk13']}\", \"{$_POST['pressure14']}\", \"{$_POST['risk14']}\", \"{$_POST['pressure15']}\", \"{$_POST['risk15']}\", \"{$_POST['pressure16']}\", \"{$_POST['risk16']}\", \"{$_POST['pressure17']}\", \"{$_POST['risk17']}\", \"{$_POST['pressure18']}\", \"{$_POST['risk18']}\", \"{$_POST['pressure19']}\", \"{$_POST['risk19']}\", \"{$_POST['pressure20']}\", \"{$_POST['risk20']}\", \"{$_POST['pressure21']}\", \"{$_POST['risk21']}\")");

print "<html><head><META HTTP-EQUIV='refresh' CONTENT='3;URL=http://ipro.iit.edu'></head>";
print "<body><h3>Thank you for your submission. You are now being taken back to the IPRO Home Page.</h3></body></html>";

 }

else {

?>

<html>
<head>
<title>Code of Ethics</title>
<script language="javascript" type="text/javascript" src="speller/spellChecker.js">
</script>

<!-- Call a function like this to handle the spell check command -->
<script language="javascript" type="text/javascript">
function openSpellChecker() {
	var speller = new spellChecker();
	speller.spellCheckAll();
}
</script>
</head>

<body>

<form action='ethicsform2.php' method='post'>
<h2>Worksheet for Pressure, Risks, and Principles</h2>

<b>IPRO Project Number / Title:</b>&nbsp;
<select name='ipro'>

<?php

foreach ($ipros as $ipro)
 print "<option value='{$ipro['sIITID']}'>{$ipro['sIITID']} / {$ipro['sName']}</option>"

?>

</select>
<br><br>

<h4>Overarching Team Principle</h4>
<input type='text' size='70' name='principle'><br>
<br>

<h4>Law and Regulation</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure1'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk1'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure2'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk2'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure3'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk3'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon1'></textarea></td></tr>
</table>
<p>Example: "A physician shall respect the law and also recognize a
responsibility to seek changes in those requirements which are contrary to the
best interests of the patient." Note: All Examples are taken from the <i>AMA
Code of Ethics</i></p><br>

<h4>Contracts</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure4'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk4'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure5'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk5'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure6'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk6'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon2'></textarea></td></tr>
</table>
<p>Example: "A physician shall respect the rights of patients, colleagues, and
other health professionals, and shall safeguard patient confidences and
privacy within the constraints of the law."</p><br>

<h4>Professional Codes</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure7'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk7'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure8'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk8'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure9'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk9'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon3'></textarea></td></tr>
</table>
<p>Example: "A physican shall uphold the standards of professionalism, be
honest in all professional interactions, and strive to report physicians
deficient in character or competence, or engaging in fraud or deception, to
appropriate entities."</p><br>

<h4>Business and Industry Environment</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure10'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk10'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure11'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk11'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure12'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk12'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon4'></textarea></td></tr>
</table>
<p>Example: "A physician shall, in the provision of appropriate patient care,
except in emergencies, be free to choose whom to serve, with whom to
associate, and the environment in which to provide medical care."</p><br>

<h4>Community</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure13'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk13'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure14'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk14'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure15'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk15'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon5'></textarea></td></tr>
</table>
<p>Example: "A physician shall recognize a responsibility to participate in
activities contributing to the improvement of the community and the betterment
of public health. A physician shall support access to medical care for all people."</p><br>

<h4>Personal Relations</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure16'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk16'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure17'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk17'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure18'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk18'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon6'></textarea></td></tr>
</table>
<p>Example: "A physician shall, while caring for a patient, regard
responsibility to the patient as paramount."</p><br>

<h4>Moral Values</h4>
<table>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure19'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk19'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure20'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk20'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Pressure:</td><td><input type='text' size='70' name='pressure21'></td></tr>
<tr><td>Risk:</td><td><input type='text' size='70' name='risk21'></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td valign='top'>Canon(s):</td><td><textarea cols='70' name='canon7'></textarea></td></tr>
</table>
<p>Example: "A physician shall be dedicated to providing competent medical
care, with compassion and respect for human dignity and rights."</p><br>
<br>
<center><input type='button' value='Spell Check' onClick="openSpellChecker();">&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit' value='Submit'></center><br>
</form>
</body>

</html>

<?php } ?>
