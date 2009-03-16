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


class Sitengine_Session_SaveHandler implements Zend_Session_SaveHandler_Interface
{
	
	protected $_database = null;
	protected $_table = 'sessions';
	protected $_lifetime = null;
	
	
	function __construct(Zend_Db_Adapter_Abstract $database, $table = 'sessions')
	{
		$this->_database = $database;
		$this->_table = $table;
		$this->_lifetime = 3600*24*7*2;
	}
	
	
	public function setLifetime($lifetime)
	{
		$this->_lifetime = $lifetime;
	}
	
	
	public function setTable($table)
	{
		$this->_table = $table;
	}
	
	
	public function open($save_path, $name) {}

    
    public function close() {}
    
    
	
	function read($id)
	{
		try {
			$q = 'SELECT data FROM '.$this->_table.' WHERE id = :id';
			$bind = array(':id' => $id);
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			return $statement->fetchColumn();
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Session/Exception.php';
			throw new Sitengine_Session_Exception('session read error', $exception);
		}
	}
	
	
	function write($id, $data)
	{
		try {
			if(isset($this->_database))
			{
				$q  = 'REPLACE '.$this->_table;
				$q .= ' SET id = :id, data = :data';
				
				$bind = array(
					':id' => $id,
					':data' => $data
				);
				$statement = $this->_database->prepare($q);
				$statement->execute($bind);
				return $statement->rowCount();
			}
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Session/Exception.php';
			throw new Sitengine_Session_Exception('session write error', $exception);
		}
	}
	
	
	function destroy($id)
	{
		try {
			$q  = 'DELETE FROM '.$this->_table;
			$q .= ' WHERE id = :id';
			
			$bind = array(':id' => $id);
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			return $statement->rowCount();
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Session/Exception.php';
			throw new Sitengine_Session_Exception('session destroy error', $exception);
		}
	}
	
	
	function gc($lifetime)
	{
		try {
			$q  = 'DELETE FROM '.$this->_table;
			$q .= ' WHERE mdate < DATE_SUB(NOW(), INTERVAL '.$this->_lifetime.' SECOND)';
			
			$statement = $this->_database->prepare($q);
			$statement->execute();
			return $statement->rowCount();
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Session/Exception.php';
			throw new Sitengine_Session_Exception('session gc error', $exception);
		}
	}
	
}


?>