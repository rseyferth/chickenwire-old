<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\ActiveRecord\Connection;
	use ChickenWire\Lib\Util\ArrayUtil;

	class SQLExpressions {

		const ParameterMarker = '?';

		private $expressions;
		private $values = array();
		private $connection;

		public function __construct($connection, $expressions = null /* [, $value1, $value2, ... ] */) {

			// Localize
			$this->connection =$connection;			

			// Start empty
			$values = null;

			// Is an array given
			if (is_array($expressions)) {

				// Is there a operator given?
				$glue = func_num_args() > 2 ? func_get_arg(2) : ' AND ';
				list($expressions, $values) = $this->buildSQLFromHash($expressions, $glue);

			}

			// Anything found?
			if (!empty($expressions)) {

				// No values extraces from hash?
				if (!$values) {

					// Use function arguments instead
					$values = array_slice(func_get_args(), 2);

				}

				// Store it
				$this->values = $this->flattenValues($values);
				$this->expressions = $expressions;

			}

		}

		protected function flattenValues($values) {

			// Loop through array
			$newValues = array();
			foreach ($values as $index => $value) {

				$newValues[] = $this->valueToString($value);				
				
			}

			return $newValues;

		}

		protected function valueToString($value) {

			// Is it null?
			if (is_null($value)) {
				return "NULL";
			}

			// Array?
			if (is_array($value)) {
				$processed = array();
				foreach($value as $val) {

					// Another array?
					if (is_array($val)) {

						$processed[] = $this->valueToString($val);

					}
					// String?
					elseif (is_string($val)) {
						$processed[] = $this->connection->Escape($val);
					} else {
						$processed[] = $val;
					}

				}
				return implode(", ", $processed);
			}

		}



		public function getSQL() {

			return $this->expressions;

		}

		public function __toString() {
			return $this->Build();
		}

		public function getValues() {
			return $this->values;
		}




		private function buildSQLFromHash(&$hash, $glue) {

			// Start.
			$sql = '';
			$g = '';

			// Loop through hash
			foreach ($hash as $key => $value) {

				// What type of value?
				if (is_array($value)) {

					// Use an IN(...) query
					$sql .= "$g$key IN(?)";

				} elseif (is_null($value)) {

					// Use IS null
					$sql .= "$g$key IS ?";

				} else {

					// Default query
					$sql .= "$g$key = ?";

				}

				// Now use glue for next one
				$g = $glue;


			}

			// Give back the SQL and an array of values
			return array($sql, array_values($hash));

		}



	}

?>