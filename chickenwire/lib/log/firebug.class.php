<?php

	namespace ChickenWire\Lib\Log;
	
	use ChickenWire\Lib\Log\Log;


	class FireBug extends Log {


		private $fb;

		public function __construct() {

			// Load firebug
			require_once(CHICKENWIRE_DIR . "vendor/FirePHPCore/fb.php");
			$this->fb = \FirePHP::getInstance(true);

		}


		public function Log() {

			call_user_func_array(array($this->fb, "log"), func_get_args());

		}

	}

?>