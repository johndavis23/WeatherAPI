<?php
ini_set("log_errors", 1);
ini_set("error_log", "error_log_platform");

//use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
include_once('Config/config.php');
//load environment variables
$dotenv = new Dotenv\Dotenv(__DIR__.'/../environment/');
$dotenv->load();