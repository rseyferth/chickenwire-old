<?php

	use ChickenWire\Core\ChickenWire as ChickenWire;

	// Auto load classes
	function __autoload($class_name) {

		// Convert namespaces to file path
		$parts = explode("\\", strtolower($class_name));

		// A ChickenWire class?
		if (count($parts) > 0 && $parts[0] == "chickenwire") {

			// Look for it one level up (I'm in chickenwire/core/)
			array_shift($parts);
			$filename = __DIR__ . "/../" . implode("/", $parts) . ".class.php";
			if (file_exists($filename)) {
				require_once($filename);
			}

		} elseif (count($parts) > 0 && $parts[0] == strtolower(ChickenWire::getApplicationNS())) {

			// Look for it in the application directory
			array_shift($parts);
			$filename = APPLICATION_DIR . "/" . implode("/", $parts) . ".class.php";
			if (file_exists($filename)) {
				require_once($filename);
			}

		}
		
		

	}


?>