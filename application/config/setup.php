<?php

	use ChickenWire\Core\ChickenWire;
	use ChickenWire\Request\Format;

	// Set application namespace
	ChickenWire::set("applicationNamespace", "WipkipAdmin");

	// Set PHP extension
	ChickenWire::set("phpExtension", "php");

	// Make it so that extensions are more important than accept headers
	ChickenWire::set("extensionOverridesAcceptHeaders", true);
		
	// Set the default output format
	ChickenWire::set("defaultFormat", Format::HTML());

	// Set the default output formats for routes
	ChickenWire::set("defaultRouteFormats", array(Format::HTML(), Format::JSON()));

	// Set locations for static assets
	ChickenWire::set("pathCSS", "static/css");
	ChickenWire::set("pathJavascript", "static/js");
	ChickenWire::set("pathImages", "static/images");

	// Set whether to use absolute URLs
	ChickenWire::set("useAbsoluteUrls", false);
	


?>