<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\ActiveRecord\SQLExpressions;
	use ChickenWire\ActiveRecord\Connection;
	use ChickenWire\Lib\Util\ArrayUtil;
	use ChickenWire\Lib\Util\StringUtil;

	class SQL {

		protected $connection;
		protected $operation = 'SELECT';
		
		protected $table;
		protected $select = '*';
		protected $joins;
		
		protected $order;
		protected $limit;
		protected $offset;
		protected $group;
		
		protected $having;
		protected $update;

		// Where's
		protected $where;
		protected $where_values = array();

		// Insert/Update
		protected $data;
		

		public function __construct(Connection $connection, $table) {

			// Localize
			$this->connection = $connection;
			$this->table = $table;

		}

		public function __toString() {
			return $this->Build();
		}

		public function Build() {

			// Build operation
			$buildFunction = "build" . StringUtil::titleize($this->operation);
			return $this->$buildFunction();

		}

		protected function buildSelect() {

			// Basics
			$sql = "SELECT $this->select FROM $this->table";

			// Joins

			// Wheres
			if ($this->where) {
				$sql .= " WHERE $this->where";
			}

			// Group
			if ($this->group) {
				$sql .= " GROUP BY $this->group";
			}

			// Having
			if ($this->having) {
				$sql .= " HAVING $this->having";				
			}

			// Order
			if ($this->order) {
				$sql .= " ORDER BY $this->order";
			}

			// Limit/offset?
			if ($this->limit || $this->offset) {
				$sql = $this->connection->LimitQuery($sql, $this->offset, $this->limit);
			}

			// Done
			return $sql;

		}


		protected function buildInsert() {


			// Quote the fieldnames
			$data = $this->quoteFields();

			// Create query strings
			$columns = implode(",", array_keys($data));
			$parameters = implode(",", array_fill(0, count($data), '?'));
			
			// Create expression
			$sql = "INSERT INTO $this->table ($columns) VALUES ($parameters)";
			return $sql;
			
		}



		public function getWhereValues() {
			return $this->where_values;
		}

		public function getInsertValues() {
			return array_values($this->data);
		}
		public function getUpdateValues() {
			return $this->getUpdateValues();
		}



		public function Insert($data) {

			// Store data and set to insert
			$this->data = $data;
			$this->operation = "INSERT";

			// Allow chaining
			return $this;

		}



		public function Where(/* ($conditions, $values) || ($hash) */) {

			// Apply where conditions
			$this->applyWhereConditions(func_get_args());

			// Allow chaining
			return $this;

		}


		public function Order($order) {
			$this->order = $order;
			return $this;
		}

		public function Limit($limit) {
			$this->limit = $limit;
			return $this;
		}

		public function Offset($offset) { 
			$this->offset = $offset;
			return $this;
		}

		public function Group($group) {
			$this->group = $group;
			return $this;
		}

		public function Having($having) {
			$this->having = $having;
			return $this;
		}


		protected function applyWhereConditions($args) {

			// A hash given?
			if (count($args) == 1 && ArrayUtil::IsHash($args[0])) {

				// More than 1 table used? => Use table names in fields
				$hash = $this->quoteFields($args[0], is_null($this->joins));
				
				// Create where expressions
				$clauses = array();
				foreach ($hash as $key => &$value) {

					// Array?
					if (is_array($value)) {
						$clauses[] = $key .= ' IN (?)';
					} else {
						$clauses[] = $key .= ' = ?';
					}

					// Simplify value
					$value = $this->valueToString($value);
					
				}

				// Store it
				$this->where = implode(", ", $clauses);
				$this->where_values = array_values($hash);

			} elseif (count($args) > 0) {

				throw new Exception("Not yet implemented.", 1);
				

			}

		}

		/**
		 * Prepare field names for use in SQL query
		 * @param  array  $hash     Hash of fields and values
		 * @param  boolean $addTable Whethere to prepend the fields with the table name
		 * @return array            The quoted hash
		 */
		protected function quoteFields($hash = null, $addTable = false) {

			// Create new hash
			$newHash = array();

			// Add table name?
			$tableName = $addTable ? $this->connection->EscapeName($this->table) . "." : "";

			// No hash given?
			if (is_null($hash)) {

				// Use data instead
				$hash = $this->data;

			}

			// Loop through fields and quote it
			foreach ($hash as $key => $value) {
				$field = $tableName . $this->connection->EscapeName($key);
				$newHash[$field] = $value;
			}

			return $newHash;

		}

		/**
		 * Value to string conversion (such as array, and null);
		 * @param  [type] $value [description]
		 * @return [type]        [description]
		 */
		protected function valueToString($value) {

			// Is it null?
			if (is_null($value)) {
				return null;
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

			return $value;

		}




			
	}

?>