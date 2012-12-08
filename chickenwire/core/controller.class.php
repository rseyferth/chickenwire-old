<?php

	namespace ChickenWire\Core;

	use ChickenWire\Lib\Util\ArrayUtil;
	use ChickenWire\Request\Request;




	class Controller {

		protected $HTML;


		protected $route;
		protected $urlParams;
		protected $request;

		protected $renderedContent;

		protected $_currentContentKey;


		/**
		 * Create a new Controller a instance.
		 * 
		 * @param Request $request The Request that was used to reach this controller
		 * @param Route $route The Route that matched the Request
		 * @param array $urlParams The parameters that were matched in the Route
		 */
		public function __construct(Request $request, Route $route, array $urlParams) {

			// Localize
			$this->request = $request;
			$this->route = $route;
			$this->urlParams = $urlParams;

			// Set up HTML builder
			$this->HTML = new \ChickenWire\Lib\HTML\HTMLBuilder();

		}



		/**
		 * Render a response to send to the browser
		 * 
		 * ## Render view
		 * @param string $view The view you wish to render, e.g. "news/index"
		 * @param array $options (Optional) An associative array containing rendering options. See below for more information.
		 * 
		 * *Calling the function in this way will in fact set $view as $options['view'].*
		 * 
		 * ## Custom rendering
		 * @param array $options An associative containing the rendering options
		 * 
		 * ### Render target
		 * Use one of the following options to set the content to render. You cannot use more than one of these in one `Render` call.
		 * 
		 * option 			| type 							| example 						| description
		 * ---------------- | ----------------------------- | ----------------------------- | -----------------------------
		 * view 			| string 						| `"news/index"`				| The reference to the view you wish to render. Usually `[path]/[file-without-extension]`. The controller will determine which output format to use by looking at the request (either specific format through request, or HTTP accept headers). If you add an extension (e.g. `news/index.html`) to the view, this behavior will be skipped.
		 * partial 			| string 						| `"news/menu"`					| The reference to a partial you wish to render. This is the same a rendering a view, except in that it will render without a layout (unless you manually specify a layout in the `Render` call). Partial files usually begin with an `_`, (e.g.: `news/_menu.html.php`).
		 * json  			| string / IJsonObject / array	| `$newsModelInstance`			| When you pass an object that implements IJsonObject (such as Model), its AsJson function will be called and used as output for rendering. You can also pass an associative array, or a string containing already parsed Json. Finally, you can also use a view to render the Json; just pass the view location, as you would normally do when rendering a view.
		 * xml 				| string / IXMLObject / array 	| `$newsModelInstance`			| Render given object/view as XML. This works the same way as `json`.
		 * text 			| string 						| `"OK"`						| Render the given string as plain text.
		 * html 			| string 						| `""
		 * 
		 * 
		 * ### Rendering options
		 * 
		 * option 			| type 					| example 						| description
		 * ---------------- | --------------------- | ----------------------------- | -----------------------------
		 * layout			| string / boolean		| `"newsletter"`				| Layout to render. If you don't pass a layout, it will look in the Controller for a default layout, and finally for a globally set default layout. If you set `layout` to `false`, it will render without a layout.
		 * flash 			| array 				| `array("notice" => "abc")`	| The flash messages you wish the page to know about. This array will be available as `$this->flash` in your views.
		 * content-type		| string 				| `"application/rss"`			| You can use this to override the default MIME content-type associated with the data you render. 
		 * http-status		| int 					| `403`							| The HTTP status code to send to the browser. By default this is 200 (Success). 
		 *
		 */
		protected function Render() {

			// Get arguments
			$args = func_get_args();
			if (count($args) == 0) {
				throw new Exception("Render cannot be called without at least 1 argument.", 1);				
			} elseif (count($args) >= 1 && is_string($args[0])) {
				
				// Render a view. Now add that to the options array, if there is one
				$options = array("view" => $args[0]);
				if (count($args) > 1) {
					$options = ArrayUtil::Defaults($options, $args[1]);
				}

			} else {

				// Interpret the first argument as an options array
				$options = $args[0];

			}

			// Set content type
			header("Content-type: " . $this->request->format->contentTypes[0]);

			// Instantiate rendered content array
			$this->renderedContent = array("main" => "");

			// Start buffering
			$this->_currentContentKey = "main";
			ob_start();

			// Render object
			$renderType = $this->renderContent($options);

			// Buffering done
			$rendered = ob_get_contents();
			ob_end_clean();

			// Layout defined (we only render layouts for views...)?
			if ($renderType == "view" && (!array_key_exists("layout", $options) || $options['layout'] == "")) {

				// Get the default layout
				$options['layout'] = $this->getDefaultLayout();

			}

			// Any layout?
			if (array_key_exists("layout", $options) && $options['layout'] !== false) {

				// Store it
				$this->renderedContent['main'] .= $rendered;

				// Look for the layout file
				$layoutFilename = $this->lookForFile("layouts/" . $options['layout']);
				if ($layoutFilename === false) {
					throw new \Exception("Layout could not be found for " . $options['layout'], 1);					
				}
				
				// Create layout
				$layout = new Layout($layoutFilename);
				$layout->Render($this->renderedContent);
			
			} else {

				// Just output rendered content	
				echo ($rendered);

			}



		}


		/**
		 * Render the actual content
		 * 
		 * @private
		 * @param array $options Options as given to the `Render` function
		 * @return string The options property that was used for rendering (renderType)
		 */
		private function renderContent($options) {

			// Is a view given?
			if (array_key_exists("view", $options)) {

				// Render a view
				$this->renderView($options);

				// Done.
				return "view";

			}

			// Json?
			if (array_key_exists("json", $options)) {

				// Is it an object?
				$json = $options['json'];
				if (is_object($json)) {

					// As json?
					if (is_subclass_of($json, "ChickenWire\Data\IJsonObject")) {

						// Call the asjson function
						echo (json_encode($json->AsJson()));

					} else {

						// Not possible
						throw new \Exception("Object passed to render as Json was invalid.", 1);

					}

				} elseif (is_array($json)) {

					// Then just output the array
					echo (json_encode($json));

				} elseif (is_string($json)) {

					// Just output as is
					echo ($json);

				} else {

					// Not valid
					throw new \Exception("Object passed to render as Json was invalid.", 1);
					

				}


				// Done.
				return "json";

			}


		}

		/**
		 * Render a view
		 * 
		 * @param array $options The options as passed to the Render function.
		 */
		private function renderView($options) {

			// Find view filename
			$viewFilename = $this->lookForFile('views/' . $options['view'], $php);

			// Anything?
			if ($viewFilename === false) {
				throw new \Exception("No view can be found for '" . $options['view'] . "', for format '" . $this->request->format . "'", 1);				
			}

			// Is it PHP or static?
			if ($php == true) {

				// Include the view
				include($viewFilename);

			} else {

				// Just read the contents
				$contents = file_get_contents($viewFilename);
				echo ($contents);

			}


		}


		/**
		 * Start a block of content that is for a specific part of the page (like "head", or "navigation")
		 * 
		 * @param string $key The key for the content. This key can be used later in your layout, for Yield. E.g.: $this->Yield("head")
		 */
		protected function BeginContentFor($key) {

			// Check current content already rendered
			if ($key == $this->_currentContentKey) return;
			$buffered = ob_get_contents();
			if ($buffered !== false) {

				// Add it
				$this->renderedContent[$this->_currentContentKey] .= $buffered;				

				// Stop buffering
				ob_end_clean();

			}

			// Switch content, and start buffering
			$this->_currentContentKey = $key;
			ob_start();

		}

		/**
		 * End block of content. The content after this call will be used for the main block.
		 */
		protected function EndContentFor() {

			// Return to main content
			$this->beginContentFor("main");

		}




		/**
		 * Get default layout for this Controller
		 * 
		 * First this will check if a default layout is set in the Controller.
		 * If not, it will look in ChickenWire for the defaultLayout property.
		 * When no layout was found, it will return `false`.
		 * 
		 * @return string/boolean The name of the layout, or `false` when no layout was found.
		 */
		private function getDefaultLayout() {

			// Get the controller class currently in use
			$name = get_called_class();
			$class = new \ReflectionClass($name);

			// Get its static properties
			$properties = $class->getStaticProperties();

			// Is layout in there?
			if (array_key_exists("layout", $properties)) {

				// Use this
				return $properties['layout'];


			} else {

				// Look for default layout in ChickenWire
				$layout = ChickenWire::get("defaultLayout");
				if ($layout == null) {
					return false;
				}
				return $layout;

			}

		}

		/**
		 * Use an abstract location to find an actual file
		 * @param string $file Abstract filename, as used in views, layout, etc. E.g.: "views/news/index", or "layout/main", or "views/news/index.txt"
		 * @param boolean &$php If you pass a variable, this will be set to true, when the extension is a PHP extension, and should be parsed.
		 * @return string The file location, or false when not found
		 */
		private function lookForFile($file, &$php = null) {

			//TODO: The Request should have a FORMAT that it requests, based on the extension in the URL or the HTTP-accept header. This should be linked to an extension of a file to look for (.html, .txt, .json, etc.)

			// Split up in parts on /
			$dirparts = explode("/", $file);

			// Check filename for extensions
			$filename = array_pop($dirparts);

			// Loop through the request format's extensions
			$extensions = $this->request->format->extensions;
			foreach ($extensions as $extension) {

				// No extension at all?
				$fileparts = explode(".", $filename); 
				if (count($fileparts) == 1) {

					// Add format's extensions
					array_push($fileparts, $extension);

				}

				// Look for file now
				$completeFilename = APPLICATION_DIR . implode($dirparts, "/") . "/" . implode($fileparts, ".");
				if (file_exists($completeFilename)) {

					// PHP?
					$php = (ArrayUtil::LastItem($fileparts) == ChickenWire::get("phpExtension"));

					// Found it!
					return $completeFilename;

				}

				// Should we look for the PHP variant?
				if (ArrayUtil::LastItem($fileparts) != ChickenWire::get("phpExtension")) {

					// Add php too
					array_push($fileparts, ChickenWire::get("phpExtension"));
					$completeFilename = APPLICATION_DIR . implode($dirparts, "/") . "/" . implode($fileparts, ".");
					if (file_exists($completeFilename)) {

						// PHP!
						$php = true;

						// Found it!
						return $completeFilename;

					}

				}

			}



			// Nothing :(
			return false;

		}


	}


?>