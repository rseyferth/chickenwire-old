<?php

	namespace ChickenWire\ActiveRecord;

	use ChickenWire\Data\ISerializable;

	class RecordSet implements \ArrayAccess, \Iterator, \Countable, ISerializable {


		protected $parentModel;
		protected $attributeName;

		protected $newRecords;
		protected $deletedRecords;
		protected $records = array();

		protected $position = 0;


		public function setAttributeOf($model, $attributeName) {

			// Localize
			$this->parentModel = $model;
			$this->attributeName = $attributeName;

			// Iterator
			$this->position = 0;

		}


		public function __construct() {

		}


		/**
		 * Iterator implementation
		 */
		public function rewind() {
			$this->position = 0;
		}
		public function current() {
			return $this->records[$this->position];
		}
		public function key() {
			return $this->position;
		}
		public function next() {
			return ++$this->position;
		}
		public function valid() {
			return isset($this->records[$this->position]);
		}


		/**
		 * ArrayAccess implementation
		 */
		public function offsetExists($offset) {
			return isset($this->records[$offset]);
		}

		public function offsetSet($offset, $value) {
			if (is_null($offset)) {
				$this->records[] = $value;
			} else {
				$this->records[$offset] = $value;
			}

		}

		public function offsetUnset($offset) {
			unset($this->records[$offset]);
			$this->flagDirty();
		}

		public function offsetGet($offset) {
			return isset($this->records[$offset]) ? $this->records[$offset] : null;
		}

		/**
		 * Countable implementation
		 */
		public function count() {
			return count($this->records);
		}


		/** ISerializable implementation */
		public function serializeJson(array $options = null) {

			// Format
			$json = array();
			foreach($this->records as $record) {
				$json[] = $record->serializeJson();
			}
			return $json;

		}

		public function serializeXML(array $options = null) {
			throw new \Exception("XML not implemented", 1);
			
		}


	}


?>