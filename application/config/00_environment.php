<?php

	use ChickenWire\Core\ChickenWire;

	// Detect environment
	switch ($_SERVER['HTTP_HOST']) {

		case "admin.wipkip.com":
			ChickenWire::set("environment", "production");
			break;
		
		case 'admin.wipkip.dev':
		default:
			ChickenWire::set("environment", "development");
			break;

	}

?>