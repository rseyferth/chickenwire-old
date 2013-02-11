<?php

	namespace ChickenWire\Exceptions;

	use ChickenWire\ActiveRecord\Connection;

	class DBException extends \Exception {

		public function __construct($info) {

			
			// Check the info type
			if ($info instanceof Connection) {
				parent::__construct(
					implode(", ", $info->pdo->errorInfo()),
					intval($info->pdo->errorCode())
				);
			} elseif ($info instanceof \PDOStatement) {
				parent::__construct(
					implode(", ", $info->errorInfo()),
					intval($info->errorCode())
				);			
			} else {
				parent::__construct($info);
			}

		}

	}




?>