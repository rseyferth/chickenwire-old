<?php

	namespace ChickenWire\Core;

	/**
	 * ChickenWire main class
	 * 
	 * The ChickenWire class has two main functions:
	 * - It is the central location for static information about the application, such as routing, requests etc.
	 * - It handles requests and calls the appropriate controllers
	 * 
	 * @version 0.0.1
	 * @author Ruben Seyferth
	 */
	class ChickenWire {


		// Instance private properties
		protected $_request;
		protected $_activeRoute;
		protected $_urlVariables;


		/**
		 * Create new ChickenWire process
		 * 
		 * @param Request $request The request to handle through ChickenWire. When no request is passed, the HTTP request will be used.
		 */ 
		public function __construct(Request $request = null) {

			// Check if request is given
			if (is_null($request)) {

				// Get it from actual request
				$request = new Request();

			}
			$this->_request = $request;

			// Find route
			$this->findRoute();

		}


		/**
		 * Find a Route that matches current request
		 */
		protected function findRoute() {

			// Loop through routes
			$routeFound = null;
			foreach (self::$_routes as $route) {
				if ($route->Match($this->_request, $routeVars) == true) {
					$routeFound = $route;
					break;
				}
			}

			// Found nothing?
			if ($routeFound == null) { 

				// 404
				ChickenWire::Send404();
				die;

			}

			// Store it
			$this->_urlVariables = $routeVars;
			$this->_activeRoute = $routeFound;

			// Activate controller
			$this->callController();

		}

		/**
		 * Call controller for the Route that was found
		 */
		protected function callController() {

			// Create class name
			$className = self::$_applicationNamespace . "\\Controllers\\" . $this->_activeRoute->controller;

			// Create the controller
			$controllerInstance = new $className($this->_request, $this->_activeRoute, $this->_urlVariables);

			// Is it a Controller-derived classed?
			if (!is_subclass_of($controllerInstance, "ChickenWire\Core\Controller")) {
				throw new \Exception(get_class($controllerInstance) . " does not extend ChickenWire\\Core\\Controller.", 1);				
			}
			
			// Call the action!
			$controllerInstance->{$this->_activeRoute->action}();


		}


		static private $_routes = array();
		static private $_applicationNamespace;


		/**
		 * Add a route to the routing for ChickenWire
		 * 
		 * @param string $pattern The url matching pattern for the Route. For details on how to use patterns, see Route.
		 * @param Array $options Associative array containing options. For available options, see Route::__construct.
		 */
		public static function AddRoute ($pattern, $options) {

			// Create route
			$route = new Route($pattern, $options);

			// Add it!
			array_push(self::$_routes, $route);

		}

		/**
		 * Set the namespace for your application
		 * @param string $namespace The namespace for your application. E.g.: myapplication
		 */
		public static function setApplicationNS($namespace) {
			self::$_applicationNamespace = $namespace;
		}

		/**
		 * Get the namespace that was set for your application
		 */
		public static function getApplicationNS() {
			return self::$_applicationNamespace;
		}


		public static function Send404() {

			// Render error controller?
			header("HTTP/1.1 404 Not Found");
			echo ("<h1>404</h1><p>TODO: Render error page.</p>");

		}



	}

?>