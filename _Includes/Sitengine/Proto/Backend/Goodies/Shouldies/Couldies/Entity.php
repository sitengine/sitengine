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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



abstract class Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_goodyRow = null;
    protected $_shouldyRow = null;
    protected $_row = null;
    
    
    
    public function __construct(Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
        if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('entity not started');
    	}
    	return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('entity not started');
    	}
        foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    
    public function getAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('entity not started');
    	}
        return $this->_shouldyRow->id;
    }
    
    
    
    public function getGreatAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('entity not started');
    	}
        return $this->_goodyRow->id;
    }
    
    
    
    public function getBreadcrumbs()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('entity not started');
    	}
        return array(
        	'goody' => $this->_goodyRow,
        	'shouldy' => $this->_shouldyRow,
        	'couldy' => $this->_row
        );
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $aid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ANCESTORID);
            $gaid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_GREATANCESTORID);
            
            if(!$id) { $this->_row = null; }
            else {
            	$table = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable();
				$select = $table->select()->where('id = ?', $id);
				$row = $table->fetchRow($select);
				if($row !== null) { $this->_row = $row; }
				else  {
					require_once 'Zend/Log.php';
                    require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
                    throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
            }
            
            $table = $this->_controller->getFrontController()->getProtoPackage()->getShouldiesTable();
            $select = $table->select()->where('id = ?', $aid);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_shouldyRow = $row; }
        	else  {
        		require_once 'Zend/Log.php';
                require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
                throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_shouldyRow->toArray())) { return false; }
            
            
            $table = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTable();
            $select = $table->select()->where('id = ?', $gaid);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_goodyRow = $row; }
        	else  {
        		require_once 'Zend/Log.php';
                require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
                throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_goodyRow->toArray())) { return false; }
            return true;
        }
        catch (Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('start entity error', $exception);
        }
    }
    
}
?>