<?php

include_once 'vendor/autoload.php';
include 'Bootstrap/Bootstrap.php';

use App\Classes\SiteController;
use App\Classes\ConsoleController;
use App\Classes\Request;
use App\Classes\Request\ConsoleRequest;

$sapi_type = php_sapi_name();

if (substr($sapi_type, 0, 3) == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
    $request = new ConsoleRequest();
    $app = new ConsoleController();
} else {
    $request = new Request();
    $app = new SiteController();
}
$app->route($request);
exit;
	
	?>




