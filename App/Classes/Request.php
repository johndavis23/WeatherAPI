<?php

namespace App\Classes;


class Request
{
	public $method;
	public $parameters;
	
	function __construct()
	{
		$this->method     = $_SERVER['REQUEST_METHOD'];
		$this->parameters = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
	}

	function popNextParameter() 
	{
		return array_shift($this->parameters);
	}

	static function current() {
        return new self();
    }

	static function post($name = null) {
        if ($name === null) {
            return $_POST;
        }
        return $_POST[$name];
    }

	static function uri() {
        $uri = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        return $uri;
    }

    static function console()
    {
        global $argv;
        return $argv;
    }
}