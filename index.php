<?php

	/*****************************************************
	 *      Elementary ChickenWire (CW) Configuration    *
	 *****************************************************/

	/**
	 * Environment
	 * 
	 * In most cases the environment will be set to one of the following:
	 * - development
	 * - testing
	 * - production
	 */
	define("ENVIRONMENT", "development");


	/**
	 * Framework paths
	 *
	 * Specify the paths for the application and ChickenWire folders.
	 * By default you can leave these as they are.
	 *
	 * NB: Use relative paths without a trailing slash
	 */
	$pathChickenWire = "chickenwire";
	$pathApplication = "application";
	 

	/**
	 * That is all the configuration for now. For further configuration
	 * see the application/config/ folder. All files in this folder will be
	 * loaded upon boot of the framework.
	 *
	 * NB: Do not change anything below this line.
	 */

	// Get current path
	$pathRoot = pathinfo(__FILE__, PATHINFO_DIRNAME);

	// Now boot!
	require_once($pathRoot . "/" . $pathChickenWire . "/boot.php");

?>