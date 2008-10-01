<?php
	function decodeEmailSort($sort) {
		switch($sort)
		{
			case 1:
				return " order by Emails.sSubject";
			case -1:
				return " order by Emails.sSubject desc";
			case 2:
				return " order by People.sLName, People.sFName";
			case -2:
				return " order by People.sLName desc, People.sFName desc";
			case 3:
				return " order by Emails.dDate, Emails.iID";
			case -3:
				return " order by Emails.dDate desc, Emails.iID desc";
			default:
				return " order by Emails.iID desc";
		}
	}
	
	function decodeFileSort($sort) {
		switch($sort)
		{
			case 1:
				return " order by Files.sTitle";
			case -1:
				return " order by Files.sTitle desc";
			case 2:
				return " order by Files.sDescription";
			case -2:
				return " order by Files.sDescription desc";
			case 3:
				return " order by People.sLName, People.sFName";
			case -3:
				return " order by People.sLName desc, People.sFName desc";
			case 4:
				return " order by Files.dDate, Files.iID";
			case -4:
				return " order by Files.dDate desc, Files.iID desc";
			default:
				return " order by Files.iID desc";
		}
	}
?>
