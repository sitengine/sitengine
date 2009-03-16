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
 
 
class Sitengine_Directory
{
	
	
	public static function calculateSize($path, $excludeDsStoreFile = false)
	{
		if(!is_dir($path)) { return false; }
		
		$size = 0;
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path),
			RecursiveIteratorIterator::SELF_FIRST
		);
		
		foreach($files as $info)
		{
			if(
				!$info->isDir() &&
				!preg_match('/^\.\.?$/i', $info->getFilename())
			)
			{
				if($excludeDsStoreFile && $info->getFilename() == '.DS_Store') { continue; }
				$size += $info->getSize();
			}
		}
		return $size;
	}
	
	
	
	public function walkRecursively($dir, $callbackDirhandler='', $callbackFilehandler='', $depth=0, $maxDepth=10)
	{
		if($depth >= $maxDepth) { return false; }
		else if(is_dir($dir) && is_readable($dir)) {
			$d = dir($dir);
			while(($f = $d->read())!==false) {
				if($f == '.' || $f == '..') { continue; } # skip . and ..
				else if(is_dir("$dir/$f")) {
					if($callbackDirhandler) {
						eval('$callbackDirhandler($dir, $f);');
					}
					Sitengine_Directory::walkRecursively(
						"$dir/$f",
						$callbackDirhandler,
						$callbackFilehandler,
						$depth+1,
						$maxDepth
					);
				}
				else {
					if($callbackFilehandler) {
						eval('$callbackFilehandler($dir, $f);');
					}
				}
			}
			$d->close();
		}
	}
	
	
    public function walk($dir, $callbackDirhandler='', $callbackFilehandler='')
    {
    	if(is_dir($dir)) {
            $handle = opendir($dir);
            while($f = readdir($handle)) {
            	if($f != '.' && $f != '..') {
            		if(is_dir("$dir/$f") && $callbackDirhandler) { eval('$callbackDirhandler($dir, $f);'); }
            		else if(is_file("$dir/$f") && $callbackFilehandler) { eval('$callbackFilehandler($dir, $f);'); }
            	}
            }
            return true;
        }
        return false;
    }
    
}

?>