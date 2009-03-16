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



require_once 'Sitengine/Env.php';
require_once 'Sitengine/Form/Element.php';


class Sitengine_Debug_Sections
{
    
    
    public static function getForm(Sitengine_Controller_Request_Http $request, $mode, array $options=array(), $selector='')
    {
        $html = array();
        $hiddens = array();
        /*
        $hiddens = $request->getParams();
        unset($hiddens[Sitengine_Env::PARAM_MODULE]);
		unset($hiddens[Sitengine_Env::PARAM_CONTROLLER]);
		unset($hiddens[Sitengine_Env::PARAM_ACTION]);
        #unset($hiddens[Sitengine_Env::PARAM_SUBMISSION]);
        unset($hiddens[Sitengine_Env::PARAM_LOGINUSER]);
        unset($hiddens[Sitengine_Env::PARAM_LOGINPASS]);
        unset($hiddens[Sitengine_Env::PARAM_DBG]);
        
        foreach($hiddens as $k => $v) {
            $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
        }
        */
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
        
        $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_DBG, $mode);
        $e->setId($selector.Sitengine_Env::PARAM_DBG);
        $e->setClass($selector.'Select');
        $html[Sitengine_Env::PARAM_DBG] = $e->getSelect($options);
        
        return array(
            'hiddens' => implode('', $hiddens),
            'ELEMENTS' => $html
        );
    }
}

?>