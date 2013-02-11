<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\Lib\Util\ArrayUtil;
	use ChickenWire\Data\ISerializable;

	/**
	 * ActiveRecord Model class
	 * 
	 * The ActiveRecord Model is the basic class to use when using models in
	 * your application.
	 *
	 * @author Ruben Seyferth
	 */
	class Model implements ISerializable {

		const ALL = 'all';
		const LAST = 'last';
		const FIRST = 'first';

		/**
		 * The name of the connection for this Model to use. When not set specifically, 
		 * the default ChickenWire database will be used.
		 * 
		 * @var string 
		 */	
		static $connection;


		/**
		 * The name of the Model's table in the database. By default this will be 'guessed'.
		 * @var string
		 */
		static $tableName;

		/**
		 * Whether to record timestamps
		 * @var boolean
		 */
		static $recordTimestamps = true;
		static $recordTimestampsFields = array('created_at', 'modified_at');


		/**
		 * Array of attributes that are protected from being mass-assigned. This array will only 
		 * be used when there items in it. 
		 *
		 * This is the opposite of attrAccessible
		 * 
		 * @var array
		 */
		static $attrProtected = array("created_at", "modified_at");

		/**
		 * Array of attributes that can be mass-assigned. This array will only be used
		 * when there are items in it.
		 *
		 * This is the opposite of attrProtected
		 * 
		 * @var array
		 */
		static $attrAccessible = array();


		/**
		 * Collection of Model's attribute values
		 * @var array
		 */
		protected $attributes = array();

		/**
		 * 'Dirty' attribute names, meaning the attributes that changed after creation/load
		 * @var array
		 */
		protected $dirtyAttributes = array();


		/**
		 * Is this a new record, or an existing one..?
		 * @var boolean
		 */
		protected $newRecord = true;


		/**
		 * Create new Model instance
		 * @param array   $attributes     Optional array containing record values as hash
		 * @param bool 	  $newRecord      Whether this is a new record. When true the default values will be set.
		 */
		public function __construct(array $attributes = array(), $newRecord = true) {

			// Localize
			$this->newRecord = $newRecord;

			// New record?
			if ($newRecord == true) {

				// Set default values
				$columns = static::getTable()->columns;
				foreach ($columns as $name => $column) {

					// Auto increment?
					if ($column->autoIncrement) {

						// Don't set a value then...
						continue;

					}

					// Set it
					$this->attributes[$name] = $column->default;

				}

			}

			// Mass assign attributes (without dirtying record)
			if (count($attributes) > 0) {
				$this->massAssign($attributes, false);
			}

		}


		/**
		 * Assign multiple attributes at once
		 * @param  array  $attributes         Hash containing attributes
		 * @param  boolean $trackDirty         Keep track of changes in the record
		 * @param  boolean $overrideProtection Ignore attrAccessible and attrProtected
		 * @return void
		 */
		protected function massAssign($attributes, $trackDirty = true, $overrideProtection = false) {

			// Not a hash?
			if (!ArrayUtil::isHash($attributes)) {
				throw new Exception("The attributes have to be formatted as a hash array.", 1);				
			}

			// Loop attributes
			foreach ($attributes as $name => $value) {

				// Check protection?
				if (!$overrideProtection) {

					// Attribute not accessible? 
					if (count(static::$attrAccessible) > 0 && !in_array($name, static::$attrAccessible)) {
						continue;
					}

					// Attribute protected?
					if (in_array($name, static::$attrProtected)) {
						continue;
					}


				}

				// Assign value
				$this->assign($name, $value, $trackDirty);

			}


		}


		/**
		 * Find Column instance for the given attribute
		 * @param  string $attributeName Name of the attribute to find the Column for
		 * @return Column                The Column instance, or null when none was found
		 */
		protected function getColumn($attributeName) {

			// Check in my table definition
			$table = static::getTable();
			if (array_key_exists($attributeName, $table->columns)) {
				return $table->columns[$attributeName];
			}


			// None found
			return null;

		}

		/**
		 * Set the value of an attribute
		 * @param  string  $name      
		 * @param  mixed  $value      
		 * @param  boolean $trackDirty Keep track of this change
		 * @return void
		 */
		protected function assign($name, $value, $trackDirty = true) {


			//@TODO Find set_attr functions

			// Find column for this attribute
			if ($column = $this->getColumn($name)) {

				// Cast value
				$value = $column->Cast($value, static::getConnection());

				// Apply value
				$this->attributes[$name] = $value;

				// Track dirty?
				if ($trackDirty) $this->flagDirty($name);
				return;

			}

			//@TODO Find relationships


			throw new \Exception("There is no attribute '$name' on '" . get_called_class() . "'", 1);
			
		}

		/**
		 * Read attribute value
		 * @param string $name Attribute name
		 */
		protected function &readAttribute($name) {

			//@TODO Find get_attr functions
			
			// Find in my attributes
			if ($column = $this->getColumn($name)) {

				if (array_key_exists($name, $this->attributes)) {
					return $this->attributes[$name];
				} else {
					return null;
				}
				
			}

			//@TODO Find relationships


			throw new \Exception("There is no attribute '$name' on '" . get_called_class() . "'", 1);

		}


		/**
		 * Track changes for given attribute name
		 * @param  string $attributeName The atrribute to flag as dirty
		 * @return void
		 */
		public function flagDirty($attributeName) {

			// Set to dirty
			if (!in_array($attributeName, $this->dirtyAttributes)) {
				$this->dirtyAttributes[] = $attributeName;
			}

		}

		/**
		 * Get hash of attributes that have been flagged as dirty
		 * @return array Hash of dirty attributes
		 */
		private function getDirtyAttributes() {

			// None?
			if (count($this->dirtyAttributes) == 0) return null;

			// Insersect dirty with my attributes
			$dirty = array();
			foreach ($this->dirtyAttributes as $name) {
				$dirty[$name] = $this->attributes[$name];
			}
			return count($dirty) == 0 ? null : $dirty;

		}


		/**
		 * Attribute GETTER
		 */
		public function &__get($name) {

			// Read attribute
			return $this->readAttribute($name);

		}


		/**
		 * Attribute SETTER
		 * @param string $name  Attribute name
		 * @param mixed $value 
		 */
		public function __set($name, $value) {

			// Try to assign
			$this->assign($name, $value);

		}


		/**
		 * Commit Model to database
		 * @param boolean $validate Whether to validate the record before committing it
		 */	
		public function Save($validate = true) {

			// Insert or update?
			return $this->newRecord	? $this->saveCreate($validate) : $this->saveUpdate($validate);

		}

		/**
		 * Insert the Model into the database
		 * @param  boolean $validate Whether to validate the record before inserting it
		 * @return [type]            [description]
		 */
		private function saveCreate($validate = true) {

			//@TODO Check validations

			// Invoke callbacks
			if (false == $this->invokeCallback("beforeCreate") || false == $this->invokeCallback("beforeSave")) {
				return;
			}

			// Get changed attributes
			$attributes = $this->attributes;
			$table = static::getTable();

			// Do the insert
			$table->Insert($attributes);

			// Get ID
			foreach ($table->primaryKeys as $key) {

				if ($key->autoIncrement) {

					// Get insert ID
					$this->attributes[$key->name] = $table->connection->pdo->lastInsertId();
					break;

				}

			}

			// Done.
			$this->invokeCallback("afterCreate");
			$this->invokeCallback("afterSave");

		}



		private function invokeCallback($name) {
			return static::getTable()->callbacks->Invoke($this, $name);
		}


		public function recordTimestampsCreate() {
			
			// Assign value
			$this->attributes[static::$recordTimestampsFields[0]] = new ActiveDateTime();

		}

		public function recordTimestampsModify() {
			
			// Assign value
			$this->attributes[static::$recordTimestampsFields[1]] = new ActiveDateTime();

		}


		public function serializeJSON(array $options = null) {

			//@TODO Do serialize options.

			return $this->attributes;

		}

		public function serializeXML(array $options = null) {

			return "<error>serializeXML not implemented</error>";

		}




		/**
		 * Get the Table for this model
		 * @return Table The Table instance for this Model class
		 */
		public static function getTable() {

			return Table::Get(get_called_class());

		}


		public static function getConnection() {

			return static::getTable()->connection;

		}





		/**
		 * Create a new record and save it
		 * @param hash $attributes Attributes hash
		 */
		public static function Create(array $attributes = null) {

			// Create new record
			$recordClass = get_called_class();
			$record = new $recordClass($attributes, true);

			// Save it
			$record->Save();

			// Done :)
			return $record;

		}




		/**
		 * All is an alias for Find('all', ...)
		 */
		public static function All() {
			return call_user_func_array('static::Find', array_merge(array('all'), func_get_args()));
		}


		/**
		 * Valid options to use in a find command
		 * @var array
		 */
		static $VALID_OPTIONS = array('conditions', 'limit', 'offset', 'order', 'select', 'joins', 'include', 'readonly', 'group', 'from', 'having');

		/**
		 * Find model records in the database
		 *
		 * Finding by primary key
		 *
		 * <code>
		 * # Find record with id=123
		 * ModelName::Find(123);
		 *
		 * # Find records with id in (1,2,3)
		 * ModelName::Find(1, 2, 3);
		 *
		 * # Find records with options
		 * ModelName::Find(1, 2, array('order' => 'name DESC'));
		 * </code>
		 *
		 * Finding by conditions array
		 * 
		 * <code>
		 * ModelName::Find(array('conditions' => array('name=?', 'John'), 'order' => ' id DESC');
		 * ModelName::Find('first', array('conditions' => array('age > ?', 100));
		 * ModelName::Find('all', array('conditions' => array('age in (?)', array(60, 70, 80)), 'order' => ' id DESC');
		 * </code>
		 * 
		 * Finding by hash
		 *
		 * <code>
		 * ModelName::Find(array('name' => 'John', 'gender' => 'm'));
		 * ModelName::Find('first', array('name' => 'John', 'gender' => 'm'));
		 * ModelName::Find('all', array('name' => 'John', 'gender' => 'm'));
		 * ModelName::Find('last', array('name' => 'John', 'gender' => 'm'), array('order' => 'id DESC'));
		 * </code>
		 *
		 * An options array can take the following parameters:
		 *
		 * <ul>
		 * <li><b>select:</b> A SQL fragment for what fields to return such as: '*', 'people.*', 'first_name, last_name, id'</li>
		 * <li><b>joins:</b> A SQL join fragment such as: 'JOIN roles ON(roles.user_id=user.id)' or a named association on the model</li>
		 * <li><b>include:</b> TODO not implemented yet</li>
		 * <li><b>conditions:</b> A SQL fragment such as: 'id=1', array('id=1'), array('name=? and id=?','Tito',1), array('name IN(?)', array('Tito','Bob')),
		 * array('name' => 'Tito', 'id' => 1)</li>
		 * <li><b>limit:</b> Number of records to limit the query to</li>
		 * <li><b>offset:</b> The row offset to return results from for the query</li>
		 * <li><b>order:</b> A SQL fragment for order such as: 'name asc', 'name asc, id desc'</li>
		 * <li><b>readonly:</b> Return all the models in readonly mode</li>
		 * <li><b>group:</b> A SQL group by fragment</li>
		 * </ul>
		 *
		 * 
		 */
		public static function Find() {

			// Get arguments
			$args = func_get_args();
			if (count($args) == 0) $args = array('all');
			$options = static::extractAndValidateOptions($args);
			$num_args = count($args);

			// Default find is a single record.
			$singleRecord = true;

			// Check if 'all', 'first' or 'last' was given
			if ($args[0] == Model::ALL || $args[0] == Model::LAST || $args[0] == Model::FIRST) {

				// Which was it?
				switch ($args[0]) {
					case Model::ALL:

						// We'll return all records
						$singleRecord = false;
						break;

					case Model::LAST:

						// Inverse the default order, and use 'first'
						if (array_key_exists('order', $options)) {

							// Reverse the order
							$options['order'] = SQL::ReverseOrder($options['order']);

						} else {

							// Sort descending on table's primary key(s)
							$options['order'] = implode(' DESC, ', static::getTable()->primaryKeys) . ' DESC';

						}

						// Continue with first...


					case Model::FIRST:					
						$options['limit'] = 1;
						$options['offset'] = 0;
						break;

				}

				// Remove that argument
				array_shift($args);
				$num_args--;

			// Only one argument
			} elseif (count($args) == 1) {

				// Strip array around it
				$args = $args[0];

			}

			// Any arguments left?
			if ($num_args > 0 && !array_key_exists("conditions", $options)) {

				// Do a Find by Primary Key with the argument that's left
				return static::FindByPrimaryKey($args, $options);

			}

			// Find from table
			$list = static::getTable()->Find($options);

			// One record or recordlist?
			return $singleRecord ? (!empty($list) ? $list[0] : null) : $list;

		}


		/**
		 * Find records on their primary key
		 * @param array $values  Array containing on or more values for the primary key
		 * @param array $options Options array (See Model::Find)
		 * @return Model
		 */
		public static function FindByPrimaryKey($values, $options) {

			// Set conditions
			$options['conditions'] = array(
				static::getTable()->primaryKeys[0]->name => $values
			);

			// Do a find
			$list = static::getTable()->Find($options);

			// One record?
			return (count($values) == 1) ? $list[0] : $list;


		}


		/**
		 * Look for a search options hash in the given arguments array; return it and
		 * remove it from the array.
		 * 
		 * @param  array  &$args The array to search in for the options hash
		 * @return array  The validated options hash (empty when no hash was found)
		 */
		protected static function extractAndValidateOptions(array &$args) {

			// Check last argument
			$options = array();
			if (is_array($args)) {

				// Last element
				$last = $args[count($args) - 1];
				try {
					if (self::isOptionsHash($last)) {

						// Use it!	
						array_pop($args);
						$options = $last;

					}
				} catch (ActiveRecordException $e) {

					// There was an error in the hash! Was it a hash at all?
					if (!ArrayUtil::IsHash($last)) {
						throw $e;
					}

					// Set hash as conditions hash
					$options = array('conditions' => $last);
					
				}

			}

			// Done.
			return $options;

		}

		/**
		 * Check whether given array was a search options hash
		 * @param  array  $array  The hash to validate
		 * @return boolean        True is valid, otherwise false
		 */
		protected static function isOptionsHash($array) {

			// Hash at all?
			if (ArrayUtil::IsHash($array)) {

				// Check difference with valid-options array
				$keys = array_keys($array);
				$diff = array_diff($keys, self::$VALID_OPTIONS);

				// Difference found?
				if (!empty($diff)) {
					throw new ActiveRecordException("Unkown key(s): " . explode(', ', $diff));					
				}

				// Is there a resemblance then? (Not an empty array, or something..?)
				$intersect = array_intersect($keys, self::$VALID_OPTIONS);
				if (!empty($intersect)) {
					return true;
				}


			}
			return false;

		}



	}

?>