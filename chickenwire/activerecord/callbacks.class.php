<?php

	namespace ChickenWire\ActiveRecord;

	class Callbacks {


		static protected $VALID_CALLBACKS = array( 
			"afterConstruct",
			"beforeSave",
			"afterSave",
			"beforeCreate",
			"afterCreate",
			"beforeUpdate",
			"afterUpdate",
			"beforeValidation",
			"afterValidation",
			"beforeDestroy",
			"afterDestroy"
		);

		protected $class;
		protected $publicMethods;
		protected $registry = array();


		public function __construct(\ReflectionClass $class) {

			// Localize
			$this->class = $class;

			// Look for valid callbacks in the given class			
			foreach (self::$VALID_CALLBACKS as $name) {

				// Static array definitions?
				if (($definition = $this->class->getStaticPropertyValue($name, null))) {

					// Array?
					if (!is_array($definition)) {
						$definition = array($definition);
					}

					// Register all
					foreach ($definition as $methodName) {
						$this->Register($name, $methodName);
					}

				}

				// Simple function definition?
				if ($this->class->hasMethod($name)) {
					$this->Register($name);
				}

			}

		}

		/**
		 * Invoke a callback on the given Model instance
		 * @param Model $model Model instance
		 * @param string $name  Callback name
		 */
		public function Invoke($model, $name) {

			// Check if anything at all registered for this callback
			if (!array_key_exists($name, $this->registry)) {
				return true;
			}

			// Is it a 'before' callback?
			$before = 1 == preg_match('/^before/', $name);
			
			// Loop 'em
			foreach ($this->registry[$name] as $method) {

				// Call it
				$returnValue = $model->$method();

				// Before and false?
				if ($before && $returnValue === false) {

					// Then we won't continue...
					return false;

				}

			}

			// Done
			return true;

		}


		/**
		 * Register a new callback
		 * @param string  $name    The callback type (see $VALID_CALLBACKS)
		 * @param mixed  $method  Method name on the Model
		 * @param boolean $prepend Whether to run this method before all others
		 */
		public function Register($name, $method = null, $prepend = false) {

			// Default method?
			if (is_null($method)) {
				$method = $name;
			}

			// Valid callback?
			if (!in_array($name, self::$VALID_CALLBACKS)) {
				throw new \Exception("'$name' is not a valid callback.", 1);
			}

			// Check public methods
			if (is_null($this->publicMethods)) {
				$this->publicMethods = get_class_methods($this->class->name);
			}

			// Not available
			if (!in_array($method, $this->publicMethods)) {

				// Maybe it's protected or private?
				if ($this->class->hasMethod($method)) {
					throw new \Exception("The method '$method' needs to be public, in order to be used as a callback.", 1);					
				} else {
					throw new \Exception("The method '$method' could not be found on " . $this->class->name, 1);					
				}

			}

			// Check if callback-name is already known in the registry
			if (!array_key_exists($name, $this->registry)) {
				$this->registry[$name] = array();
			}
			
			// Pre- or append?
			if ($prepend) {
				array_unshift($this->registry[$name], $method);
			} else {
				$this->registry[$name][] = $method;
			}



		}



	}


?>