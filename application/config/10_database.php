<?php

	use ChickenWire\Core\ChickenWire;

	// Set database connections
	ChickenWire::set("database::connections", array(
		"development" => "mysql://root:1395.nl@localhost/wipkip_admin",
		"production" => ""
	));

	// Set default
	ChickenWire::set("database::default", ChickenWire::get("environment"));


?>