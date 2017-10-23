<?php

namespace App\Classes\Request;

use App\Classes\Request;
use App\Util\Util;

class ConsoleRequest extends Request
{
    function __construct()
    {
        $parameters = static::console();
        $this->parameters = $parameters;
    }

    function popNextParameter()
    {
        return array_shift($this->parameters);
    }

    static function console()
    {
        global $argv;
        return $argv ? $argv : $_SERVER['argv'];//$_SERVER['argv'];
    }
}