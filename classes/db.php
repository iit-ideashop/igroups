<?php
if ( !class_exists( "dbConnection" ) ) {
	class dbConnection {
		var $igroupsConn, $iknowConn;
		
		function dbConnection() {
			$ireview_db_name = "peer_review";
                        $ireview_db_user = "terran4000";
                        $ireview_db_pass = "CbsoZcD2";

			 $igroups_db_name = "igroups";
			 $igroups_db_user = "igr0upsUser";
			 $igroups_db_pass = "mxYwnc36";

			$this->ireviewConn = mysql_connect("localhost", $ireview_db_user, $ireview_db_pass) or
                                die("Could not connect: " . mysql_error());

                        mysql_select_db($ireview_db_name, $this->ireviewConn) or
                                die("Could not select DB: " . mysql_error());
			
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
		//With the introduction of 3.0 the iknow and igroups databases were combined so all previous iknow queries are handeled as igroups queries now	
		function iknowQuery( $query ) {
			 $query_db_result = mysql_query($query, $this->igroupsConn) or
				                                 die("Invalid query:\n<br />\n$query<br />\n" . mysql_error($this->igroupsConn));
                         return $query_db_result;
		}
	
		function igroupsInsertID() {
			return mysql_insert_id( $this->igroupsConn );
		}
		
		function iknowInsertID() {
			return mysql_insert_id( $this->igroupsConn );
		}
	}
}



$arr1 = array();
$arr1[] = "&lt;a";
$arr1[] = "&lt;/a";
$arr1[] = "&gt;";
$arr1[] = "&quot;";
$arr2 = array();
$arr2[] = "<a";
$arr2[] = "</a";
$arr2[] = ">";
$arr2[] = "\"";

function anchorTags($str)
{
	$arr3 = $arr1;
	$arr4 = $arr2;
	$arr3[] = "\n";
	$arr4[] = "<br /";
	return str_replace($arr1, $arr2, $str);
}

function anchorTagsNoBreaks($str)
{
	return str_replace($arr1, $arr2, $str);
}
?>
