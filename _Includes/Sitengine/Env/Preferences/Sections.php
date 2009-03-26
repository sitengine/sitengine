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


class Sitengine_Env_Preferences_Sections
{
    
    
    public static function getLanguageForm($language, array $languages, array $languageNames, $selector='')
    {
    	return array();
    	/*
    	#Sitengine_Debug::print_r($languages);
    	#Sitengine_Debug::print_r($languageNames);
        $html = array();
        $hiddens = array();
        
        $options = array();
        foreach($languages as $k => $v) {
            $options[$v] = $languageNames[$v];
        }
        require_once 'Sitengine/Form/Element.php';
        $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_LANGUAGE, $language);
        $e->setId($selector.Sitengine_Env::PARAM_LANGUAGE);
        $e->setClass($selector.'Select');
        $html[Sitengine_Env::PARAM_LANGUAGE] = $e->getSelect($options);
        
        return array(
            'options' => $options,
            'hiddens' => implode('', $hiddens),
            'ELEMENTS' => $html
        );
        */
    }
    
    
    
    public static function getTimezoneForm($timezone, array $timezones, $selector='')
    {
    	return array();
    	/*
        $html = array();
        $hiddens = array();
        
        require_once 'Sitengine/Form/Element.php';
        $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_TIMEZONE, $timezone);
        $e->setId($selector.Sitengine_Env::PARAM_TIMEZONE);
        $e->setClass($selector.'Select');
        $html[Sitengine_Env::PARAM_TIMEZONE] = $e->getSelect($timezones);
        
        return array(
            'hiddens' => implode('', $hiddens),
            'ELEMENTS' => $html
        );
        */
    }
    
}


?>