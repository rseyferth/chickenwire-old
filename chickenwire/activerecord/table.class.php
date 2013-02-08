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

			echo($this->table);

			


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



		}


		protected function getTableInfo() {

			$memcache = new \Memcache;
			$memcache->connect('localhost', 11211);


		}


	}

?>