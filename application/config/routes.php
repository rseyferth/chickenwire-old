<?php

	use ChickenWire\Core\ChickenWire;

	ChickenWire::AddRoute("/", 						array(	"controller" => "Hours" ));
	ChickenWire::AddRoute("/{anything}/{#id}",			array(	"controller" => "Hours" ));
	

?>