<?php

	use ChickenWire\Core\ChickenWire;
	use ChickenWire\Request\Format;

	ChickenWire::AddRoute("/", 						array(	"controller" => "Hours",		"action" => "Index", 		"alias" => "/index" ));


?>