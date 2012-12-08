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

		public static function SortOn(Array &$array, $property, $ascending = true) {

			// Create function
			$compareFunction = function($a, $b) use ($ascending, $property) {
				
				$varA = $a->$property;
				$varB = $b->$property;
				if ($varA == $varB) return 0;
				if ($ascending == true) {
					return ($varA > $varB) ? 1 : -1;
				} else {
					return ($varA < $varB) ? 1 : -1;
				}
				
			};

			// Do it
			usort($array, $compareFunction);

		}

		/**
		 * Get last item in the given array
		 * @param Array $array The array
		 * @return * The last item in the given array, or null when the array is empty.
		 */
		public static function LastItem(Array $array) {

			// Anything?
			if (count($array) == 0) {
				return null;
			}

			// Return it
			return $array[count($array) - 1];

		}

	}

?>