<?php
$accept = $_SERVER['HTTP_ACCEPT'];
$ua = $_SERVER['HTTP_USER_AGENT'];

function setXHTML()
{
	global $st, $checked, $disabled, $selected, $readonly, $contenttype;
	header('Content-Type: application/xhtml+xml');
	$contenttype = 'application/xhtml+xml';
	$st = ' /';
	$checked = 'checked="checked"';
	$disabled = 'disabled="disabled"';
	$selected = 'selected="selected"';
	$readonly = 'readonly="readonly"';
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n";
	echo "<head>\n<meta http-equiv=\"Content-Type\" content=\"$contenttype; charset=utf-8\"$st>\n";
}

function setHTML()
{
	global $st, $checked, $disabled, $selected, $readonly, $contenttype;
	$contenttype = 'text/html';
	$st = '';
	$checked = 'checked';
	$disabled = 'disabled';
	$selected = 'selected';
	$readonly = 'readonly';
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
	echo "<html lang=\"en\">\n";
	echo "<head>\n<meta http-equiv=\"Content-Type\" content=\"$contenttype; charset=utf-8\"$st>\n";
}
if(isset($_GET['html']))
	setHTML();
else if(isset($_GET['xhtml']))
	setXHTML();
else if(isset($accept) && isset($ua))
	(stristr($accept, 'application/xhtml+xml') !== false || stristr($ua, 'W3C_Validator') !== false) ? setXHTML() : setHTML();
else if(stristr($ua, 'W3C_Validator') !== false)
	setXHTML();
else
	setHTML();
?>
<!-- This web-based application is copyrighted 2009 Interprofessional Projects Program, Illinois Institute of Technology -->
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['rootdir']; ?>style.css"<?php echo $st; ?>>
<script type="text/javascript" src="global.js"></script>
