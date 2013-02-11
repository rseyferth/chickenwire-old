<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\Core\ChickenWire;
	use ChickenWire\Lib\Util\Options;

	use ChickenWire\ActiveRecord\Column;

	use ChickenWire\Exceptions\DBException;

	use \PDO;
	use \PDOException;

	abstract class Connection {

		static $OPTIONS_ALLOW = array("protocol", "user", "pass", "host", "port", "db", "encoding", "driver");
		static $OPTIONS_MANDATORY = array("protocol", "host", "user", "pass");
		static $OPTIONS_DEFAULT = array(
			"encoding" => "UTF8",
			"driver" => array()
		);

		static $PDO_DRIVER_OPTIONS = array(
			PDO::ATTR_CASE => PDO::CASE_LOWER,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false
		);


		/**
		 * Create a new ActiveRecord database connection
		 *
		 * From URI:
		 *
		 * <code>
		 * Connection::Create('[protocol]://[user]:[pass]@[host]:[port]/[dbname]?[extra-option1]=[extra-value1]&[extra-option2]=[extra-value2]');
		 * Connection::Create('mysql://user:pass@localhost:3109/mydatabase?encoding=UTF8');
		 * </code>
		 *
		 *
		 * From options hash:
		 * <code>
		 * Connection::Create(array(
		 * 		"protocol" => "mysql",
		 * 		"user" => "username",
		 * 		"pass" => "password",
		 * 		"host" => "localhost",
		 * 		"port" => 3109,
		 * 		"encoding" => "UTF8"
		 * ));
		 * </code>
		 * 
		 */
		public static function Create($options) {

			// Is the argument a string or a hash?
			if (is_string($options)) {

				// Parse URI
				$options = static::parseDBUri($options);

			}

			// Check if protocol is given
			if (!array_key_exists("protocol", $options)) {
				throw new Exception("You need to provide a database adapter in your configuration.", 1);				
			}

			// Check the protocol in adapters
			$fullAdapterClassName = "ChickenWire\\ActiveRecord\\Adapters\\" . $options['protocol'];

			// Try to create it
			try {
				
				// New connection
				$connection = new $fullAdapterClassName($options);
				return $connection;


			} catch (Exception $e) {
				
				throw $e;
				

			}

		}


		protected static function parseDBUri($uri) {  

			// Do a preg_match_all!
			preg_match_all('/^([^:]+):\/\/(([^:@]+):([^@]+))@?([^:\/]+)(:([0-9]+))?(\/([^?\/]*))?(\?(.*))?$/', $uri, $matches);

			// No matches?
			if (count($matches[1]) == 0) {
				throw new \Exception("Invalid DB Uri: " . $uri, 1);
				
			}

			// Create options object out of it
			$options = array(
				"protocol" => $matches[1][0],
				"user" => $matches[3][0],
				"pass" => $matches[4][0],
				"host" => $matches[5][0],
				"port" => $matches[7][0],
				"db" => $matches[9][0]
			);

			// Extra options?
			if (!empty($matches[11][0])) {

				// Split it!
				$pairs = explode("&", $matches[11][0]);
				foreach ($pairs as $pair) {
					list($key, $value) = explode("=", $pair);
					if (!empty($key) && !empty($value)) {
						$options[$key] = $value;
					}
				}

			}

			// Done!
			return $options;

		}



		protected $info;
		public $encoding;
		public $pdo;

		public $lastQuery;
		

		public function __construct($options) {

			// Validate options
			$this->info = new Options($options, static::$OPTIONS_ALLOW, static::$OPTIONS_MANDATORY, static::$OPTIONS_DEFAULT);

			// Merge driver options
			$driverOptions = array_merge($this->info->driver, static::$PDO_DRIVER_OPTIONS);
			
			// Create PDO
			try {
				
				// A file unix socket?
				if ($this->info->host[0] == '/') {

					// Use unix socket
					$host = "unix_socket=" . $this->info->host;

				} else {

					// Use network host
					$host = "host=" . $this->info->host;
					if (!is_null($this->info->port) && !empty($this->info->port)) {
						$host .= ";port=" . $this->info->port;
					}

				}
				

				// Create the connection
				$connString = sprintf('%s:%s;dbname=%s', $this->info->protocol, $host, $this->info->db);
				ChickenWire::Log($connString, "PDO Connect");
				$this->pdo = new PDO($connString, $this->info->user, $this->info->pass, $driverOptions);

				// Remove password... so var_dumping won't show too much secret info
				$this->info->pass = "[obfuscated]";

			} catch (PDOException $e) {
				
				throw $e;
				
			}		

			// Store encoding
			$this->encoding = $this->info->encoding;


		}

		/**
		 * Create new ActiveDateTime from string, using the connection's settings
		 * @param  string $str String representation of a datetime
		 * @return ActiveDateTime      ActiveDateTime instance
		 */
		public function stringToDateTime($str) {

			// Empty date?
			if (empty($str) || $str == '0000-00-00 00:00:00') {
				return null;
			}
			

			// Create date
			$date = date_create($str);
			
			// Any errors?
			$errors = date_get_last_errors();
			if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
				$messages = array_merge($errors['warnings'], $errors['errors']);
				throw new \Exception("Could not convert '$str' to DateTime: " . implode('. ', $messages), 1);				
				return null;
			}

			// Create activeDT
			return new ActiveDateTime($date->format('Y-m-d H:i:s T'));

		}

		/**
		 * Convert (Active)DateTime to string that the database understands
		 * @param  DateTime $dateTime The DateTime to convert
		 * @return string           The converted date
		 */
		public function dateTimeToString($dateTime) {

			// Type right?
			if (!$dateTime instanceof \DateTime) {
				return null;
			}

			// Format
			return $dateTime->format(ActiveDateTime::getFormat('db'));

		}



		/**
		 * Execute a raw SQL query on the database
		 * @param string $sql    SQL query string
		 * @param array  $values Optional values array to bind in query
		 * @return mixed A result set object
		 */
		public function Query($sql, &$values = array()) {

			// Log it
			if (count($values) > 0) {
				ChickenWire::Log($sql . " ==> " . implode(", ", $values), "SQL Query");
			} else {
				ChickenWire::Log($sql, "SQL Query");
			}

			// Store query
			$this->lastQuery = $sql;

			// Try to prepare the query
			try {
				if (!($sth = $this->pdo->prepare($sql))) {
					throw new DBException($this);					
				}
			} catch (PDOException $e) {
				throw new DBException($this);				
			}
			
			// Set fetch mode
			$sth->setFetchMode(PDO::FETCH_ASSOC);

			// Execute query
			try {
				if (!($sth->execute($values))) {
					throw new DBException($sth);					
				}	
			} catch (PDOException $e) {
				throw new DBException($e);
				
			}
			
			return $sth;

		}
		
		/**
		 * Get the columns for given table
		 * @param  string $tableName The database name of the table
		 * @return array            An array of Column objects
		 */
		abstract public function getColumns($tableName);



		/**
		 * Get the fully qualified escaped column name for given Column instance
		 * @param Column $column Column instance to escape
		 * @return string Fully qualified and escaped column name to use in query
		 */
		abstract public function EscapeColumn(Column $column);



		abstract public function LimitQuery($sql, $offset, $limit);



	}

?>