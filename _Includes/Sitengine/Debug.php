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


class Sitengine_Debug
{
    
    
    public static function clearSession()
    {
    	foreach($_SESSION as $k => $v) { session_unregister($k); }
    }
    
    
    public static function destroySession()
    {
    	if(isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
    }
    
    
    public static function deleteCookies()
    {
    	foreach($_COOKIE as $k => $v) {
			setcookie($k, '', time()-42000, '/');
		}
    }
    
    
    public static function getExecTime()
    {
    	if(defined('SITENGINE_DEBUG_STARTEXEC')) {
			list($usec, $sec) = explode(' ', SITENGINE_DEBUG_STARTEXEC);
			$start = ((float)$usec + (float)$sec);
			list($usec, $sec) = explode(' ', microtime());
			$endTime = ((float)$usec + (float)$sec);
			$time = round(($endTime - $start), 4);
			print '<hr><h1>EXECUTION TIME: '.$time.'</h1><hr>';
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('Constant SITENGINE_DEBUG_STARTEXEC must be set before using self::getExecTime()');
		}
    }
    
    
    public static function printGlobals()
    {
    	print '<h1>GLOBALS</h1><pre>';
		foreach($GLOBALS as $k => $v) {
			print $k.' = ';
			print (is_object($v)) ? get_class($v).' Object' : $v;
			print '<br />';
		}
		print '</pre>';
    }
    
    
    public static function printIncludes()
    {
    	print '<h1>INCLUDES</h1>';
        self::print_r(get_included_files());
    }
    
    
    public static function printConstants()
    {
    	print '<h1>CONSTANTS</h1>';
        self::print_r(get_defined_constants(true));
    }
    
    
    public static function printUserConstants()
    {
    	print '<h1>USER CONSTANTS</h1><pre>';
		$constants = get_defined_constants(true);
		if(isset($constants['user'])) {
			foreach($constants['user'] as $name => $val) {
				print $name.' = '.$val.'<br />';
			}
		}
    }
    
    
    public static function printClasses()
    {
    	print '<h1>CLASSES</h1>';
        self::print_r(get_declared_classes());
    }
    
    
    public static function printSession()
    {
    	print '<h1>SESSION</h1>';
        self::print_r($_SESSION);
    }
    
    
    public static function printInput()
    {
    	print '<h1><h1>COOKIE</h1>';
		self::print_r($_COOKIE);
		print '<hr><h1>GET</h1>';
		self::print_r($_GET);
		print '<hr><h1>POST</h1>';
		self::print_r($_POST);
		print '<hr><h1>FILES</h1>';
		self::print_r($_FILES);
    }
    
    
    public static function printServer()
    {
    	print '<h1>SERVER</h1>';
        self::print_r($_SERVER);
    }
    
    
    public static function printEnv()
    {
    	print '<h1>ENV</h1>';
        self::print_r($_ENV);
    }
    
    
    public static function print_r($var)
    {
        print '<pre>';
        print_r($var);
        print '</pre>';
    }
    
    
    public static function action($mode)
    {
    	switch($mode) {
    		case 'clearSession': self::clearSession(); break;
    		case 'destroySession': self::destroySession(); break;
    		case 'deleteCookies': self::deleteCookies(); break;
    	}
    }
    
    
    public static function info($mode)
    {
    	ob_start();
    	
    	switch($mode) {
    		case 'execTime': self::getExecTime(); break;
    		case 'phpInfo': phpinfo(); break;
    		case 'globals': self::printGlobals(); break;
    		case 'constants': self::printConstants(); break;
    		case 'userConstants': self::printUserConstants(); break;
    		case 'includes': self::printIncludes(); break;
    		case 'classes': self::printClasses(); break;
    		case 'session': self::printSession(); break;
    		case 'input': self::printInput(); break;
    		case 'server': self::printServer(); break;
    		case 'env': self::printEnv(); break;
    	}
    	$out = ob_get_contents();
        ob_end_clean();
        return $out;
    }
    
    
    public static function getOptions(array $options = array())
    {
    	$default = array(
            '' => 'Dbg (Off)',
            'execTime' => 'Exec Time',
            'phpInfo' => 'Phpinfo',
            'globals' => 'Globals',
            'constants' => 'Constants (All)',
            'userConstants' => 'Constants (User)',
            'includes' => 'Includes',
            'classes' => 'Classes',
            'input' => 'Input',
            'session' => 'Session',
            'server' => 'Server',
            'env' => 'Env',
            'noneSelected1' => '----------------',
            'clearSession' => '> Clear session',
            'destroySession' => '> Destroy session',
            'deleteCookies' => '> Delete cookies'
        );
        
        if(sizeof($options))
        {
        	$default['noneSelected2'] = '----------------';
        }
        
        return array_merge($default, $options);
    }
}

?>