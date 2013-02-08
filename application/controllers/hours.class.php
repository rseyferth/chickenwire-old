<?php

	namespace WipkipAdmin\Controllers;

	use ChickenWire\Core\Controller;
	use ChickenWire\Request\Format;

	use WipkipAdmin\Models\Hour;
	

	class Hours extends Controller {


		protected static $layout = "main";


		/**
		 * GET /
		 * GET /hours/
		 */
		public function Index() {

			$hours = Hour::Find("last");

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