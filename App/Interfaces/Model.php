<?php
namespace App\Interfaces;
/**
 *
 * @author jdavis
 */
interface ModelInterface
{
    public function create(array $fieldValuePairs);
    public function read(array $where);
    public function update(array $update, array $where);
    public function delete(array $where, $limit = null);
    public function exists(array $where);
 	public function count(array $where);
	public function readOffset(array $where,  $limit = null, $offset = null);
    public function readWithID($id);
    public function readAll();
    public function readAllIds();

}