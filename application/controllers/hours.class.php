<?php

	namespace WipkipAdmin\Controllers;

	use ChickenWire\Core\Controller;
	use ChickenWire\Request\Format;

	use WipkipAdmin\Models\Hour;

	use ChickenWire\ActiveRecord\ActiveDateTime;
	

	class Hours extends Controller {


		protected static $layout = "main";


		/**
		 * GET /
		 * GET /hours/
		 */
		public function Index() {


			$hour = Hour::Find(11);
			$hour->Delete();
			var_dump($hour);


			$this->hours = Hour::All();

			switch ($this->request->format) {

				case "HTML":
					$this->Render("hours/index");
					break;

				case "JSON":
					$this->Render(array("json" => $this->hours));
					break;

			}
 
			

		}

	}


?>