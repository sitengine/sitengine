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



abstract class Sitengine_Mime_Type
{
    
    
    public static function isImage($mime)
    {
        return preg_match('/(jpg|jpeg|gif|png)/i', $mime);
    }
    
    
    
    public static function isFlash($mime)
    {
        return preg_match('/(shockwave)/i', $mime);
    }
    
    
    
    public static function isJpg($mime)
    {
        return preg_match ('/(jpg|jpeg)$/i', $mime);
    }
    
    
    
    public static function isGif($mime)
    {
        return preg_match ('/(gif)/i', $mime);
    }
    
    
    
    public static function isPng($mime)
    {
        return preg_match ('/(png)/i', $mime);
    }
    
    
    
    public static function isPdf($mime)
    {
        return preg_match ('/(pdf)/i', $mime);
    }
    
    
    
    public static function isWord($mime)
    {
        return preg_match ('/(msword)/i', $mime);
    }
    
    
    
    public static function isExcel($mime)
    {
        return preg_match ('/(xls)/i', $mime);
    }
    
    
    
    public static function getSuffix($mime, $withDot = true)
    {
        $patterns = array(
        	'/shockwave\-flash/i' => 'swf',
            '/image\/vnd\.adobe\.photoshop/i' => 'psd',
            '/msword/i' => 'doc',
            '/excel/i' => 'xls',
            '/powerpoint/i' => 'ppt',
            '/zip/i' => 'zip',
            '/pdf/i' => 'pdf',
            '/(mpeg|mpg)/i' => 'mp3',
            '/aiff/i' => 'aif',
            '/wav/i' => 'wav',
            '/bmp/i' => 'bmp',
            '/gif/i' => 'gif',
            '/(jpg|jpeg)/i' => 'jpg',
            '/png/i' => 'png',
            '/tiff/i' => 'tif',
            '/css/i' => 'css',
            '/csv/i' => 'csv',
            '/html/i' => 'html',
            '/plain/i' => 'txt',
            '/rtf/i' => 'rtf',
            '/mpeg/i' => 'mpg',
            '/quicktime/i' => 'qt',
            '/msvideo/i' => 'avi',
            '/stuffit/i' => 'sit',
            '/tar/i' => 'tar'
        );
        
        foreach($patterns as $pattern => $suffix) {
            if(preg_match($pattern, $mime)) { return ($withDot) ? '.'.$suffix : $suffix; }
        }
        return '';
    }
    
    
    
    public static function get($suffix)
    {
        $patterns = array(
        	'/\.?swf$/i' => 'application/x-shockwave-flash',
            '/\.?psd$/i' => 'image/vnd.adobe.photoshop',
            '/\.?doc$/i' => 'application/msword',
            '/\.?xls$/i' => 'application/vnd.ms-excel',
            '/\.?ppt$/i' => 'application/vnd.ms-powerpoint',
            '/\.?zip$/i' => 'application/zip',
            '/\.?pdf$/i' => 'application/pdf',
            '/\.?mp3$/i' => 'audio/mpeg',
            '/\.?aif$/i' => 'audio/x-aiff',
            '/\.?wav$/i' => 'audio/wav',
            '/\.?bmp$/i' => 'image/bmp',
            '/\.?gif$/i' => 'image/gif',
            '/\.?(jpg|jpeg|pjpeg)$/i' => 'image/jpeg',
            '/\.?png$/i' => 'image/png',
            '/\.?tif$/i' => 'image/tiff',
            '/\.?css$/i' => 'text/css',
            '/\.?csv$/i' => 'text/csv',
            '/\.?html?$/i' => 'text/html',
            '/\.?txt$/i' => 'text/plain',
            '/\.?rtf$/i' => 'text/rtf',
            '/\.?mpg$/i' => 'video/mp4',
            '/\.?qt$/i' => 'video/quicktime',
            '/\.?avi$/i' => 'video/x-msvideo',
            '/\.?sit$/i' => 'application/x-stuffit',
            '/\.?tar$/i' => 'application/x-tar'
        );
        
        foreach($patterns as $pattern => $mime) {
            if(preg_match($pattern, $suffix)) { return $mime; }
        }
        return '';
    }
    

}

?>