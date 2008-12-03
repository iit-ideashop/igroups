<?php
if ( !class_exists( "SuperString" ) ) {
	class SuperString {
		var $str;
		
		function SuperString( $string ) {
			$this->setString( $this->stripEscapeChars( $string ) );
		}
		
		function stripEscapeChars( $string ) {
			$tempString = str_replace("\\'", "'", $string);
			$tempString = str_replace('\\"', '"', $tempString);
			$tempString = str_replace('\\\\', '\\', $tempString);
			return $tempString;
		}
		
		function getString() {
			return $this->str;
		}
		
		function setString( $string ) {
			$this->str = $string;
		}
		
		function getDBString() {
			$tempString = str_replace('\\', '\\\\', $this->str);
			$tempString = str_replace("'", "\\'", $tempString);
			$tempString = str_replace('"', '\\"', $tempString);
			return $tempString;
		}
		
		function getHTMLString() {
			$tempString = str_replace( "\r\n", "<br />", $this->str );
			$tempString = str_replace( "'", "&#39;", $tempString );
			return $tempString;
		}
		
		function getJavaString() {
			$tempString = str_replace( "\\", "\\\\", $this->str );
			$tempString = str_replace( "\"", "&quot;", $tempString );
			$tempString = str_replace( "\r", "\\r", $tempString );
			$tempString = str_replace( "\n", "\\n", $tempString );
			$tempString = str_replace( "'", "\\'", $tempString );
			return $tempString;
		}

		function almostJavaString() {
			$tempString = str_replace( "\\", "\\\\", $this->str );
			$tempString = str_replace( "\r", "\\r", $tempString );
			$tempString = str_replace( "\n", "\\n", $tempString );
			$tempString = str_replace( "'", "\\'", $tempString );
			return $tempString;
		}
	}
	
	function quickDBString( $str ) {
		$tmp = new SuperString( $str );
		return $tmp->getDBString();
	}

    function distanceOfTimeInWords($from_time, $to_time = 0, $include_seconds = false) {
        $distance_in_minutes = round(abs($to_time - $from_time) / 60);
        $distance_in_seconds = round(abs($to_time - $from_time));

        if ($distance_in_minutes >= 0 and $distance_in_minutes <= 1) {
            if (!$include_seconds) {
                return ($distance_in_minutes == 0) ? 'less than a minute' : '1 minute';
            } else {
                if ($distance_in_seconds >= 0 and $distance_in_seconds <= 4) {
                    return 'less than 5 seconds';
                } elseif ($distance_in_seconds >= 5 and $distance_in_seconds <= 9) {
                    return 'less than 10 seconds';
                } elseif ($distance_in_seconds >= 10 and $distance_in_seconds <= 19) {
                    return 'less than 20 seconds';
                } elseif ($distance_in_seconds >= 20 and $distance_in_seconds <= 39) {
                    return 'half a minute';
                } elseif ($distance_in_seconds >= 40 and $distance_in_seconds <= 59) {
                    return 'less than a minute';
                } else {
                    return '1 minute';
                }
            }
        } elseif ($distance_in_minutes >= 2 and $distance_in_minutes <= 44) {
            return $distance_in_minutes . ' minutes';
        } elseif ($distance_in_minutes >= 45 and $distance_in_minutes <= 89) {
            return 'about 1 hour';
        } elseif ($distance_in_minutes >= 90 and $distance_in_minutes <= 1439) {
            return 'about ' . round(floatval($distance_in_minutes) / 60.0) . ' hours';
        } elseif ($distance_in_minutes >= 1440 and $distance_in_minutes <= 2879) {
            return '1 day';
        } elseif ($distance_in_minutes >= 2880 and $distance_in_minutes <= 43199) {
            return 'about ' . round(floatval($distance_in_minutes) / 1440) . ' days';
        } elseif ($distance_in_minutes >= 43200 and $distance_in_minutes <= 86399) {
            return 'about 1 month';
        } elseif ($distance_in_minutes >= 86400 and $distance_in_minutes <= 525599) {
            return round(floatval($distance_in_minutes) / 43200) . ' months';
        } elseif ($distance_in_minutes >= 525600 and $distance_in_minutes <= 1051199) {
            return 'about 1 year';
        } else {
            return 'over ' . round(floatval($distance_in_minutes) / 525600) . ' years';
        }
    }

   /*
    * Like distanceOfTimeInWords, but where to_time is fixed to the output of time()
    *
    */
    function timeAgoInWords($from_time, $include_seconds = false) {
        return $this->distanceOfTimeInWords($from_time, time(), $include_seconds);
    }
}
?>
