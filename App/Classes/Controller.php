<?php
namespace App\Classes;

use App\Classes\Request;
use App\Classes\View;


class Controller
{
    protected $validActions = ["Index"];
    protected $view;
    protected $request;

    function __construct()
    {
        $this->view = new View();
    }

    function run(Request $request)
    {
        if($request === null)
            throw new InvalidArgumentException("Attempt to run controller without request.");

        $action = $request->popNextParameter();
        $this->request = $request;

        if(in_array($action, $this->validActions)) //its not in this array?!
        {
            call_user_func_array([$this, $action], [$request]);
            return;
        }
        else
        {
            call_user_func_array([$this, $this->validActions[0]], [$request]);//fail softly instead and supply default action.
            return;
        }
    }
    public function Index()
    {

    }
    public function registerAction($action)
    {
        if(($action === null)|in_array($action, $this->validActions))
            throw new InvalidArgumentException("Attempt to register null action on Controller");

        $this->validActions[] = $action;
    }
}
