<?php

	namespace ChickenWire\Core;

	class Controller {


		protected $route;
		protected $urlParams;
		protected $request;

		/**
		 * Create a new Controller a instance.
		 * 
		 * @param Request $request The Request that was used to reach this controller
		 * @param Route $route The Route that matched the Request
		 * @param array $urlParams The parameters that were matched in the Route
		 */
		public function __construct(Request $request, Route $route, array $urlParams) {

			$this->request = $request;
			$this->route = $route;
			$this->urlParams = $urlParams;

		}


	}


?>