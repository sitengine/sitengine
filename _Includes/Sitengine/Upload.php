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



require_once 'Sitengine/Exception.php';



class Sitengine_Upload
{
    
    
    protected $_name = '';
    
    
    public function Sitengine_Upload($name)
    {
        $this->_name = $name;
    }
    
    
    
    public function isFile()
    {
        if(isset($_FILES[$this->_name])) {
            return is_uploaded_file($_FILES[$this->_name]['tmp_name']);
        }
        return false;
    }
    
    
    
    public function getMime()
    {
        return $_FILES[$this->_name]['type'];
    }
    
    
    
    public function getSize() 
    {
        return $_FILES[$this->_name]['size'];
    }
    
    
    
    public function getError()
    {
        return $_FILES[$this->_name]['error'];
    }
    
    
    
    public function getName()
    {
        return $_FILES[$this->_name]['name'];
    }
    
    
    
    public function getTempName()
    {
        return $_FILES[$this->_name]['tmp_name'];
    }
    
    
    
    public function save($file)
    {
        if(!move_uploaded_file($_FILES[$this->_name]['tmp_name'], $file)) {
            throw new Sitengine_Exception('uploaded file could not be saved: '.$file);
        }
    }
}
?>