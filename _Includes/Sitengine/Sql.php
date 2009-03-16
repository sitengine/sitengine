<?php

/**
 * Sitengine - An example serving as a pattern
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sitengine
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_Sql
{
    
    /*
    public static function getFieldsAndValuesString(Zend_Db_Adapter_Abstract $database, array $data)
    {
        $vals = '';
        foreach($data as $k => $v) {
            $vals .= (($vals) ? ', ' : ' ').$k.' = '.$database->quote($v);
        }
        return $vals;
    }
    */
    
    public static function getLockQuery(array $tables)
    {
        $t = '';
        foreach($tables as $name => $locktype) {
            $t .= (($t) ? ',' : '').$name.' '.$locktype;
        }
        return 'LOCK TABLES '.$t;
    }
    
    
    
    public static function getFields(array $fields)
    {
        $f = '';
        foreach($fields as $field) {
            $f .= (($f) ? ',' : '').$field;
        }
        return $f;
    }
    
    
    
    public static function getWhereStatement(array $whereClauses, $prependWithWhere = true)
    {
    	$where = '';
		foreach($whereClauses as $clause) {
			$and = ($where) ? ' AND ' : ' ';
			$where .= ($clause) ? $and.$clause : '';
		}
		if($prependWithWhere) { return ($where) ? ' WHERE '.$where : ''; }
		return $where;
    }
    
}


?>