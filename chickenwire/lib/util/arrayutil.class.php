<?php 

	namespace ChickenWire\Lib\Util;

	/**
	 * Array utility functions
	 */
	class ArrayUtil {

		/**
		 * Combine two arrays into one (mimicing the jQuery.extend method). 
		 * 
		 * @param Array $array The main associative
		 * @param Array $default Associative array containing default values
		 * @return Array The merged array
		 */
		public static function Defaults(Array $array, Array $default) {

			// Loop through default values
			$newArray = $array;
			foreach ($default as $key => $value) {
				if (!array_key_exists($key, $newArray)) {
					$newArray[$key] = $value;
				}
			}
			return $newArray;


		}

	}

?>