<?php

	namespace ChickenWire\Core;

	use ChickenWire\Request\Request;
	use ChickenWire\Request\Format;

	use ChickenWire\ActiveRecord\Connection;

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

			// Loop accept headers
			$routeFound = null;
			foreach ($this->_request->accept as $format) {

				// Loop through routes
				foreach (self::$_routes as $route) {
					if ($route->Match($this->_request, $format, $routeVars) == true) {
						$routeFound = $route;
						break;
					}
				}

				// Found something?
				if (!is_null($routeFound)) {
					break;
				}

			}

			

			// Found nothing?
			if (is_null($routeFound)) { 

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
			$className = self::get("applicationNamespace") . "\\Controllers\\" . $this->_activeRoute->controller;

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
		static private $_settings = array();
		static private $_databases = array();

		static private $logger = null;


		public static function DB($connection = "") {

			// No connection given?
			if (empty($connection)) {

				// Get default connection
				$connection = ChickenWire::get("database::default");

				// Null?
				if (is_null($connection)) {
					throw new Exception("No default database has been selected. Use ChickenWire::set('database::default').", 1);					
				}

			}

			// Is the connection already made?
			if (array_key_exists($connection, self::$_databases)) {

				// Return it now
				return self::$_databases[$connection];

			}

			// Look up settings
			$dbs = ChickenWire::get("database::connections");
			if (is_null($dbs) || !array_key_exists($connection, $dbs)) {
				throw new Exception("No database connection found for '$connection'.", 1);					
			}
			
			// Create
			$conn = Connection::Create($dbs[$connection]);
			
			// Store and return
			self::$_databases[$connection] = $conn;
			return $conn;


		}

		/**
		 * Add a route to the routing for ChickenWire
		 * 
		 * @param string $pattern The url matching pattern for the Route. For details on how to use patterns, see Route.
		 * @param Array $options Associative array containing options. For available options, see Route::__construct. One special property you can use in this function is `alias`, which will in fact create the Route multiple times with the different patterns. `alias` can either be a string or an array of strings.
		 */
		public static function AddRoute ($pattern, $options) {

			// Alias?
			if (array_key_exists("alias", $options)) {

				// Remove the alias setting
				$alias = $options['alias'];
				unset ($options['alias']);

				// 1 alias?
				if (is_string($alias)) {

					// Create alias'ed route
					$route = new Route($alias, $options);
					array_push(self::$_routes, $route);

				} elseif (is_array($alias)) {

					// Loop 'em
					foreach ($alias as $a) {
						$route = new Route($a, $options);
						array_push(self::$_routes, $route);
					}

				}

			}

			// Create route			
			$route = new Route($pattern, $options);
			
			// Add it!
			array_push(self::$_routes, $route);

		}


		public static function AddResource ($pattern, $options) {

			throw new Exception("Not yet implemented", 1);
			

		}



		/**
		 * Get a setting from ChickenWire
		 */
		public static function get($name) {
			if (array_key_exists($name, self::$_settings)) {
				return self::$_settings[$name];
			} else {
				return null;
			}
		}

		/**
		 * Set a setting for ChickenWire
		 */
		public static function set($name, $value) {


			// Property already there?
			if (array_key_exists($name, self::$_settings)) {

				// Just set it then
				self::$_settings[$name] = $value;
				return;

			}

			// Known property?
			if ($name != "applicationNamespace" && 
				$name != "defaultCharset" &&
				$name != "defaultLayout" &&
				$name != "environment" &&
				$name != "database::connections" &&
				$name != "database::default" &&
				$name != "phpExtension" &&
				$name != "extensionOverridesAcceptHeaders" &&
				$name != "memCache" &&
				$name != "log" &&
				$name != "defaultFormat" &&
				$name != "defaultRouteFormats" &&
				$name != "pathCSS" &&
				$name != "pathJavascript" &&
				$name != "pathImages" &&
				$name != "useAbsoluteUrls") {

				throw new \Exception("There is no setting for '" . $name . "'", 1);				

			}

			// Set it
			self::$_settings[$name] = $value;
			return;

		}



		public static function Send404() {

			// Render error controller?
			header("HTTP/1.1 404 Not Found");
			echo ("<h1>404</h1><p>TODO: Render error page.</p>");

		}


		private static function _Log() {

			// Check arguments and add datetime
			$args = func_get_args();
			$args[0] = '[' . date('Y-m-d H:i:s') . '] ' . $args[0];

			// Call the logger
			call_user_func_array(array(self::$logger, 'Log'), $args);

		}


		public static function Log() {

			

			// Logger defined?
			if (!is_null(self::$logger)) {

				call_user_func_array("self::_Log", func_get_args());
				return;

			}

			// Log enabled?
			if (!is_null(self::get("log"))) {

				// Create logger
				$loggerClass = "ChickenWire\\Lib\\Log\\" . self::get("log");
				self::$logger = new $loggerClass();

				call_user_func_array("self::_Log", func_get_args());
				return;

			}

		}



	}

?>