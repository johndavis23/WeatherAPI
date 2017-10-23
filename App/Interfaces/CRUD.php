<?php
namespace App\Interfaces;
/**
 *
 * @author jdavis
 */
interface CRUD
{
    public function create(array $namevalue) ;
    public function read(array $where);
    public function update(array $namevalue, array$where);
    public function delete(array $where, $limit);
}