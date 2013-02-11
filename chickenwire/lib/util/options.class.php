<?php

	namespace ChickenWire\Lib\Util;

	use ChickenWire\Exceptions\OptionsException;

	class Options {

		static $OPTIONS_ALLOW = array();
		static $OPTIONS_MANDATORY = array();
		static $OPTIONS_DEFAULT = array();


		protected static function ValidateHash($hash, $allow = null, $mandatory = null, $default = null) {

			// Arrays given?
			if (is_null($allow) || is_null($mandatory) || is_null($default)) {

				// Check extending class for settings
				if (is_null($allow) && isset(static::$OPTIONS_ALLOW)) {
					$allow = static::$OPTIONS_ALLOW;
				}
				if (is_null($mandatory) && isset(static::$OPTIONS_MANDATORY)) {
					$mandatory = static::$OPTIONS_MANDATORY;
				}
				if (is_null($default) && isset(static::$OPTIONS_DEFAULT)) {
					$default = static::$OPTIONS_DEFAULT;
				}

			}

			// Checked if dis-allowed fields are presents
			$optionsKeys = array_keys($hash);
			$disallowedKeys = array_diff($optionsKeys, $allow);
			if (count($disallowedKeys) > 0) {
				throw new OptionsException("One or more disallowed options were given: '" . implode("', '", $disallowedKeys) . "'. The allowed options are: '" . implode("', '", $allow) . "'", 1);				
			}

			// Apply default values
			$defaultKeys = array_keys($default);
			$missingDefaultKeys = array_diff($defaultKeys, $optionsKeys);
			if (count($missingDefaultKeys) > 0) {

				// Loop and set default value
				foreach ($missingDefaultKeys as $key) {
					$hash[$key] = $default[$key];					
				}

			}

			// Check mandatory
			$missingKeys = array_diff($mandatory, $optionsKeys);
			if (count($missingKeys) > 0) {
				throw new OptionsException("One or more mandatory options were missing: '" . implode("', '", $missingKeys) . "'.", 1);				
			}
			

			// Done.
			return $hash;

		}





		protected $hashOptions;


		/**
		 * Create new Options instance
		 * @param array $hash Optional hash for initial values
		 */
		public function __construct($hash = null, $allow = null, $mandatory = null, $default = null) {

			// Hash given?
			if (!is_null($hash)) {

				// Validate and localize
				$this->hashOptions = self::ValidateHash($hash, $allow, $mandatory, $default);

			} else {

				// Start empty
				$this->hashOptions = array();

			}

		}

		public function __get($name) {

			// Look it up
			if (array_key_exists($name, $this->hashOptions)) {
				return $this->hashOptions[$name];
			} else {
				throw new OptionsException("There is no property for '$name'", 1);
				
			}

		}

		public function __set($name, $value) {

			$this->hashOptions[$name] = $value;

		}



	}

?>