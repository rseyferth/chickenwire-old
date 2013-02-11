<?php


	namespace ChickenWire\ActiveRecord;

	class Column {

		// types for $type
		const STRING	= 1;
		const INTEGER	= 2;
		const DECIMAL	= 3;
		const DATETIME	= 4;
		const DATE		= 5;
		const TIME		= 6;

		/**
		* Map a database column type to a local type.
		* @static
		* @var array
		*/
		static $TYPE_MAPPING = array(
			'datetime'	=> self::DATETIME,
			'timestamp'	=> self::DATETIME,
			'date'		=> self::DATE,
			'time'		=> self::TIME,

			'int'		=> self::INTEGER,
			'integer'	=> self::INTEGER,
			'tinyint'	=> self::INTEGER,
			'smallint'	=> self::INTEGER,
			'mediumint'	=> self::INTEGER,
			'bigint'	=> self::INTEGER,

			'float'		=> self::DECIMAL,
			'double'	=> self::DECIMAL,
			'numeric'	=> self::DECIMAL,
			'decimal'	=> self::DECIMAL,
			'dec'		=> self::DECIMAL);



		public $name;
		public $type;
		public $rawType;
		public $length;
		public $nullable;
		public $primaryKey;
		public $default;
		public $autoIncrement;

		public $connection;
		public $table;


		public function __toString() {

			return $this->connection->EscapeColumn($this);

		}

		/**
		 * Cast given value to the type of the Column.
		 * @param mixed $value The value to cast
		 * @param Connection $connection The DB connection to use for casting
		 * @return mixed The cast value
		 */
		public function Cast($value, $connection) {

			// What type to cast to?
			switch ($this->type) {

				// Cast to string
				case Column::STRING:
					return (string)$value;
					
				// Cast to integer
				case Column::INTEGER:
					return (int)$value;
					
				// Cast to decimal (float/double)
				case Column::DECIMAL:
					return (double)$value;

				// Cast to ActiveDateTime
				case Column::DATETIME:
				case Column::DATE:

					// Null?
					if (is_null($value)) {
						return null;
					}

					// Already an ActiveDateTime
					if ($value instanceof ActiveDateTime) {
						return $value;
					}

					// A regular PHP DateTime?
					if ($value instanceof \DateTime) {
						return new ActiveDateTime($value->format('Y-m-d H:i:s T'));
					}

					// Just a string perhaps..?
					return $connection->stringToDateTime($value);

				
				default:
					throw new Exception("Unknown column type", 1);
					break;
			}

			return $value;


		}


		/**
		 * Prepare value for use in a SQL query
		 * @param mixed $value      The value to prepare for use in a query
		 * @param Connection $connection The connection the SQL query will be executed on
		 * @return mixed Database ready value
		 */
		public function Prepare($value, $connection) {

			// What type to cast to?
			switch ($this->type) {

				// Cast to string
				case Column::STRING:
					return (string)$value;
					
				// Cast to integer
				case Column::INTEGER:
					return (int)$value;
					
				// Cast to decimal (float/double)
				case Column::DECIMAL:
					return (double)$value;

				// Cast to ActiveDateTime
				case Column::DATETIME:

					// Null?
					if (is_null($value)) {
						return null;
					}

					// An ActiveDateTime
					if ($value instanceof ActiveDateTime || $value instanceof \DateTime) {
						return $connection->dateTimeToString($value);
					}

					// Just use the string
					return $value;
					break;

				case Column::DATE:

					// Null?
					if (is_null($value)) {
						return null;
					}

					// An ActiveDateTime
					if ($value instanceof ActiveDateTime || $value instanceof \DateTime) {
						return $connection->dateToString($value);
					}

					// Just use the string
					return $value;
					break;


				
				default:
					throw new Exception("Unknown column type", 1);					
					break;
			}

			

			return $value;

		}



		public static function mapRawType($rawType) {

			$rawType = ($index = strpos($rawType, '(')) ? substr($rawType, 0, $index) : $rawType;
			if (array_key_exists($rawType, self::$TYPE_MAPPING)) {
				return self::$TYPE_MAPPING[$rawType];	
			} else {
				return self::STRING;
			}
			
		}



	}



?>