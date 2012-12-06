<?php

	// Auto load classes
	function __autoload($class_name) {

		// Filename
		$filename = strtolower($class_name) . ".class.php";

		// Look for it in auto load paths
		foreach (ChickenWire::getCLassPaths() as $path) {
			$fullPath = BASE_DIR . "/". $path . $filename;
			if (file_exists($fullPath)) {
				require_once($fullPath);
				return;
			}
		}
		

	}


?>