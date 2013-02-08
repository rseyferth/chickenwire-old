<?php

	namespace ChickenWire\ActiveRecord;

	class Connection {


		/**
		 * Create a new ActiveRecord database connection
		 *
		 * From URI:
		 *
		 * <code>
		 * Connection::Create('[protocol]://[user]:[pass]@[host]:[port]/[dbname]?[extra-option1]=[extra-value1]&[extra-option2]=[extra-value2]');
		 * Connection::Create('mysql://user:pass@localhost:3109/mydatabase?encoding=UTF8');
		 * </code>
		 *
		 *
		 * From options hash:
		 * <code>
		 * Connection::Create(array(
		 * 		"protocol" => "mysql",
		 * 		"user" => "username",
		 * 		"pass" => "password",
		 * 		"host" => "localhost",
		 * 		"port" => 3109,
		 * 		"encoding" => "UTF8"
		 * ));
		 * </code>
		 * 
		 */
		public static function Create($options) {

			// Is the argument a string or a hash?
			if (is_string($options)) {

				// Parse URI
				$options = static::parseDBUri($options);

			}


			var_dump($options);


		}


		protected static function parseDBUri($uri) {  

			// Do a preg_match_all!
			preg_match_all('/^([^:]+):\/\/(([^:@]+):([^@]+))@?([^:\/]+)(:([0-9]+))?\/([^?\/]+)(\?(.*))?$/', $uri, $matches);

			// Create options object out of it
			$options = array(
				"protocol" => $matches[1][0],
				"user" => $matches[3][0],
				"pass" => $matches[4][0],
				"host" => $matches[5][0],
				"port" => $matches[7][0],
				"database" => $matches[8][0]
			);

			// Extra options?
			if (!empty($matches[10][0])) {

				// Split it!
				$pairs = explode("&", $matches[10][0]);
				foreach ($pairs as $pair) {
					list($key, $value) = explode("=", $pair);
					if (!empty($key) && !empty($value)) {
						$options[$key] = $value;
					}
				}

			}

			// Done!
			return $options;

		}


		public function __construct() {


		}
		




	}

?>