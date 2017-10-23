<?php

namespace App\Classes;

use App\Classes\Request\ConsoleRequest;
use App\Util\Util;

class ConsoleController
{
    private $method;
    private $parameters;

    function __construct()
    {

    }

    function route()
    {
        try{

            $request = new ConsoleRequest();

            $path = array_shift($request->parameters);
            $controller = array_shift($request->parameters);

            //if(empty($controller)) $controller = "Console";

            $controller .= "Controller";
            $controller = "App\Controllers\\$controller";

            $controller = new $controller();
            $controller->run($request);

            return;
        } catch (\Exception $e) {

        }

    }
}
