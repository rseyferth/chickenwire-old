<?php

	namespace ChickenWire\Core;

	use ChickenWire\Lib\Util\StringUtil;
	use ChickenWire\Lib\Util\ArrayUtil;
	use ChickenWire\Request\Request;
	use ChickenWire\Request\Format;

	/**
	 * ## Examples
	 * - new Route("/kies-bijeenkomst",						array( "controller" => "Application",			"action" => "SelectMeeting"	));
	 * - ChickenWire::AddRoute("/kies-bijeenkomst/{meeting}",			array( "controller" => "Application",			"action" => "ConfirmMeeting" ));
	 *
 	 * 	
	 */


	class Route {

		public $pattern;			/** The pattern used to match a request */
		
		public $controller;			/** The controller to use when this Route is matched  */
		public $action;				/** The action (method) to call on the controller */
		public $model;				/** The Model with which this Route is associated */
		public $method;				/** The HTTP method to filter on (GET/POST) */
		public $formats;
		public $protocol;
		public $domains;

		private $_regExPattern;		
		private $_regExVars;


		/**
		 * Create a new Route. 
		 * 
	 	 * @param string $pattern The pattern (TODO: explain patterns)
	 	 * @param array $options Associative array containging the route configuration.
	 	 * 
	 	 * Possible options are:
		 * 
	 	 * option 			| type 			| default 	| description
	 	 * ---------------- | ------------- | --------- | -------------------------
	 	 * controller       | string 		| 			| The controller for this route
	 	 * action 			| string 		| "Index" 	| The controller-action to use
	 	 * model 			| string 		| ""		| The model for this route. This can be used to auto-generate urls for models, using Route::editPath($modelInstance), Route::newPath(ModelClass), etc.
	 	 * method 			| string 		| "" 		| HTTP method filter (POST or GET). When no method is passed, all methods are accepted
	 	 * protocol			| string		| ""		| Server protocol filter ("https" or "http"). By default all protocols are accepted
	 	 * domains			| array			| 			| An array of domains this route is active for. By default this is empty, meaning all domains will match. You can use regular expressions in this array.
		 * formats 			| array			| 			| The list of accepted formats. See ..........
		 * 
		 */
		public function __construct($pattern, $options) {

			// No controller given?
			if (!array_key_exists("controller", $options) || empty($options['controller'])) {
				throw new \Exception("You cannot create a Route without specifying a controller.", 1);				
			}

			// Default options
			$options = ArrayUtil::Defaults($options, array(
				"action" => "Index",
				"model" => "",
				"method" => "",
				"protocol" => "",
				"controllerPath" => "",
				"domains" => null,
				"formats" => null
			));

			// Localize
			$this->pattern = $pattern;
			$this->controller = $options['controller'];
			$this->action = $options['action'];
			$this->model = $options['model'];
			$this->method = $options['method'];
			$this->protocol = $options['protocol'];
			$this->controllerPath = $options['controllerPath'];
			$this->domains = $options['domains'];
			$this->formats = $options['formats'];

			// Is there a variable in pattern?
			if (strstr($this->pattern, "{") !== false) {

				// Find all { }'s
				preg_match_all("/({([^}]*)})/", $this->pattern, $matches);
				$this->_regExVars = $matches[2];

				// Create regular expression to match this pattern
				$regex = "/" . preg_replace(
									array(
										"/({#([^}]*)})/",
										"/({([^}]*)})/",
									),
									array(
										"([0-9]+)",
										"([a-zA-Z0-9_-]+)"
									),
									str_replace("/", "\\/", $this->pattern))
										 . "+$/";
				$this->_regExPattern = $regex;
				
			} else {
				$this->_regExPattern = '/' . str_replace("/", "\\/", $this->pattern). "$/";								
				$this->_regExVars = array();
			}


		}


		/**
		 * Match the Route against the given Request
		 * 
		 * @param Request $request The request to match against the Route
		 * @param Format $format The format to match against the Route
		 * @param Array &$variables A pass-by-reference array, that will be filled with the variable matches in the Route
		 * @return boolean Whether the Request matches this Route
		 */
		public function Match(Request $request, Format $format, array &$variables = null) {

			// Check if method matches
			if (!empty($this->method) && $request->method != $this->method) {
				return false;
			}

			// Check if protocol matches
			if (!empty($this->protocol) && $request->protocol != $this->protocol) {
				return false;
			}

			// Check if domain matches
			if (!is_null($this->domains) && count($this->domains) > 0) {
				$domainMatched = false;
				foreach ($this->domains as $domain) {

					// Is it a regular expression?
					if (!preg_match("/^\/.*\/[a-z]+$/", $domain)) {
						$domain = "/^$domain$/";
					}					

					// Match against the request's domain
					if (preg_match($domain, $request->domain)) {
						$domainMatched = true;
						break;
					}
				}
				if ($domainMatched == false) {
					return false;
				}
			}

			// Check if format matches
			if (!in_array($format, $this->getFormats())) {
				return false;
			}

			// Match it!
			preg_match_all($this->_regExPattern, $request->path, $matches);

			// Anything?
			if (count($matches[0]) == 0) {
				return false;
			}

			// We have a MATCH!
			// Check variables
			$variables = array();
			foreach ($this->_regExVars as $index => $var) {

				// #?
				if (substr($var, 0, 1) == "#") {
					$var = substr($var, 1);
				}
				$variables[$var] = $matches[$index + 1];
			}

			// GOOD! :)
			return true;

		}

		private function getFormats() {

			// No formats given?
			if (is_null($this->formats)) {

				// Use the default formats
				$formats = ChickenWire::get("defaultRouteFormats");
				
				// Still empty?
				if (is_null($formats)) {

					// That's no good
					throw new \Exception("ChickenWire could not determine a format for Route '" . $this->pattern . "`. Either define formats for each Route, or use ChickenWire::set(\"defaultRouteFormats\", ...) in your configuration.", 1);
					

				}
				return $formats;

			} else {
				return $this->formats;
			}



		}



	}

?>