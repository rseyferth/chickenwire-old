<?php

	namespace ChickenWire\Lib\HTML;

	use ChickenWire\Lib\Util\ArrayUtil;
	use ChickenWire\Lib\Net\Url;
	use ChickenWire\Lib\Filesystem\Path;
	use ChickenWire\Core\ChickenWire;

	class HTMLBuilder {


		public function Stylesheet($filename, Array $options = null) {

			// No options?
			if (is_null($options)) $options = array();

			// Check filename's extension
			if (pathinfo($filename, PATHINFO_EXTENSION) == "") {
				$filename .= ".css";
			}

			// Add static path and create URL
			if (strstr($filename, "://")) {
				$link = $filename;
			} else {
				$link = Url::Create(Path::Construct(ChickenWire::get("pathCSS")) . $filename);
			}

			// Create the tag
			return $this->SingleTag("link", ArrayUtil::Defaults($options, array(
				"src" => $link,
				"rel" => "stylesheet",
				"type" => "text/css"
			)));

		}


		public function SingleTag($tag, Array $attributes = null) {

			if (is_null($attributes)) $attributes = array();

			$html = "<" . $tag;

			foreach ($attributes as $attr => $value) {
				$html .= " " . $attr . '="' . $value . '"';
			}

			$html .= " />";


			return $html;


		}




	}


?>