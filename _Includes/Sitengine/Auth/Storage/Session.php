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


require_once 'Zend/Auth/Storage/Session.php';


class Sitengine_Auth_Storage_Session extends Zend_Auth_Storage_Session
{

    public function getData()
    {
        return $this->_session->data;
    }
    
    
    public function writeData($data)
    {
        $this->_session->data = $data;
    }
    
    
    public function getExpires()
    {
        return $this->_session->expires;
    }
    
    
    public function writeExpires($expires)
    {
        $this->_session->expires = $expires;
    }
    
    
    public function getIpAddress()
    {
        return $this->_session->ipAddress;
    }
    
    
    public function writeIpAddress($ipAddress)
    {
        $this->_session->ipAddress = $ipAddress;
    }
    
    
    public function clear()
    {
    	$this->_session->unsetAll();
        #unset($this->_session->{$this->_member});
    }
}

?>