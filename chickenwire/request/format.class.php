<?php

	namespace ChickenWire\Request;

	use ChickenWire\Lib\Util\ArrayUtil;

	class Format {

		public $format;

		public $extensions;
		public $contentTypes;
		public $preference;

		private static $_formats = array();
		private static $_initialized = false;


		/**
		 * Create a new Format instance
		 */
		public function __construct($format = "", $extensions = null, $contentTypes = null, $preference = 1) {

			$this->format = $format;
			$this->extensions = $extensions;
			$this->contentTypes = $contentTypes;
			$this->preference = $preference;

		}

		public static function Initialize() {

			// Already done?
			if (self::$_initialized == true) return;

			// Create the formats
			self::$_formats = array();

				// HTML
				self::$_formats['HTML'] = new Format(
							"HTML",
							array("html", "htm"),
							array("text/html", "application/xhtml+xml"));

				// XML
				self::$_formats['XML'] = new Format(
							"XML",
							array("xml"),
							array("text/xml", "application/xml"));

				// JSON
				self::$_formats['JSON'] = new Format(
							"JSON",
							array("json"),
							array("application/json"));


			// Done
			self::$_initialized = true;

		}

		public static function __callStatic($name, $arguments) {

			// Initialize
			self::Initialize();

			// Format name?
			if (array_key_exists(strtoupper($name), self::$_formats)) {
				return self::$_formats[strtoupper($name)];
			}
			return null;

		}

		public static function FromHTTPAcceptHeader() {

			// Get the header
			$accept = $_SERVER['HTTP_ACCEPT'];
			
			// Split on ,
			$types = explode(",", $accept);
			
			// Loop through types
			$foundFormatNames = array();
			$foundFormats = array();
			foreach ($types as $type) {

				// Create format from it
				$format = Format::FromContentType($type);

				// Not yet found?
				if (!is_null($format) && !in_array($format->format, $foundFormatNames)) {
					array_push($foundFormats, $format);
					array_push($foundFormatNames, $format->format);
				}

			}

			// Sort on preference
			ArrayUtil::SortOn($foundFormats, "preference", false);
			
			// Done.
			return $foundFormats;


		}

		public static function FromContentType($contentType) {

			// Split on ; to find preference
			$parts = explode(";", $contentType);
			if (count($parts) == 1) {

				// No preference, so it's 1
				$preference = 1.0;
				$contentType = $parts[0];

			} else {

				// Use q=X;
				$p = explode("=", $parts[1]);
				$preference = floatval($p[1]);
				$contentType = $parts[0];

			}

			// Do a switch on the ctype
			$format = null;
			foreach (self::$_formats as $f) {
				if (in_array($contentType, $f->contentTypes)) {
					$format = $f;
					break;
				}
			}

			// Anything found?
			if (!is_null($format)) {
				$format->preference = $preference;	
			}			

			return $format;
			

		}

		public static function FromExtension($extension) {

			// Initialize
			self::Initialize();

			// Loop formats
			foreach (self::$_formats as $format) {
				if (in_array($extension, $format->extensions)) {
					return $format;
				}
			}
			return null;

		}

		public function __toString() {
			return $this->format;
		}







	}

?>