<?php

	namespace ChickenWire\Lib\Filesystem;

	use ChickenWire\Lib\Util\StringUtil as StringUtil;


	/**
	 * Path manipulation and formatting
	 * 
	 * The path class is a static class used to format and manipulate path strings
	 * 
	 * @author Ruben Seyferth
	 * @version 0.1
	 */
	class Path {



		public static function Construct() {

			// Loop through parts
			$parts = func_get_args();
			$path = "";
			foreach ($parts as $part) {
				$part = str_replace("\\", "/", $part);
				if (!StringUtil::endsWith($part, "/")) {
					$part .= "/";
				}
				$path .= $part;
			}
			return $path;

		}

	}

?>