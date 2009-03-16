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


require_once 'Zend/Controller/Request/Http.php';
require_once 'Sitengine/String.php';


class Sitengine_Controller_Request_Http extends Zend_Controller_Request_Http
{
    
    /*
    ###
    public function get($key, $default=null)
    {
        $val = (isset($_COOKIE[$key])) ? Sitengine_String::gpcStripSlashes($_COOKIE[$key]) : $default;
        $val = (isset($_POST[$key])) ? Sitengine_String::gpcStripSlashes($_POST[$key]) : $val;
        $val = (isset($_GET[$key])) ? Sitengine_String::gpcStripSlashes($_GET[$key]) : $val;
        return $val;
    }
    */
    
    public function __get($key)
    {
        switch (true) {
            case isset($this->_params[$key]):
                return $this->_params[$key];
            case isset($_GET[$key]):
                return Sitengine_String::gpcStripSlashes($_GET[$key]);
            case isset($_POST[$key]):
                return Sitengine_String::gpcStripSlashes($_POST[$key]);
            case isset($_COOKIE[$key]):
                return Sitengine_String::gpcStripSlashes($_COOKIE[$key]);
            case ($key == 'REQUEST_URI'):
                return $this->getRequestUri();
            case ($key == 'PATH_INFO'):
                return $this->getPathInfo();
            case isset($_SERVER[$key]):
                return $_SERVER[$key];
            case isset($_ENV[$key]):
                return $_ENV[$key];
            default:
                return null;
        }
    }
    
    ###
    public function getQuery($key=null, $default=null)
    {
    	if($key === null)
    	{
            $in = array();
            
			foreach($_GET as $k => $v) {
				$in[$k] = Sitengine_String::gpcStripSlashes($v);
			}
			return $in;
        }
        return (isset($_GET[$key])) ? Sitengine_String::gpcStripSlashes($_GET[$key]) : $default;
    }
    
    
    ###
    public function getPost($key=null, $default=null)
    {
    	if($key === null)
    	{
            $in = array();
			
			foreach($_POST as $k => $v) {
				$in[$k] = Sitengine_String::gpcStripSlashes($v);
			}
			return $in;
        }
        return (isset($_POST[$key])) ? Sitengine_String::gpcStripSlashes($_POST[$key]) : $default;
    }
    
    
    ###
    public function getCookie($key=null, $default=null)
    {
    	if($key === null)
    	{
            $in = array();
			
			foreach($_COOKIE as $k => $v) {
				$in[$k] = Sitengine_String::gpcStripSlashes($v);
			}
			return $in;
        }
        return (isset($_COOKIE[$key])) ? Sitengine_String::gpcStripSlashes($_COOKIE[$key]) : $default;
    }
    
    
    
    public function getParam($key, $default = null)
    {
        $keyName = (null !== ($alias = $this->getAlias($key))) ? $alias : $key;

        if (isset($this->_params[$keyName])) {
            return $this->_params[$keyName];
        } elseif ((isset($_GET[$keyName]))) {
            return $this->getQuery($keyName);
        } elseif ((isset($_POST[$keyName]))) {
            return $this->getPost($keyName);
        }

        return $default;
    }
    
    
    
    public function getParams()
    {
    	return $this->_params + $this->getQuery(null) + $this->getPost(null);
    }
    
    
    
    public function getPostGetParams()
    {
        return $this->getQuery(null) + $this->getPost(null);
    }
    
    
    
    public function getAll()
    {
        $in = self::getCookie(null);
        $in = array_merge($in, self::getPost(null));
        $in = array_merge($in, self::getQuery(null));
        $in = array_merge($in, $this->_params);
        return $in;
    }
    
    
    
    public static function makeNameValueQuery(array $args, $argSep = '&amp;')
    {
    	$a = '';
		
		foreach($args as $name => $val) {
			$a .= ($val != '') ? (($a) ? $argSep : '').$name.'='.$val : '';
		}
		return ($a) ? '?'.$a : '';
    }
    
    
    
    public static function makeNameValuePath(array $args)
    {
    	$a = '';
		
		foreach($args as $name => $val) {
			$a .= ($val != '') ? '/'.$name.'/'.$val : '';
		}
		return $a;
    }
    
    
    
    public function getMethod()
    {
    	require_once 'Sitengine/Env.php';
    	
    	if(parent::getMethod() == Sitengine_Env::METHOD_POST)
    	{
			if($this->getPost(Sitengine_Env::PARAM_METHOD) == Sitengine_Env::METHOD_PUT)
			{
				return Sitengine_Env::METHOD_PUT;
			}
			else if($this->getPost(Sitengine_Env::PARAM_METHOD) == Sitengine_Env::METHOD_DELETE)
			{
				return Sitengine_Env::METHOD_DELETE;
			}
			return Sitengine_Env::METHOD_POST;
		}
		else if(parent::getMethod() == Sitengine_Env::METHOD_PUT)
		{
			return Sitengine_Env::METHOD_PUT;
		}
		else if(parent::getMethod() == Sitengine_Env::METHOD_DELETE)
		{
			return Sitengine_Env::METHOD_DELETE;
		}
		else {
			return Sitengine_Env::METHOD_GET;
		}
    }
    


    public static function filterInsertDeprecated($submit, array $input, array $fieldsNormal=array(), array $fieldsOnOff=array())
    {
        $data = array();
        
        foreach($fieldsOnOff as $k => $v) {
            if($submit && array_key_exists($k, $input)) { $data[$k] = 1; }
            else if($submit && !array_key_exists($k, $input)) { $data[$k] = 0; }
            else { $data[$k] = $v; }
        }
        foreach($fieldsNormal as $k => $v) {
            if(array_key_exists($k, $input)) {
                $data[$k] = $input[$k];
            }
            else { $data[$k] = $v; }
        }
        return $data;
    }
    
    
    
    public static function filterUpdateDeprecated($submit, array $input, array $fieldsNormal=array(), array $fieldsOnOff=array(), array $stored=array())
    {
        $data = array();
        
        if(!$submit) {
            # use stored data
            foreach($fieldsOnOff as $k => $v) {
                $val = (isset($stored[$k])) ? $stored[$k] : '';
                $data[$k] = $val;
            }
            foreach($fieldsNormal as $k => $v) {
                $val = (isset($stored[$k])) ? $stored[$k] : '';
                $data[$k] = $val;
            }
        }
        else {
            # filter data
            foreach($fieldsOnOff as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : 0;
            }
            foreach($fieldsNormal as $k => $v) {
                $val = (isset($stored[$k])) ? $stored[$k] : '';
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $val;
            }
        }
        return $data;
    }
    
    
    
    
    
    
    
    
    
    public static function filterInsert($submit, array $input, array $fields = array())
    {
        $data = array();
        
        foreach($fields as $k => $v)
        {
            if(array_key_exists($k, $input))
            {
                $data[$k] = $input[$k];
            }
            else { $data[$k] = $v; }
        }
        return $data;
    }
    
    
    
    
    
    public static function filterUpdate($submit, array $input, array $fields = array(), array $stored = array())
    {
        $data = array();
        
        if(!$submit)
        {
            # use stored data
            foreach($fields as $k => $v)
            {
                $val = (isset($stored[$k])) ? $stored[$k] : '';
                $data[$k] = $val;
            }
        }
        else {
            # filter data
            foreach($fields as $k => $v)
            {
                $val = (isset($stored[$k])) ? $stored[$k] : '';
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $val;
            }
        }
        return $data;
    }
    
    
    
    
    
    
    
    public static function getSelectedRows(array $source, $flagItem = 'SELECTROWITEM')
    {
        $rows = array();
        
        foreach($source as $k => $v)
        {
            $id = preg_replace('/^'.$flagItem.'/', '', $k);
            if($id != $k && $v == 1) { $rows[$id] = ''; }
        }
        return $rows;
    }
    
    
    
    public static function getModifiedRows(
        array $source,
        $tokenCurrent = 'UPDATEROWITEMCURRENT',
        $tokenElement = 'UPDATEROWITEM',
        $tokenId = 'ITEMID'
    )
    {
        $current = array();
        $updated = array();
        $queued = array();
        
        foreach($source as $k => $v)
        {
            if(preg_match('/^'.$tokenCurrent.'/', $k))
            {
            	#$col = preg_replace('/^'.$tokenCurrent.'(\w*)'.$tokenId.'\w*/', "$1", $k);
                #$id = preg_replace('/^'.$tokenCurrent.'\w*'.$tokenId.'(\w*)/', "$1", $k);
                $col = preg_replace('/^'.$tokenCurrent.'(.*)'.$tokenId.'.*/', "$1", $k);
                $id = preg_replace('/^'.$tokenCurrent.'.*'.$tokenId.'(.*)/', "$1", $k);
                #print $id.' = '.$col.'<br />';
                $current[$id][$col] = $v;
                
                if(!isset($source[$tokenElement.$col.$tokenId.$id]))
                {
                    # set value for checkboxes that have been unselected
                    $updated[$id][$col] = 0;
                }
                else {
                    $updated[$id][$col] = $source[$tokenElement.$col.$tokenId.$id];
                }
                # compare current and updated data
                if($updated[$id][$col] != $current[$id][$col]) {
                    $queued[$id] = $updated[$id];
                }
            }
        }
        return $queued;
    }
}


?>