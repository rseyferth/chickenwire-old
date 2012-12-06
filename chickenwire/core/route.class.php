<?php

	/**
	 * Examples:
	 *	Route::Add("/kies-bijeenkomst",						array( "controller" => "Application",			"action" => "SelectMeeting"	));
	 *	Route::Add("/kies-bijeenkomst/{meeting}",			array( "controller" => "Application",			"action" => "ConfirmMeeting" ));
	 *
 	 *
 	 * Possible options are:
 	 *	controller			<String>		The controller for this route
 	 *	action 				<String 		The controller-action to use. When no action is given the "Index" action will be used.
 	 *	model 				<String>		The model for this route. This can be used to auto-generate urls for 
 	 *										models, using Route::editPath($modelInstance), Route::newPath(ModelClass), etc.
 	 *	method 				<String>		HTTP method filter (POST or GET). When no method is passed, all methods are accepted
 	 *  protocol			<String>		Server protocol filter ("https" or "http"). By default all protocols are accepted
 	 *	controllerPath		<String>		Local path where the controller class is located. When empty, the default controller path
 	 *										will be used.
 	 *	domainPattern		<String>		A domain matching pattern. By default this is empty, meaning all domains will match.
	 *	formats 			<Array>			The list of accepted formats. See 
 	 * 	
	 */


	class Route {


		private $controller;
		private $action;
		private $model;
		private $method;
		private $controllerPath;
		private $domainPattern;
		private $formats;
		private $protocol;

		public static function Add ($pattern, $options) {



		}


	}

?>