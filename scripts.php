<script src="http://www.google.com/jsapi"></script>
<script src="ChangeLocation.js"></script>
<script src="Announcement.js"></script>
<script> 
	google.load("jquery", "1.3.2");
	google.load("jqueryui", "1.7.2");
</script>
<script> 
    $(document).ready(function () {
		$("#datepick").datepicker({ dateFormat: 'yy-mm-dd', showOn: 'focus' });
		$("#datepickalt").datepicker({ dateFormat: 'mm/dd/yy', showOn: 'focus' });
	});
</script>
<script type="text/javascript" src="scripts/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="scripts/js/jquery-ui-1.7.2.custom.min.js"></script>
<link type="text/css" href="skins/default/custom-theme/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
