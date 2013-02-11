<?php

	namespace ChickenWire\ActiveRecord\Adapters;

	use ChickenWire\ActiveRecord\Connection;
	use ChickenWire\ActiveRecord\Column;

	class Mysql extends Connection {
		
		static $OPTIONS_DEFAULT = array(
			"port" => 3306,
			"encoding" => "UTF8",
			"driver" => array()
		);
		

		const ESCAPE_CHAR = "`";

		public function __construct($options) {

			// Create me
			parent::__construct($options);

			// Set encoding
			$this->Query("SET NAMES '" . $this->encoding . "'");

		}



		public function getColumns($tableName) {

			// Query DB
			$columns = array();
			$sth = $this->Query("SHOW COLUMNS FROM $tableName");
			while ($columnInfo = $sth->fetch()) {
				
				// Create column
				$column = new Column();

				// Store connection
				$column->connection = $this;
				
				// Set basic values
				$column->name = $columnInfo['Field'];
				$column->rawType = $columnInfo['Type'];
				$column->default = $columnInfo['Default'];

				// Booleans
				$column->nullable = $columnInfo['Null'] == "YES";
				$column->primaryKey = $columnInfo['Key'] == "PRI";
				$column->autoIncrement = $columnInfo['Extra'] == "auto_increment";

				// Check length
				$column->length = ($index = strpos($columnInfo['Type'], '(')) ? intval(substr($columnInfo['Type'], $index + 1, -1)) : null;

				// Map type
				$column->type = Column::mapRawType($column->rawType);

				// Add it
				$columns[$column->name] = $column;

			}


			return $columns;

		}


		public function Escape($s) {

			return $this->pdo->quote($s);

		}


		public function EscapeColumn(Column $column) {

			$s = $this->EscapeName($column->name);

			// Add table
			$s = $column->table . "." . $s;

			return $s;

		}

		public function EscapeName($name) {

			// Already escaped?
			if ($name[0] == static::ESCAPE_CHAR && $name[strlen($name) - 1] == static::ESCAPE_CHAR) {
				return $name;	
			} 

			// Add
			return static::ESCAPE_CHAR . $name . static::ESCAPE_CHAR;

		}



		public function LimitQuery($sql, $offset, $limit) {

			$offset = is_null($offset) ? "" : intval($offset) . ",";
			$sql = $sql . " LIMIT " . $offset . intval($limit);
			return $sql;

		}



	}
	



?>