<?php 

namespace App\Classes;


class SiteController
{
	private $method;
	private $parameters;
	
	function __construct()
	{

	}
	
	function route(Request $request)
	{
		try
        {
            $controller = array_shift($request->parameters);

            $controller = filter_var($controller, FILTER_SANITIZE_STRING);


            if(empty($controller)) $controller = "Login";

            $controller .= "Controller";
            $controller = "App\Controllers\\$controller";

            $controller = new $controller();
            $controller->run($request);

            return;
        } catch (\Exception $e) {

        }

	}
}
