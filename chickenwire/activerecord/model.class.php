<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\Lib\Util\ArrayUtil;

	/**
	 * ActiveRecord Model class
	 * 
	 * The ActiveRecord Model is the basic class to use when using models in
	 * your application.
	 *
	 * @author Ruben Seyferth
	 */
	class Model {

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
		 * Get the Table for this model
		 * @return Table The Table instance for this Model class
		 */
		public static function getTable() {

			return Table::Get(get_called_class());

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
						$single = false;
						break;

					case Model::LAST:

						// Inverse the default order, and use 'first'
						if (array_key_exists('order', $options)) {

							// Reverse the order
							$options['order'] = SQL::ReverseOrder($options['order']);

						} else {

							// Sort descending on table's primary key(s)
							$options['order'] = explode(' DESC, ', static::getTable()->primaryKeys) . ' DESC';

						}

						// Continue with first...


					case Model::FIRST:					
						$options['limit'] = 1;
						$options['offset'] = 0;
						break;
					
					
				}

			}


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