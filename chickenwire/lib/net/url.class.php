<?php

	namespace ChickenWire\Lib\Net;

	use ChickenWire\Core\ChickenWire;

	class Url {


		public static function Create($link) {

			// Is it already a complete url?
			if (strstr($link, "://")) {
				return $link;
			}

			// Include domain?
			if (ChickenWire::get("useAbsoluteUrls") == true) {

				// Prefix with url
				return (BASE_URL . $link);

			} else {

				// Prefix with path
				return (BASE_PATH . $link);

			}

		}

	}

?>