# Barebones-MVC
A barebones MVC Framework

#Requirements
PHP 5.4
mysql, mySQLi, mysqlnd

#Setup

Coming Soon

#Description
This is a simple boilerplate MVC. Included are examples of a simple API and a simple login. Neither are full featured and are merely intended to demonstrate the framework.


#Using Models
Below is a brief introduction through examples of the datalayer. Ideally you would derive a custom model class instead like we did with the UserModel. Here you can perform custom queries outside of the usual CRUD operations in the ModelInterface.

Model classes will streamline data operations by making use of automatic prepared query generation. This has the added benefit of free sanitization, leaving just validation to the application developer.

Here, for the example, we create an ad-hoc Model:
```php
	include_once("Classes/Model.php");
	
	$table    = "Users";
	$id       = "uID";
	
	$databaseModel = new Model($table, $id);
```

Creating entries is easy. Simply pass in an array of key value pairs where each key is a field name and each pair is a value.
```php	
	//Create
	$createId  = $databaseModel->create(["data_field"=>"Some Value","data_field_2"=>1]);
```

Read them back is equally easy. Here we pass in a where clause checking for a data field to equal a particular value
```php
	//Read
	$entries  = $databaseModel->read(["data_field"=>"Some Value"]);
	
	foreach($entries as $entry)
	{
		foreach($entry as $field -> $value)
		{
			echo "$field: $value";
		}
	}
```

Update is slightly more complex requiring two key-value arrays, one for the values to update and one for the where clause.
```php
	//Update
	$update     = ["data_field_2"=>"3"]; //update data_field_2 to 3
	$where      = ["id_field"=>$createId]; //where the id_field is $createId
	$databaseModel->update($update, $where);
```

And delete, again, just a key-value array for the where clause.
```php
	//Delete
	$databaseModel($where);
	
	//Count
	$count      = $databaseModel->count($where);
```

Additionally, 'normal' prepared queries are able to be implemented within Models using $this->db>preparedQuery(...) and plain queries with $this->db->query($query). This allows for more fine grained detail on data operations while still encapsulating the SQL and data logic within the Model. It does risk coupling your application to SQL though, which is not the case when using the CRUD and ModelInterface functions.


More information about the model class and its functions is available here: http://heartsleeve.net/datalayer/docs/


#Using Views

Views are implemented within the Controller. You will render them from a controller using a syntax similar to:
```php
$this->view->render($path, $data);
```
$path will tell the framework where to look within the directory /Views/ to find the appropriate template ie Views/Users/List.php would be called using:
```php
$path = “Users/List”;  
```
$data is an array of variables you wish to set. If you have, for example, 
```php
$data = [“data_element_1”=>1, “data_element_2”=>2];
```
your view will look something like this:
```php
<html>Element 1: <?= $data_element_1 ?> Element 2: <?= $data_element_2 ?></html>
```

Additionally, you can redirect or 404.
```php
$this->view->redirectController("Login");
$this->view->404();
```

#Using Controllers

A simple controller example is provided with the login controller. Set $validActions to the end points for various actions.

```php
<?php
/*
 * A barebones login controller.
 * 
 * index.php/Login/Index or index.php/Login/ 	Supplies a form for login and registration 
 * If logged in instead supplies a list of users
 * index.php/Login/Register 					Processes the register form
 * index.php/Login/Logout 						Logs the user out
 * index.php/Login/Login 						Processes a login form
 * 
 */


class LoginController extends Controller
{
	protected $login;
	protected $validActions = ["Index","Login", "Register","Logout"];
	
	function __construct()
	{
		$this->view     = new View();
		$this->login    = new Login();
	}
	
	//Our Pages
	function Index()
	{
		if($this->login->loggedIn())
		{
			$um = new UserModel();
			$users = $um->read([]); 
			//or $um->readAll();
			//You can use something like this if you'd like to page these results: 
			//$users = $um->readOffset([],  15, 0);

			$this->view->render("Users/List", ["users"=>$users]);
			
			return;
		}
		else 
		{
			//display forms instead
			$this->view->render("Forms/Login/Login",[]);
			$this->view->render("Forms/Login/Register",[]);
			return;
		}
	}
	
	function Login()
	{
		$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
		$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
		if($this->login->login($username, $password))
		{
			$_SESSION['username'] = $username;
			$this->view->redirectController("Login");
		}
		else 
		{
			$this->login->logout();
			$this->view->redirectController("Login");
		}
		
	}
	
	function Logout()
	{
		$this->login->logout();
		$this->view->redirectController("Login");
	}
	
	function Register()
	{
		
		$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
		$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
		$email 	  = filter_var($_POST['email'],    FILTER_SANITIZE_EMAIL);
		
		$um = new UserModel();
		$um->registerUser($username, $password, $email);
		
		$this->view->redirectController("Login");
	}
	
	
}
```