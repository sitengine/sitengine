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


require_once 'Zend/Auth/Adapter/DbTable.php';


class Sitengine_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
	
	
	
	public function authenticate()
    {
    	$result = parent::authenticate();
    	
    	if($result->isValid() && !$this->_resultRow['enabled'])
        {
			$this->_authenticateResultInfo = array(
				'identity' => $this->_identity,
				'code' => Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
				'messages' => array('Supplied credential is invalid.')
			);
			return $this->_authenticateCreateAuthResult();
        }
        
        return $result;
    }
    
    
    public function setIdentity($value)
    {
        $this->_identity = mb_strtolower($value);
        return $this;
    }
    
    
	
	public function update($id, array $data)
    {
		return $this->_zendDb->update($this->_tableName, $data, "id = '$id'");
    }
    
    
    
    public function reauthenticate($id)
    {
    	$select = $this->_zendDb->select();
        $select
        	->from($this->_tableName)
            ->where('enabled = ?', 1)
            ->where('id = ?', $id)
        ;
        
        $this->_zendDb->setFetchMode(Zend_DB::FETCH_ASSOC);
        $this->_resultRow = $this->_zendDb->fetchRow($select->__toString());
        
        if($this->_resultRow === false)
        {
        	$this->_authenticateResultInfo = array(
				'identity' => '',
				'code' => Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
				'messages' => array('A record with the supplied id could not be found.')
			);
			return $this->_authenticateCreateAuthResult();
        }
        
        $this->_authenticateResultInfo = array(
			'identity' => $this->_resultRow['name'],
			'code' => Zend_Auth_Result::SUCCESS,
			'messages' => array('Authentication successful.')
		);
		return $this->_authenticateCreateAuthResult();
    }
    
    
    /*
    protected function _authenticateCreateAuthResult()
    {
    	require_once 'Sitengine/Auth/Result.php';
        return new Sitengine_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
        );
    }
    */
}

?>