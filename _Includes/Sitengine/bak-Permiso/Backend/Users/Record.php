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
 * @package    Sitengine_Permiso
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Sitengine/Record.php';


abstract class Sitengine_Permiso_Backend_Users_Record extends Sitengine_Record {
    
    
    protected $_controller = null;
    
    
    public function __construct(
    	Sitengine_Permiso_Backend_Users_Controller $controller
    )
    {
        $this->_controller = $controller;
        parent::__construct($this->_controller->getDatabase());
        $this->_table = $this->_controller->getPermiso()->getUsersTableName();
    }
    
    
    
    protected function _checkModifyException(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (2|\'nickname\')/i', $exception->getMessage())) {
    		$this->_setError('nicknameExists');
            return false;
    	}
    	if(preg_match('/Duplicate entry.*for key (3|\'name\')/i', $exception->getMessage())) {
    		$this->_setError('nameExists');
            return false;
    	}
    	return true;
    }
}
?>