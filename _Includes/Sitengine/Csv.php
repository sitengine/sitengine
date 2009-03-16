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


class Sitengine_Csv
{
	
	/**
	 * Split a line from a CSV file into fields. Should handle various field
	 * delimiters, escape characters and enclosing quotes for fields
	 *
	 * @param string $line Line to split for the CSV
	 * @param string $delimiter Field delimiter (defaults to ',')
	 * @param string $escaper Field escaper (defaults to '\')
	 * @param string $encloser Field encloser (defaults to '"')
	 */
    public static function splitLine($line, $delimiter=',', $encloser='"', $escaper='\\')
    {
        $fields = array();               
        if (is_string($delimiter) && is_string($encloser)) :
            
            // Make sure the arguments are regex-safe 
            $regSafeDelimiter = '\x'.dechex(ord($delimiter));
            $regSafeEncloser = '\x'.dechex(ord($encloser));
            $regSafeEscaper = '\x'.dechex(ord($escaper));
        
            $line = trim($line);
            
            // Replace any 'quote-escaped' quotes within fields. This is only really necessary to 
            // handle excel exports, which often escape double quotes with double quotes. 
            if ($encloser == $escaper) :
                $line = mb_ereg_replace($regSafeEscaper.$regSafeEncloser, "__ESCAPED__ENCLOSER__", $line);
            endif;
            
            // Loop over the string and extract each field       
            $fieldNum = 0;
            while(mb_strlen($line) > 0) :
                if(mb_substr($line, 0, 1) == $encloser) :
                
                    // If this string starts with an encloser, look for the next (non-escaped) encloser
                    preg_match('/^'.$regSafeEncloser.'((?:[^'.$regSafeEncloser.
                                ']|(?<='.$regSafeEscaper.')'.$regSafeEncloser.
                                ')*)'.$regSafeEncloser.$regSafeDelimiter.
                                '?(.*)$/', $line, $matches);
                    $value = mb_ereg_replace($regSafeEscaper.$regSafeEncloser, $encloser, $matches[1]);
                    $line = trim($matches[2]);                   
                    $fields[$fieldNum++] = $value;
                    
                // Otherwise, look for the next (non-escaped) delimiter
                else :
                    preg_match('/^((?:[^'.$regSafeDelimiter.
                                ']|(?<='.$regSafeEscaper.')'.$regSafeDelimiter.
                                ')*)'.$regSafeDelimiter.
                                '?(.*)$/', $line, $matches);                   
                    $value = mb_ereg_replace($regSafeEscaper.$regSafeDelimiter, $delimiter, $matches[1]);
                    $line = trim($matches[2]);
                    $fields[$fieldNum++] = $value;
                endif;
            endwhile;
       
            if ($encloser == $escaper) :
                for ($i=0; $i<count($fields); $i++) :    
                    $fields[$i] = mb_ereg_replace("__ESCAPED__ENCLOSER__", $encloser, $fields[$i]);
                endfor;
            endif;
        endif;
            
        return $fields;
    }
    
}

?>