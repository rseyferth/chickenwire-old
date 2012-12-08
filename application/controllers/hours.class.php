<?php

	namespace WipkipAdmin\Controllers;

	use ChickenWire\Core\Controller;
	use ChickenWire\Request\Format;
	

	class Hours extends Controller {


		protected static $layout = "main";


		/**
		 * GET /
		 * GET /hours/
		 */
		public function Index() {

			switch ($this->request->format) {

				case "HTML":
					$this->Render("hours/index");
					break;

				case "JSON":
					$this->Render(array("json" => array("TeSt" => "Jhoh")));
					break;

			}
 
			

		}

	}


?>