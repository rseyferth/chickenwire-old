<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\Core\ChickenWire;
	use ChickenWire\Lib\Util\StringUtil;
	use ChickenWire\Lib\Util\ArrayUtil;


	/**
	 * ActiveRecord Table
	 * 
	 * The table class acts as an interface between the Connection and 
	 * a Model. All queries a model needs executed will be done by the Table class.
	 * 
	 * @author Ruben Seyferth
	 */
	class Table {

		private static $cache = array();

		public static function Get($className) {

			// Look in the cache
			if (!array_key_exists($className, static::$cache)) {
				
				// Create it and cache it
				static::$cache[$className] = new Table($className);

			}

			// Give back cached version
			return static::$cache[$className];

		}

		

		public $class;
		public $className;
		
		public $table;
		public $tableName;

		public $connection;
		public $database;

		public $columns;

		public $primaryKeys;


		public $callbacks;


		/**
		 * Create a new Table instance 
		 *
		 * This method should not be used outside the framework. To retrieve/create a
		 * Table instance, use Table::Get($className) instead.
		 * 
		 * @param string $className [description]
		 */
		public function __construct($className) {

			// Get class reflection info
			$this->class = new \ReflectionClass($className);
			$this->className = ArrayUtil::LastItem(explode('\\', $this->class->getName()));

			// Get DB connection
			$this->initConnection();

			// Check table info
			$this->initTableName();
			$this->getTableInfo();

			// Activate callbacks
			$this->callbacks = new Callbacks($this->class);

			// Check if we record timestamps
			if (($recordTimestamps = $this->class->getStaticPropertyValue("recordTimestamps", null)) && $recordTimestamps == true) {

				// Register callbacks
				$this->callbacks->Register("beforeCreate", "recordTimestampsCreate");
				$this->callbacks->Register("beforeSave", "recordTimestampsModify");

			}
			

		}



		protected function optionsToSQL(array $options){

			// Check table (either options['from'] or me-self)
			$table = array_key_exists("from", $options) ? $options['from'] : $this->getFullyQualifiedName();

			// Create new SQL query
			$sql = new SQL($this->connection, $table);

			// Any table joins?
			if (array_key_exists("joins", $options)) {

				// 
				//@TODO Join tables
				throw new Exception("Table JOINS not yet implemented.", 1);
				

			}

			// Select fields?
			if (array_key_exists("select", $options)) {

				// Apply select
				$sql->Select($options['select']);

			}

			// Conditions given?
			if (array_key_exists("conditions", $options)) {

				// Is it a field hash?
				if (ArrayUtil::IsHash($options['conditions'])) {

					// Do a where with the hash
					$sql->Where($options['conditions']);

				} else {

					// Is it a single string?
					if (is_string($options['conditions'])) {

						// Wrap in array
						$options['conditions'] = array($options['conditions']);

					}

					// Do a where with the string(s) as arguments
					call_user_func_array(array($sql, "Where"), $options['conditions']);

				}

			}

			// Order.
			if (array_key_exists("order", $options)) {
				$sql->Order($options['order']);
			}

			// Limit
			if (array_key_exists("limit", $options)) {
				$sql->Limit($options['limit']);
			}

			// Offset
			if (array_key_exists("offset", $options)) {
				$sql->Offset($options['offset']);
			}

			// Grouping
			if (array_key_exists("group", $options)) {
				$sql->Group($options['group']);
			}

			// Having
			if (array_key_exists("having", $options)) {
				$sql->Having($options['having']);
			}


			// Done
			return $sql;

		}


		public function Find(array $options) {

			// Convert options to SQL query
			$sql = $this->optionsToSQL($options);
			
			// Execute
			return $this->FindBySQL(
						$sql->Build(), 
						$sql->getWhereValues());


		}



		public function FindBySQL($sql, $values = null) {

			// Execute the query
			$sth = $this->connection->Query($sql, $values);

			// Loop it
			$list = new RecordSet();
			while ($row = $sth->fetch()) {

				// Create new model instance
				$model = new $this->class->name($row, true, false);

				// Add it
				$list[] = $model;

			}


			// List is done.
			return $list;


		}


		public function Insert(&$attributes) {

			// Create query
			$sql = new SQL($this->connection, $this->getFullyQualifiedName());
			$sql->Insert($this->prepareData($attributes));

			// Execute
			$this->connection->Query($sql->Build(), $sql->getInsertValues());

		}



		/**
		 * Prepare data for Database
		 * @return [type] [description]
		 */
		protected function prepareData($data) {

			// Process values
			foreach ($data as $name => &$value) {
					
				// Lookup column
				$column = $this->columns[$name];
				$value = $column->Prepare($value, $this->connection);

			}

			return $data;


		}






		/**
		 * Find the appropriate DB connection for this model
		 */
		protected function initConnection() {

			// Check connection property on the model
			if ($connection = $this->class->getStaticPropertyValue('connection', null)) {

				// Get that connection
				$this->connection = ChickenWire::DB($connection);

			} else {

				// Get default connection
				$this->connection = ChickenWire::DB();

			}


		}

		/**
		 * Initialize the table name value		 
		 */
		protected function initTableName() {

			// Is there a table property set on the Model itself
			if ($table = $this->class->getStaticPropertyValue('table', null)) {

				// Store it
				$this->table = $table;

			} else {

				// Tableize classname
				$this->table = StringUtil::tableize($this->className);

			}


			// Was there a db-name specified in the model?
			if ($db = $this->class->getStaticPropertyValue('database', null)) {
				$this->database = $db;
			} else {
				$this->database = null;
			}



		}


		protected function getTableInfo() {

			/*$memcache = new \Memcache;
			$memcache->connect('localhost', 11211);*/

			// Get columns from connection
			$this->columns = $this->connection->getColumns($this->getFullyQualifiedName());

			// Store primary keys in seperate array
			$this->primaryKeys = array();
			foreach($this->columns as $index => $column) {
				
				// Store table on column
				$column->table = $this;

				// Primary key?
				if ($column->primaryKey == true) {
					array_push($this->primaryKeys, $column);
				}
			}

		}

		public function __toString() {

			return $this->getFullyQualifiedName();

		}

		public function getFullyQualifiedName($quote = true) {

			// Quote or not?
			$table = $quote ? $this->connection->EscapeName($this->table) : $this->table;

			// Add database name?
			if (!is_null($this->database)) {
				$table = ($quote ? $this->connection->EscapeName($this->database) : $this->database) . "." . $table;
			}

			return $table;


		}


	}

?>