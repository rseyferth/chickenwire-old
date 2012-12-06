<?php

	namespace ChickenWire\Lib\Util;

	class StringUtil {

		public static function randomString($length = 10) {
			    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
			    srand((double)microtime()*1000000);
			    $i = 0;
			    $pass = '' ;
			    while ($i <= $length) {

			        $num = rand() % 33;
			        $tmp = substr($chars, $num, 1);
			        $pass = $pass . $tmp;
			        $i++;

			    }
			    return $pass;

			}

		public static function validateEmail($email) {

			if (preg_match('/^[^\W][a-zA-Z0-9_\.]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/', $email)) { 
				return true;
			} else {
				return false;
			}

		}

		public static function unaccent($string, $encoding = "UTF-8") {
			return preg_replace('/&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);/i', '$1', htmlentities($string, ENT_COMPAT, $encoding));
		}

		public static function slug($str){
			$str = strtolower(trim($str));
			$str = preg_replace('/[^a-z0-9-]/', '-', $str);
			$str = preg_replace('/-+/', "-", $str);
			return $str;
		}

		public static function startsWith($haystack, $needle)
		{
			return !strncmp($haystack, $needle, strlen($needle));
		}

		public static function endsWith($haystack, $needle)
		{
			$length = strlen($needle);
			if ($length == 0) {
				return true;
			}

			return (substr($haystack, -$length) === $needle);
		}

		public static function titleize($string) {
			return ucfirst($string);
		}

		public static function pluralize($string, $count = 2) {

			// Count 1?
			if ($count == 1) {
				return $string;
			}


			$plural = array(
				array( '/(quiz)$/i',               "$1zes"   ),
				array( '/^(ox)$/i',                "$1en"    ),
				array( '/([m|l])ouse$/i',          "$1ice"   ),
				array( '/(matr|vert|ind)ix|ex$/i', "$1ices"  ),
				array( '/(x|ch|ss|sh)$/i',         "$1es"    ),
				array( '/([^aeiouy]|qu)y$/i',      "$1ies"   ),
				array( '/([^aeiouy]|qu)ies$/i',    "$1y"     ),
					array( '/(hive)$/i',               "$1s"     ),
					array( '/(?:([^f])fe|([lr])f)$/i', "$1$2ves" ),
					array( '/sis$/i',                  "ses"     ),
					array( '/([ti])um$/i',             "$1a"     ),
					array( '/(buffal|tomat)o$/i',      "$1oes"   ),
					array( '/(bu)s$/i',                "$1ses"   ),
					array( '/(alias|status)$/i',       "$1es"    ),
					array( '/(octop|vir)us$/i',        "$1i"     ),
					array( '/(ax|test)is$/i',          "$1es"    ),
					array( '/s$/i',                    "s"       ),
					array( '/$/',                      "s"       )
				);

			$irregular = array(
			array( 'move',   'moves'    ),
			array( 'sex',    'sexes'    ),
			array( 'child',  'children' ),
			array( 'man',    'men'      ),
			array( 'person', 'people'   )
			);

			$uncountable = array( 
			'sheep', 
			'fish',
			'series',
			'species',
			'money',
			'rice',
			'information',
			'equipment'
			);

			// save some time in the case that singular and plural are the same
			if ( in_array( strtolower( $string ), $uncountable ) )
			return $string;

			// check for irregular singular forms
			foreach ( $irregular as $noun )
			{
			if ( strtolower( $string ) == $noun[0] )
				return $noun[1];
			}

			// check for matches using regular expressions
			foreach ( $plural as $pattern )
			{
			if ( preg_match( $pattern[0], $string ) )
				return preg_replace( $pattern[0], $pattern[1], $string );
			}
		
			return $string;
		}


	}


?>