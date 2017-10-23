<?php
namespace App\Classes;

/*
 *
 * John Davis
 * Open Source
 */

use App\Util\Util;
use App\Interfaces\CRUD;

class Model implements CRUD
{
    protected   $db;
    protected   $id_field;
    protected   $table;
    protected   $fields;

    const FIRST = 0;


    function __construct( $table,  $idField = "id",  $database = "default")
    {
        $this->id_field = $idField;
        $this->table    = $table;
        $this->db       = Database::getDatabase($database);

        if (empty($fields)) {
            $this->getTableColumns($table);
        }
    }

    public function create(array $fieldValuePairs)
    {
        $payload       = $this->getQueryPayloads($fieldValuePairs);
        $questionArray = array_fill(0, count($payload['parameters']), "?");

        $query = "INSERT INTO $this->table (".join(", ", $payload['columns']).")".
            " VALUES (".join(", ", $questionArray).")";

        $results       = $this->callPreparedQueryWithPayload($query, $payload);
        return $this->db->getInsertId();
    }

    public function where(array $where)
    {
        if (empty($where)) {
            return $this->readAll();
        }

        $payload = $this->getQueryPayloads($where);
        $query   = "SELECT * FROM $this->table WHERE "
            .join("= ? AND ", $payload['columns'] ).' = ?';
        $results = $this->callPreparedQueryWithPayload($query, $payload);
        return $results;
    }

    public function update(array $update, array $where, $order_by = null, $limit = null, $offset = null,  $active = true)
    {

        //date in case sql has now and current stamp disabled
        //$update = array_merge($update, ['updated' => date("Y-m-d H:i:s")]);

        //get the relevent arrays for our prepared query
        $payload      = $this->getQueryPayloads($update);


        $wherePayload  = $this->getQueryPayloads($where);
        $limit_clause  = $this->updateParametersForLimit($limit, $wherePayload);
        $offset_clause = $this->updateParametersForOffset($offset, $payload);

        $order   = $this->getOrderByClause($order_by);
        if (empty($order)) $order = "";

        $query = "UPDATE $this->table SET "
            .join(" = ?, ", $payload['columns'])
            ." = ? WHERE ".join(" = ? AND ", $wherePayload['columns'])." = ? "
            ." $order $limit_clause $offset_clause";


        $payload['types'] .= $wherePayload['types'];

        $payload['parameters']
            = array_merge(
            $payload['parameters'],
            $wherePayload['parameters']
        );

      
        $results = $this->callPreparedQueryWithPayload($query, $payload);

        return $results;
    }

    public function delete(array $where, $limit = false)
    {
        $payload = $this->getQueryPayloads($where);

        $limit = $limit ?  "LIMIT 1" : '';


        $query = "DELETE FROM $this->table WHERE "
            .join(" = ?, ", $payload['columns'])." = ? $limit";


        $results = $this->callPreparedQueryWithPayload($query, $payload);
    }

    public function exists(array $where)
    {
        $payload = $this->getQueryPayloads($where);


        $where   = " WHERE ".join(" = ? AND ", $payload['columns'])." = ?";
        $query   = "SELECT EXISTS( SELECT 1 FROM $this->table $where )";

        $results = $this->callPreparedQueryWithPayload($query, $payload);

        $first_value = reset($results[0]);

        return $first_value > 0;

    }
    /**
     * Returns the entries that have matching ids.
     *
     * @return array
     */
    public function readWithID($id)
    {
        //if its an array of ids, do an in query
        if (is_array($id))
        {
            $query   = "SELECT * FROM $this->table WHERE ".$this->id_field." IN ( ".join(", ", $id ).')';
            $results = $this->callPreparedQueryWithPayload($query, $payload);
            return $results;
        }
        return $this->read([$this->id_field => $id])[0];
    }
    public function count(array $where)
    {
        $payload = $this->getQueryPayloads($where);
        $query   = "SELECT count(*) AS total FROM $this->table ".
            (count($where) > 0 ? ' WHERE ' : '')
            .join("= ? AND ", $payload['columns'])
            .(count($where) > 0 ? ' = ?' : '');

        if (count($where)) {
            $results = $this->callPreparedQueryWithPayload($query, $payload);

        } else {
            $results = $this->db->query($query);
        }
        if (array_key_exists('total', $results)) {
            return $results['total'];
        }
        return $results[0]['total'];
    }

    public function read(array $where, $order_by = null, $limit = null, $offset = null,  $active = true)
    {


        $payload = $this->getQueryPayloads($where);


        $where_clause  = $this->getWhereClause($where, $payload);
        $limit_clause  = $this->updateParametersForLimit($limit, $payload);
        $offset_clause = $this->updateParametersForOffset($offset, $payload);

        $order_clause   = $this->getOrderByClause($order_by);
        /*
        $order   = $this->getOrderByClause($order_by);


        $limit   = $this->updateParametersForLimit($limit, $payload);
        $offset  = $this->updateParametersForOffset($offset, $payload); */

        $query   = "SELECT * FROM $this->table  $where_clause  $order_clause  $limit_clause  $offset_clause";

        $results = $this->callPreparedQueryWithPayload($query, $payload);

        return $results;
    }

    public function readAll()
    {
        $query = "SELECT * FROM $this->table";
        $rows  = $this->db->query($query);
        return $rows;
    }

    public function timestampById($ids) {


    }

    private function callPreparedQueryWithPayload($query, array $payload)
    {
        try
        {
            $bind_params
                = array_merge([$query, $payload['types']], $payload['parameters']);
            $results
                = call_user_func_array([$this->db, 'preparedQuery'], $bind_params);
            return $results;
        }
        catch(DataException $e)
        {
            echo $e->getMessage();
            Util::error_log($e->getMessage());
            return false;
        }
    }




    /**
     * Gets and Sets the fields of the table for use in preparing a type string for prepared queries
     * @param $table string A string that matches the name of your table
     *
     * @return array Format is: [[[Field] => id, [Type] => int(7)] , ... ]
     */
    public function getTableColumns($table)
    {
        if (! $this->db ) {
            throw new DataException('Database Not Initialized');
        }

        $r = $this->db->query("SHOW COLUMNS FROM $table", false);

        if (!$r) {
            throw new DataException(
                "Could not retrieve table structure."
            );
        }

        $fields = [];
        foreach ($r as $column) {
            $fields[$column["Field"]] = $column;
        }

        $this->fields = $fields;

        return $fields;
    }



    /**
     * Gets the type charcter for use in the prepared query type string
     * @param $fieldname A string that matches the name of the field
     *
     * @return string "i", "d" "s", or "b"
     */
    private function determineFieldType($fieldname)
    {
        $type = strtolower($fieldname);

        //TODO: if size > max_allowed_packet return "b"
        //TODO: Use arrays for each type and in_array

        if (strpos($type, "int") !== false) {
            return 'i';
        } elseif (strpos($type, "double")    !== false
            || strpos($type, "float")  !== false
            || strpos($type, "real")   !== false
            || strpos($type, "precision")!== false
        ) {
            return 'd';
        } else {
            return 's';
        }
    }


    /**
     * Generates and Returns all needed data to generate a prepared query
     * @param $update A name value pair array ["FieldName"=>"Value", "FieldName2","Value2", ...]
     *
     * @return array ["types"=>$types, "columns"=>$queryColumns,"parameters"=>$queryParameters ]
     */
    private function getQueryPayloads(array $update)
    {
        $types           = "";
        $queryParameters = [];
        $queryColumns    = [];

        //for each known valid field
        foreach ($this->fields as $field) {

            // see if there is a matching field in update
            // is $field['Field']
            // in the keys for update?

            if (array_key_exists($field['Field'], $update)) {
                $types            .= $this->determineFieldType($field['Type']);
                $queryColumns[]    = $field['Field'];
                $queryParameters[] = $update[$field['Field']];
                //remove good stuff from update so we know what's left
                unset($update[$field['Field']]);
            }
        }
        //the bad stuff.
        //if any are still set, they aren't in our field list. Notify the developer.
        if (count($update)>0) {
            foreach ($update as $key => $pair) {
                Util::error_log("Invalid Field Supplied To Prepared Query: $key as $pair.");
            }
        }

        return [
            "types"    =>$types,
            "columns"   =>$queryColumns,
            "parameters"=>$queryParameters
        ];

    }

    /**
     * @param $limit
     * @param $payload
     * @return array
     */
    protected function updateParametersForLimit($limit, &$payload)
    {
//manually add the limit parameters and types for prepared query
        if (!is_null($limit) & is_int($limit)) {
            $payload['parameters'][] = $limit;
            $limit = " LIMIT ? ";
            $payload['types'] .= "i";
            return $limit;
        } else {
            $limit = "";
            return $limit;
        }
    }

    /**
     * @param $offset
     * @param $payload
     * @return array
     */
    protected function updateParametersForOffset($offset, &$payload)
    {
//manually add the offset parameters and types for prepared query
        if (!is_null($offset) & is_int($offset)) {
            $payload['parameters'][] = $offset;
            $offset = " OFFSET ? ";
            $payload['types'] .= "i";
            return $offset;
        } else {
            $offset = "";
            return $offset;
        }
    }

    /**
     * @param $order_by
     * @return string
     */
    protected function getOrderByClause($order_by)
    {
        $order = '';

        if (is_array($order_by) & !empty($order_by)) {


            foreach ($order_by as  $key => $item) {
                if ($item != "ASC" || $item != "DESC" || $item != "asc" || $item != "desc") {
                    continue;
                }
                if ($key) {
                    //for each known valid field
                    foreach ($this->fields as $field) {
                        if (array_key_exists($field['Field'], $key)) {
                            if (empty($order))
                                $order = "ORDER BY ";
                            $order .= $key. ' ' . $item;
                        }
                    }
                }
            }

            return $order;
        }

        if (!empty($order_by)) {
            $order = "ORDER BY " . $order_by;
            return $order;
        } else {
            $order = "";
            return $order;
        }
    }

    /**
     * @param array $where
     * @param $payload
     * @return array|string
     */
    protected function getWhereClause(array $where, $payload)
    {
        if (!empty($where)) {
            $where = " WHERE " . join("= ? AND ", $payload['columns']) . " = ? ";
            return $where;
        }
        return $where;
    }


}