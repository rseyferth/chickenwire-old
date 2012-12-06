<?php

	class Request {


		public $method;
		public $protocol;
		public $domain;
		public $fullPath;
		public $path;
		public $accept;
		public $acceptLanguage;
		public $uri;
		
		public function __construct($uri = "") {

			// Uri passed?
			if (empty($uri)) {

				// Use server data to form url
				$this->uri = (strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) == "https" ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				// And get some other info at the same time
				$this->method = $_SERVER['REQUEST_METHOD'];
				$this->protocol = substr($_SERVER['SERVER_PROTOCOL'], 0, 5) == "https" ? "https" : "http";
				$this->domain = $_SERVER['HTTP_HOST'];
				$this->fullPath = $_SERVER['REQUEST_URI'];
				$this->accept = explode(",", substr($_SERVER['HTTP_ACCEPT'], 0, strpos($_SERVER['HTTP_ACCEPT'], ";")));
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

		}


	}
	

?>