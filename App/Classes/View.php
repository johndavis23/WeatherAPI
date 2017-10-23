<?php

namespace App\Classes;

use App\Util\UrlUtils;

class ViewException extends \Exception{}

class View
{
    protected $js ='';
	function __construct()
	{
		
	}
    function registerJS($js)
    {
        $this->js .=$js;
    }
    function printJS()
    {
        echo "<script>";
        echo $this->js;
        echo "</script>";
    }
	function redirect($url)
	{
		 header("Location: $url");
	}

	function redirectController($controller)
	{
		$url  = UrlUtils::getControllerUrl( $controller);
		header("Location: $url");
	}

	function renderBlock($view, array $data, $return = false)
    {
        //get modules for block
        
        //foreach module, render
    }

	function render( $view, array $data, $return = false)
	{
		if(file_exists("Views/$view.php"))
		{
			//create variables for each data element passed to us
			foreach($data as $key=>$value)
			{
				$$key = $value;
              //  $view = this;
			}

			if($return)
                ob_start();

			include "Views/$view.php";

            if($return)
                return ob_get_contents();
		}
		else
		{
			throw new ViewException("View Does Not Exist");
		}	

	}
	
}
