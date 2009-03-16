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

    
/*
 * Date handling functions
 *
 * Note:
 * # Epoch refers to a count of seconds since the Unix epoch.
 * # isoDatetime refers to datetime in ISO 8601 format: yyyy-mm-dd hh:mm:ss
 * # strDatetime refers to datetime in string format: yyyymmddhhmmss
 *
 */
 
 
abstract class Sitengine_Date
{
    
    /*
    public static function datetime2parts($datetime)
    {
        $datetime = preg_replace('/[^0-9]/', '', $datetime);
        return array(
            'year' => substr($datetime,0,4),
            'mon' => substr($datetime,4,2),
            'mday' => substr($datetime,6,2),
            'hours' => substr($datetime,8,2),
            'minutes' => substr($datetime,10,2),
            'seconds' => substr($datetime,12,2)
        );
    }
    */
    
    public static function getTimezoneOffset($symbol)
    {
        # From Perl's Time::Timezone
        $timezones = array(
            #'GMT'  =>   0,           # Greenwich Mean
            'UTC'  =>   0,           # Universal (Coordinated)
            #'WET'  =>   0,           # Western European
            #'WAT'  =>  -1*3600,      # West Africa
            #'AT'   =>  -2*3600,      # Azores
            #'NFT'  =>  -3*3600-1800, # Newfoundland
            #'AST'  =>  -4*3600,      # Atlantic Standard
            #'EST'  =>  -5*3600,      # Eastern Standard
            #'CST'  =>  -6*3600,      # Central Standard
            #'MST'  =>  -7*3600,      # Mountain Standard
            #'PST'  =>  -8*3600,      # Pacific Standard
            #'YST'  =>  -9*3600,      # Yukon Standard
            #'HST'  => -10*3600,      # Hawaii Standard
            #'CAT'  => -10*3600,      # Central Alaska
            #'AHST' => -10*3600,      # Alaska-Hawaii Standard
            #'NT'   => -11*3600,      # Nome
            #'IDLW' => -12*3600,      # International Date Line West
            'CET'  =>  +1*3600,      # Central European
            #'MET'  =>  +1*3600,      # Middle European
            #'MEWT' =>  +1*3600,      # Middle European Winter
            #'SWT'  =>  +1*3600,      # Swedish Winter
            #'FWT'  =>  +1*3600,      # French Winter
            #'EET'  =>  +2*3600,      # Eastern Europe, USSR Zone 1
            #'BT'   =>  +3*3600,      # Baghdad, USSR Zone 2
            #'IT'   =>  +3*3600+1800, # Iran
            #'ZP4'  =>  +4*3600,      # USSR Zone 3
            #'ZP5'  =>  +5*3600,      # USSR Zone 4
            #'IST'  =>  +5*3600+1800, # Indian Standard
            #'ZP6'  =>  +6*3600,      # USSR Zone 5
            #'SST'  =>  +7*3600,      # South Sumatra, USSR Zone 6
            #'WAST' =>  +7*3600,      # West Australian Standard
            #'JT'   =>  +7*3600+1800, # Java 
            #'CCT'  =>  +8*3600,      # China Coast, USSR Zone 7
            #'JST'  =>  +9*3600,      # Japan Standard, USSR Zone 8
            #'CAST' =>  +9*3600+1800, # Central Australian Standard
            #'EAST' => +10*3600,      # Eastern Australian Standard
            #'GST'  => +10*3600,      # Guam Standard, USSR Zone 9
            #'NZT'  => +12*3600,      # New Zealand
            #'NZST' => +12*3600,      # New Zealand Standard
            #'IDLE' => +12*3600       # International Date Line East
        );
        return (isset($timezones[$symbol])) ? $timezones[$symbol] : 0;
    }
}
?>