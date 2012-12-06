<?php
	/**
	 * class ChickenWire
	 * The ChickenWire class has two main functions:
	 * - It is the central location for static information about the application, such as
	 *   routing, requests, class paths, etc.
	 * - It handles requests and calls the appropriate controllers
	 * 
	 * @package core
	 * @version 0.0.1
	 * @author Ruben Seyferth
	 */
	class ChickenWire {


		private static $classPaths;


		/**
		 * Get all paths that contain classes that will AutoLoad
		 */
		public static function getClassPaths() {
			if (self::$classPaths == null) {
				self::$classPaths = array();
			}
			return self::$classPaths;
		}

		/**
		 * Add a class path to ChickenWire, allowing classes in it to AutoLoad.
		 * Accepts one or more string arguments
		 */
		public static function addClassPath() {
			if (self::$classPaths == null) {
				self::$classPaths = array();
			}
			$args = func_get_args();
			foreach ($args as $arg) {
				array_push(self::$classPaths, $arg);	
			}
			
		}




		// Instance private properties
		private $_request;


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

			// 
			var_dump($this->_request);

		}



	}

?>