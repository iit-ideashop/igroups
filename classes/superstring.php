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
}
?>
