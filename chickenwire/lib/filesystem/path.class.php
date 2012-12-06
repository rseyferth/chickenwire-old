<?php

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