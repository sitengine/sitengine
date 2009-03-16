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



require_once 'Sitengine/Regex.php';


abstract class Sitengine_Validator
{
    
    
    public static function nada($val, $ignore='')
    {
        if(preg_match('/^\s*$/', $val)) { return true; }
        else if($val==$ignore) { return true; }
        else { return false; }
    }
    
    
    
    public static function validId($id)
    {
        return preg_match('/^\w+$/', $id);
    }
    
    
    
    public static function float($val)
    {
        return preg_match('/^\d+(\.\d+)?$/', $val);
    }
    
    
    
    public static function word($val)
    {
        return preg_match('/^\w+$/', $val);
    }
    
    
    
    public static function date($val, $year=4)
    {
        switch($year)
        {
            case 2: $format = '{2,2}'; break; # 2 digit year only
            default: $format = '{4,4}'; # 4 digit year
        }
        $sep = '[\.\/:-]';
        return preg_match('/^\d{1,2}'.$sep.'\d{1,2}'.$sep.'\d'.$format.'$/', $val);
    }
    
    
    
    public static function time($val, $useSeconds=0)
    {
        $sep = '[\.\/:-]';
        switch($useSeconds)
        {
            case 1: $seconds = ''; break; # no seconds allowed
            case 2: $seconds = $sep.'\d{1,2}'; break; # seconds required
            default: $seconds = '('.$sep.'\d{1,2})?'; # seconds optional
        }
        return preg_match('/^\d{1,2}'.$sep.'\d{1,2}'.$seconds.'$/', $val);
    }
    
    
    
    public static function exactLength($val, $length)
    {
        $val = mb_strlen($val);
        return ($val==$length);
    }
    
    
    
    public static function maxLength($val, $length)
    {
        $val = mb_strlen($val);
        return ($val<=$length);
    }
    
    
    
    public static function inNumericRange($val, $min, $max)
    {
        if(!is_numeric($val)) { return false; }
        else { return ($val >= $min && $val <= $max); }
    }
    
    
    
    public static function emailAddressChars($val)
    {
        return preg_match('/^[\w\d\.@-]+$/', $val);
    }
    
    
    
    public static function emailAddress($val)
    {
        $p  = '/^';
        $p .= Sitengine_Regex::getMailbox(); # mailbox portion
        $p .= Sitengine_Regex::getServerName();
        $p .= '$/';
        return preg_match($p, $val);
    }
    
    
    
    public static function serverName($val)
    {
        $p  = '/^';
        $p .= '((ftp|https?):\/\/)?'; # protocol
        $p .= Sitengine_Regex::getServerName();
        $p .= '$/';
        return preg_match($p, $val);
    }
    
    
    
    public static function fileName($val)
    {
        $p  = '/^';
        $p .= Sitengine_Regex::getFileName();
        $p .= '$/';
        return preg_match($p, $val);
    }
    
    
    
    public static function filePath($val)
    {
        $p  = '/^';
        $p .= '(\/'.Sitengine_Regex::getFileName().')*\/'.Sitengine_Regex::getFileName();
        $p .= '$/';
        return preg_match($p, $val);
    }
    
    
    
    # format: 0-255/0-255/0-255
    public static function rgbColor($color)
    {
        if(preg_match('/^(\d{1,3})\/(\d{1,3})\/(\d{1,3})$/', $color, $matches))
        {
            if($matches[1]<0 || $matches[1] > 255) { return false; }
            if($matches[2]<0 || $matches[2] > 255) { return false; }
            if($matches[3]<0 || $matches[3] > 255) { return false; }
            return array(
                'red' => $matches[1],
                'green' => $matches[2],
                'blue' => $matches[3]
            );
        }
        return false;
    }
}

?>