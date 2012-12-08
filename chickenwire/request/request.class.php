<?php

	namespace ChickenWire\Request;

	use ChickenWire\Core\ChickenWire;
	use ChickenWire\Lib\Util\StringUtil;


	class Request {


		public $method;
		public $protocol;
		public $domain;
		public $fullPath;
		public $path;
		public $accept;
		public $acceptLanguage;
		public $uri;
		public $extension;
		public $format;
		
		public function __construct($uri = "") {

			// Uri passed?
			if (empty($uri)) {

				// HTTPS?
				$this->protocol = (array_key_exists("HTTPS", $_SERVER) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";

				// Use server data to form url
				$this->uri = $this->protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				// And get some other info at the same time
				$this->method = $_SERVER['REQUEST_METHOD'];
				$this->domain = $_SERVER['HTTP_HOST'];
				$this->fullPath = $_SERVER['REQUEST_URI'];
				$this->accept = Format::FromHTTPAcceptHeader($_SERVER['HTTP_ACCEPT']);
				$this->acceptLanguage = explode(",", substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ";")));

			} else {

				// Parse url
				$this->uri = $uri;
				throw new Exception("Parsing of url not implemented", 1);
				
				//preg_match_all()


			}

			// Check base path
			$this->path = $this->fullPath;
			if (StringUtil::startsWith($this->path, BASE_PATH)) {

				// Remove it from path
				$basePath = BASE_PATH;
				if (StringUtil::endsWith($basePath, "/")) {
					$basePath = substr($basePath, 0, -1);
				}
				$this->path = substr($this->path, strlen($basePath));

			}

			// Check path for ?querystring
			$qPos =strpos($this->path, '?');
			if ($qPos > 0) {
				$this->path = substr($this->path, 0, $qPos);
			}


			// Check path for extension
			preg_match_all("/(.+)\.([a-z]+)$/i", $this->path, $matches);
			if (count($matches[0]) == 1 && !is_null(Format::FromExtension($matches[2][0]))) {

				// Extension found
				$this->extension = $matches[2][0];
				$this->path = $matches[1][0];

				// Now look for format that matches this one (if extension overrides Accept headers)
				if (ChickenWire::get("extensionOverridesAcceptHeaders") !== false || is_null($this->accept)) {

					// Find format
					$format = Format::FromExtension($this->extension);
					if (!is_null($format)) {
						$this->accept = array($format);
					}

				}

			}

			// Store format
			$this->format = $this->accept[0];
			
				

		}


	}
	

?>