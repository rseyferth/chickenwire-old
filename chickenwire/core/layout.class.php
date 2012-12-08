<?php

	namespace ChickenWire\Core;

	use ChickenWire\Lib\HTML\HTMLBuilder;

	/**
	 * The Layout class is used by the Controller to render a layout file. Inside the layout files
	 * you can use $this for the layout. So `$this->Yield()` will call the Yield function on the Layout class.
	 */
	class Layout {

		protected $_filename;
		protected $_content;

		protected $HTML;

		/**
		 * Create a new Layout instance.
		 * 
		 * @param string $filename The complete filename where the layout can be found.
		 */
		public function __construct($filename) {

			// Localize
			$this->_filename = $filename;

			// Create HTML builder
			$this->HTML = new HTMLBuilder();

		}

		/**
		 * Render the layout with the given content.
		 * 
		 * @param array $content This should be an associative array containing the rendered content. The content will be put in place by the $this->Yield calls.
		 */
		public function Render($content) {

			// Localize
			$this->_content = $content;

			// Start buffering
			ob_start();

			// Include the layout
			include($this->_filename);

			// Done.
			$content = ob_get_contents();
			ob_end_clean();

			// Output
			echo ($content);

		}

		/**
		 * This call will insert the content for the given key, at the place of call.
		 * 
		 * @param string $key The key for the content, as defined in the Render function. By default this is 'main'.
		 */
		protected function Yield($key = 'main') {

			// Content there?
			if (array_key_exists($key, $this->_content)) {
				echo ($this->_content[$key]);
			}

		}



	}


?>