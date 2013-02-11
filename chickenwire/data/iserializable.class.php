<?php

	namespace ChickenWire\Data;

	interface ISerializable {

		public function serializeJSON(Array $options = null);
		public function serializeXML(Array $options = null);

	}

?>