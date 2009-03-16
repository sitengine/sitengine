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
require_once 'Sitengine/Validator.php';


/*
x = width
y = height
z = depth
*/

abstract class Sitengine_Image
{
    
    
    public static function calcWidth($width, $height, $heightNew)
    {
        return round($width/($height/$heightNew), 0);
    }
    
    
    public static function calcHeight($width, $height, $widthNew)
    {
        return round($height/($width/$widthNew), 0);
    }
    
    
    /**
     *
     * @throws Sitengine_Exception
     *
     */
    public static function resizeJpeg($inFile, $outFile, $length, $method, $mode=0644, $quality=50)
    {
        $size = getimagesize($inFile);
        if(!$size) { throw new Sitengine_Exception('jpg could not be opened'); }
        if($size[2]!=2) { throw new Sitengine_Exception('image is not a jpg'); }
        
        if(!preg_match('/^\d{1,3}$/', $quality)) { $quality = 50; }
        if($quality<0 || $quality>100) { $quality = 50; }
        
        switch($method)
        {
            case 'width': {
                $width = $length;
                $height = self::calcHeight($size[0], $size[1], $length);
                break;
            }
            case 'height': {
                $width = self::calcWidth($size[0], $size[1], $length);
                $height = $length;
                break;
            }
            default: {
                if($size[0] > $size[1]) {
                    $width = $length;
                    $height = self::calcHeight($size[0], $size[1], $length);
                }
                else {
                    $width = self::calcWidth($size[0], $size[1], $length);
                    $height = $length;
                }
                break;
            }
        }
        
        $inRid = ImageCreateFromJpeg($inFile);
        if(!$inRid) { throw new Sitengine_Exception('jpg processing error'); }
        
        $outRid = imagecreatetruecolor($width, $height);
        if(!$outRid) { throw new Sitengine_Exception('jpg processing error'); }
        
        $copy = imagecopyresampled(
            $outRid, # resource new image,
            $inRid, # resource src image,
            0, # int dst_x, 
            0, # int dst_y,
            0, # int src_x,
            0, # int src_y,
            $width, # int new w,
            $height, # int new h,
            $size[0], # int src w,
            $size[1] # int src h
        );
        if(!$copy) { throw new Sitengine_Exception('jpg processing error'); }
        
        $image = imagejpeg($outRid, $outFile, $quality);
        if(!$image) { throw new Sitengine_Exception('jpg processing error'); }
        
        chmod($outFile, $mode);
        $stats = stat($outFile);
        
        return array(
            'mime' => 'image/jpeg',
            'size' => $stats['size'],
            'width' => $width,
            'height' => $height
        );
    }
    
    
    
    /**
     *
     * @throws Sitengine_Exception
     *
     */
    public static function resizeGif($inFile, $outFile, $length, $method, $mode=0644, $transColor='')
    {
        $size = getimagesize($inFile);
        if(!$size) { throw new Sitengine_Exception('gif could not be opened'); }
        if($size[2]!=1) { throw new Sitengine_Exception('image is not a gif'); }
        
        switch($method)
        {
            case 'width': {
                $width = $length;
                $height = self::calcHeight($size[0], $size[1], $length);
                break;
            }
            case 'height': {
                $width = self::calcWidth($size[0], $size[1], $length);
                $height = $length;
                break;
            }
            default: {
                if($size[0] > $size[1]) {
                    $width = $length;
                    $height = self::calcHeight($size[0], $size[1], $length);
                }
                else {
                    $width = self::calcWidth($size[0], $size[1], $length);
                    $height = $length;
                }
                break;
            }
        }
        
        $inRid = ImageCreateFromGif($inFile);
        if(!$inRid) { throw new Sitengine_Exception('gif processing error'); }
        
        $outRid = imagecreatetruecolor($width, $height);
        if(!$outRid) { throw new Sitengine_Exception('gif processing error'); }
        
        $rgbColors = Sitengine_Validator::rgbColor($transColor);
        if($rgbColors) {
            $color = imagecolorallocate($outRid, $rgbColors['red'], $rgbColors['green'], $rgbColors['blue']);
            $transparency = imagecolortransparent($outRid, $color);
        }
        
        /*
        $copy = imagecopymerge(
            $outRid, # resource new image,
            $inRid, # resource src image,
            0, # int dst_x, 
            0, # int dst_y,
            0, # int src_x,
            0, # int src_y,
            $width, # int new w,
            $height, # int new h,
            0 # pct ??
        );
        
        $copy = imagecopy(
            $outRid, # resource new image,
            $inRid, # resource src image,
            0, # int dst_x, 
            0, # int dst_y,
            0, # int src_x,
            0, # int src_y,
            $width, # int new w,
            $height # int new h,
        );
        */
        $copy = imagecopyresized(
            $outRid, # resource new image,
            $inRid, # resource src image,
            0, # int dst_x, 
            0, # int dst_y,
            0, # int src_x,
            0, # int src_y,
            $width, # int new w,
            $height, # int new h,
            $size[0], # int src w,
            $size[1] # int src h
        );
        if(!$copy) { throw new Sitengine_Exception('gif processing error'); }
        
        $image = imagegif($outRid, $outFile);
        if(!$image) { throw new Sitengine_Exception('gif processing error'); }
        
        chmod($outFile, $mode);
        $stats = stat($outFile);
        
        return array(
            'mime' => 'image/gif',
            'size' => $stats['size'],
            'width' => $width,
            'height' => $height
        );
    }
    
    
    
    
    
    
    /**
     *
     * @throws Sitengine_Exception
     *
     */
    public static function resizePng($inFile, $outFile, $length, $method, $mode=0644, $transColor='')
    {
        $size = getimagesize($inFile);
        if(!$size) { throw new Sitengine_Exception('png could not be opened'); }
        if($size[2]!=3) { throw new Sitengine_Exception('image is not a png'); } # not a png
        
        switch($method)
        {
            case 'width': {
                $width = $length;
                $height = self::calcHeight($size[0], $size[1], $length);
                break;
            }
            case 'height': {
                $width = self::calcWidth($size[0], $size[1], $length);
                $height = $length;
                break;
            }
            default: {
                if($size[0] > $size[1]) {
                    $width = $length;
                    $height = self::calcHeight($size[0], $size[1], $length);
                }
                else {
                    $width = self::calcWidth($size[0], $size[1], $length);
                    $height = $length;
                }
                break;
            }
        }
        
        $inRid = imagecreatefrompng($inFile);
        if(!$inRid) { throw new Sitengine_Exception('png processing error'); }
        
        $outRid = imagecreatetruecolor($width, $height);
        if(!$outRid) { throw new Sitengine_Exception('png processing error'); }
        
        $rgbColors = Sitengine_Validator::rgbColor($transColor);
        if($rgbColors) {
            $color = imagecolorallocate($outRid, $rgbColors['red'], $rgbColors['green'], $rgbColors['blue']);
            $transparency = imagecolortransparent($outRid, $color);
        }
        
        $copy = imagecopyresized(
            $outRid, # resource new image,
            $inRid, # resource src image,
            0, # int dst_x, 
            0, # int dst_y,
            0, # int src_x,
            0, # int src_y,
            $width, # int new w,
            $height, # int new h,
            $size[0], # int src w,
            $size[1] # int src h
        );
        if(!$copy) { throw new Sitengine_Exception('png processing error'); }
        
        $image = imagepng($outRid, $outFile);
        if(!$image) { throw new Sitengine_Exception('png processing error'); }
        
        chmod($outFile, $mode);
        $stats = stat($outFile);
        
        return array(
            'mime' => 'image/png',
            'size' => $stats['size'],
            'width' => $width,
            'height' => $height
        );
    }
}


?>