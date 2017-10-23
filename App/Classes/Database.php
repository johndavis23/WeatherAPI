<?php

/*
 * Last Updated: June 25, 2016
 * 
 * Singleton Pattern
 * 
 * Provides a database connection with access to prepared queries and string 
 * queries.
 * 
 * Uses our list of credentials for multiple databases and ensures a single connection
 * is alive at a given time.
 * 
 */
namespace App\Classes;

use App\Util\Util;

include('Config/config.php');



//our exceptions
class DataException 	extends \Exception {}
class SQLDataException 	extends DataException{}
	

class Database 
{
    protected static $instances;
	
	//Connection Settings
    protected $server;
    protected $username;
    protected $password;
    protected $name;
	
	//Connection Variables
    protected $databaseConnection;
    protected $results;
    protected $resultCount;
    protected $connected  	= false;
    protected $error; 
    protected $insertId;
	
    
    public static function getDatabase($database_id = "default")
    {
      
        global $DATABASES; 
		//is our database an actual database? No? Use our default.
        if (!isset($DATABASES[$database_id]))
        {
			Util::error_log("Tried to select non-configured database: $database_id. "); 
            $database_id = "default";
        }
		
        //do we have a database connection already? if not, make it
        if (null === static::$instances[$database_id]) 
        {
            static::$instances[$database_id] 		 	= new static();
            static::$instances[$database_id]->server    = $DATABASES[$database_id][0];
            static::$instances[$database_id]->username  = $DATABASES[$database_id][1];
            static::$instances[$database_id]->password  = $DATABASES[$database_id][2];
            static::$instances[$database_id]->name      = $DATABASES[$database_id][3];
            static::$instances[$database_id]->connect();
        }
        
        return static::$instances[$database_id];
    }
    
    protected function __construct(){}
    private function __clone(){}
    
    
    public function beginTransaction()
    {
         $this->databaseConnection->autocommit(FALSE);
    }
	
    public function endTransaction($rollback = false)
    {
        if($rollback)
        {
            $this->databaseConnection->rollback();
            $this->databaseConnection->autocommit(TRUE); 
        }else
        {
            $this->databaseConnection->commit();
            $this->databaseConnection->autocommit(TRUE); 
        }
        
    }
    
    public function query($query, $large = false)
    {
	   	$this->connectIfNotConnected();
		$this->result =  $this->databaseConnection->query($query, $large ? MYSQLI_USE_RESULT : null);   
        if($this->result) 
        {
           return $this->getResults();
        }
        return false;
    }
	
	public function preparedQuery($query, $types, $params)
    {
    	if (! extension_loaded('mysqlnd') )
		{
			throw new DataException("This framework requires mysqlnd.");
		}
		
        $args  = func_get_args();
        $query = array_shift($args);
        $types = array_shift($args);
	
        $this->connectIfNotConnected();

	    if (!$this->databaseConnection) {
            throw new DataException("Database Connection Failed");
        }

        $stmt = $this->databaseConnection->prepare($query);
	
        if($stmt == false)
        {
        	throw new SQLDataException("SQL Statement Error: ".$this->databaseConnection->error." for Query: $query");
        }

        $stmtResult = $this->bindParameters($stmt, $args, $types);

        if (!$stmtResult) {
            throw new DataException("Parameter Binding Failed");
        }

        $stmt->execute();

        if ($stmt == false) {
            throw new DataException("Execute Failed");
        }

        $this->result 	= $stmt->get_result();
        $out 			= $this->getResults();
			
		if(isset($this->databaseConnection->error) | isset($stmt->error))
        $this->error 	= $this->databaseConnection->error . $stmt->error;
        $this->insertId = $stmt->insert_id;
		//$stmt->free();
        //$stmt->close();

        return $out;
        
    }
	
    public function getCount()
    {
		if(isset($this->resultCount))
        	return $this->resultCount;
		return false;
    }
	
	public function getInsertId()
	{
		if(isset($this->insertId))
			return $this->insertId;
		return false;
	}
	
	/*********** Private Functions ***********/
	private function connect()
    {
    	
        $this->databaseConnection = new \mysqli($this->server, $this->username, $this->password, $this->name);
       
        if ($this->databaseConnection->connect_error) 
        {
            $this->connected = false;
        	throw new DataException("Database Connection failed: ". $this->databaseConnection->connect_error);
        }
        else
        {
            if($this->databaseConnection)
            {
                $this->connected = true; 
            }
            else
            {
                $this->connected = false;
				
				throw new DataException("No Database Connection");
            }
        }
    }
    private function disconnect()
    {   
        $this->thread = $mysqli->thread_id;
        $this->result->close();
        $this->databaseConnection->close();
        $this->mysqli->kill($thread);
        $this->connected = false;
    }
    
    
	private function connectIfNotConnected()
	{
		if(! $this->connected)
        {
            $this->connect();
        }
	}
	
	private function bindParameters( $stmt, $args, $types)
	{
        $bind_params[] =   & $types;
        $values = [];
        $i = 0;
		
		//construct parameter array
        foreach($args as $value)
        {
           
            $values[] = $value;
            $bind_params[] = &$values[$i];
            $i++;
        }
        $result = false;
		
        if($types != null)
        {
            $result = call_user_func_array(array($stmt, 'bind_param'), $bind_params);
        }
		else 
		{
			throw new DataException("Type string not supplied for Binding Parameters");
		}
		
        if (!$result) 
        {
        	throw new SQLDataException("Error on MYSQL binding.");
        }
		
        if(strlen($types) != count($args))
        {    
            Util::error_log("Number of parameters does not match number of types in Query.");
			//soft fail. Let the developer know.
        }
		
		return $result;
	}
	
    private function getResults()
	{
		
		$out = [];
		if($this->result)
        {
            while($row = $this->result->fetch_array(MYSQLI_ASSOC)) 
            {
                array_push($out, $row);
            }
			
            return $out;
        }
        elseif(isset($this->error) & !empty($this->error))
        {
        	throw new SQLDataException("Error on retrieving results: (".$this->error.")");
        }
		return $out;
	}
}
