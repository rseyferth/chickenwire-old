<?php

	namespace ChickenWire\ActiveRecord;

	class ActiveDateTime extends \DateTime {

		public static $DEFAULT_FORMAT = "rfc2822";

		public static $FORMATS = array(
				'db' => 'Y-m-d H:i:s',
				'number' => 'YmdHis',
				'time' => 'H:i',
				'short' => 'd M H:i',
				'long' => 'F d, Y H:i',
				'atom' => \DateTime::ATOM,
				'cookie' => \DateTime::COOKIE,
				'iso8601' => \DateTime::ISO8601,
				'rfc822' => \DateTime::RFC822,
				'rfc850' => \DateTime::RFC850,
				'rfc1036' => \DateTime::RFC1036,
				'rfc1123' => \DateTime::RFC1123,
				'rfc2822' => \DateTime::RFC2822,
				'rfc3339' => \DateTime::RFC3339,
				'rss' => \DateTime::RSS,
				'w3c' => \DateTime::W3C);

		private $model;
		private $attributeName;

		public function setAttributeOf($model, $attributeName) {

			// Localize
			$this->model = $model;
			$this->attributeName = $attributeName;

		}

		/**
		 * Format the DateTime to the specified format
		 * @param  string $format A format string
		 * @return string Formatted date string
		 */
		public function Format($format = null) {
			return parent::format(self::getFormat($format));
		}


		public static function getFormat($format = null) {

			// No format?
			if (is_null($format)) {

				// Use default
				$format = self::$DEFAULT_FORMAT;

			}

			// Friendly name?
			if (array_key_exists($format, self::$FORMATS)) {

				// Convert to real format
				return self::$FORMATS[$format];

			}

			// It's already a format
			return $format;

		}

		public function __toString() {
			return $this->Format();			
		}

		/**
		 * Track my dirtiness in the model I'm in
		 * @return void
		 */
		private function flagDirty() {

			// Do I know my model?
			if ($this->model) {
				$this->model->flagDirty($this->attributeName);
			}

		}

		public function setDate($year, $month, $day) {
			$this->flagDirty();
			call_user_func_array(array($this, 'parent::setDate'), func_get_args());
		}
		public function setISODate($year, $week, $day = null) {
			$this->flagDirty();
			call_user_func_array(array($this, 'parent::setISODate'), func_get_args());	
		}
		public function setTime($hour, $minute, $second = null) {
			$this->flagDirty();
			call_user_func_array(array($this, 'parent::setTime'), func_get_args());	
		}
		public function setTimestamp($timestamp) {
			$this->flagDirty();
			call_user_func_array(array($this, 'parent::setTimestamp'), func_get_args());	
		}



	}

?>