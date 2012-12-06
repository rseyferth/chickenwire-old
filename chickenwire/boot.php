<?php

	// Load ChickenWire main class manually
	require_once('core/chickenwire.class.php');

	// Add basic class paths
	ChickenWire::addClassPath(
		$pathChickenWire . "/core/",
		$pathChickenWire . "/controller/",
		$pathChickenWire . "/lib/filesystem/",
		$pathChickenWire . "/lib/util/",
		$pathChickenWire . "/model/"
	);
	
	// Get autoloading of classes to work
	require_once('core/autoload.php');

	// Set paths
	define("BASE_DIR", $pathRoot);
	define("CHICKENWIRE_DIR", Path::Construct($pathRoot, $pathChickenWire));
	define("APPLICATION_DIR", Path::Construct($pathRoot, $pathApplication));

	// Determine the BASE PATH and URL
	define("BASE_PATH", substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/") + 1));
	define("BASE_URL", (strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) == "https" ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . BASE_PATH);

	
	// Now load all the config files in the framework first, then the application config
	foreach (glob(Path::Construct(CHICKENWIRE_DIR, "config") . "*.php") as $filename) {		// WILL THERE BE ANY CONFIG IN THE FRAMEWORK???
		include_once($filename);
	}
	foreach (glob(Path::Construct(APPLICATION_DIR, "config") . "*.php") as $filename) {
		include_once($filename);
	}
	
	// Now run chickenwire!
	new ChickenWire();

?>