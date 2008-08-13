<?php
if ( !class_exists( "dbConnection" ) ) {
	class dbConnection {
		var $igroupsConn, $iknowConn;
		
		function dbConnection() {
			$iknow_db_name = "iknow";
			$iknow_db_user = "ikn0wUser";
			$iknow_db_pass = "13ZSP6w4";

			$ireview_db_name = "peer_review";
                        $ireview_db_user = "terran4000";
                        $ireview_db_pass = "CbsoZcD2";

                        $this->ireviewConn = mysql_connect("localhost", $ireview_db_user, $ireview_db_pass) or
                                die("Could not connect: " . mysql_error());

                        mysql_select_db($ireview_db_name, $this->ireviewConn) or
                                die("Could not select DB: " . mysql_error());
/*
			$this->iknowConn = mysql_connect("iknow.iit.edu", $iknow_db_user, $iknow_db_pass) or
					die("Could not connect: " . mysql_error());
*/
			$this->iknowConn = mysql_connect("localhost", $iknow_db_user, $iknow_db_pass) or
					die("Could not connect: " . mysql_error());
		
			mysql_select_db($iknow_db_name, $this->iknowConn) or
					die("Could not select DB: " . mysql_error());
		
			
			$igroups_db_name = "igroups";
			$igroups_db_user = "igr0upsUser";
			$igroups_db_pass = "mxYwnc36";
			
			$this->igroupsConn = mysql_connect("localhost", $igroups_db_user, $igroups_db_pass) or
					die("Could not connect: " . mysql_error());
					
			mysql_select_db( $igroups_db_name, $this->igroupsConn ) or
					die("Could not select DB: " . mysql_error());
		}
		
		function igroupsQuery( $query ) {
			$query_db_result = mysql_query($query, $this->igroupsConn) or
				die("Invalid query:\n<br>\n$query<br>\n" . mysql_error($this->igroupsConn));
			return $query_db_result;
		}
	
		function ireviewQuery( $query ) {
                        $query_db_result = mysql_query($query, $this->ireviewConn) or
                                die("Invalid query:\n<br>\n$query<br>\n" . mysql_error($this->ireviewConn));
                        return $query_db_result;
                }
	
		function iknowQuery( $query ) {
			$query_db_result = mysql_query($query, $this->iknowConn) or
				die("Invalid query:\n<br>\n$query<br>\n" . mysql_error($this->iknowConn));
			return $query_db_result;
		}
	
		function igroupsInsertID() {
			return mysql_insert_id( $this->igroupsConn );
		}
		
		function iknowInsertID() {
			return mysql_insert_id( $this->iknowConn );
		}
	}
}
?>
