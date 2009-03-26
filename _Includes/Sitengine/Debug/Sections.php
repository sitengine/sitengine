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


class Sitengine_Debug_Sections
{
    
    
    public static function getForm(Sitengine_Controller_Request_Http $request, $mode, array $options=array(), $selector='')
    {
    	return array();
    	/*
        $html = array();
        $hiddens = array();
        
        $defaultOptions = array(
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
        if(sizeof($options)) {
        	$defaultOptions['noneSelected2'] = '----------------';
        }
        $options = array_merge($defaultOptions, $options);
        
        require_once 'Sitengine/Form/Element.php';
        $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_DBG, $mode);
        $e->setId($selector.Sitengine_Env::PARAM_DBG);
        $e->setClass($selector.'Select');
        $html[Sitengine_Env::PARAM_DBG] = $e->getSelect($options);
        
        return array(
            'hiddens' => implode('', $hiddens),
            'ELEMENTS' => $html
        );
        */
    }
}

?>