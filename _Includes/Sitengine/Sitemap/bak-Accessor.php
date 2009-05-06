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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



class Sitengine_Sitemap_Accessor
{
    
    protected $_env = null;
    protected $_database = null;
    protected $_permiso = null;
    protected $_table = null;
    protected $_fetches = array();
    
    
    public function __construct(
    	Sitengine_Env $env,
    	Zend_Db_Adapter_Abstract $database,
    	Sitengine_Permiso $permiso,
    	$table = 'sitemap'
    )
    {
		$this->_env = $env;
		$this->_database = $database;
		$this->_permiso = $permiso;
		$this->_table = $table;
    }
    
    
    /*
    public function getSnippet($path, $transcriptIndex = 0)
    {
    	try {
    		$defaultIndex = 0;
    		return $this->_fetchSnippet($path, $transcriptIndex, $defaultIndex);
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('get snippet item error', $exception);
		}
    }
    */
    
    
    public function getPage($path, $transcriptIndex = 0)
    {
    	try {
    		$defaultIndex = 0;
    		$page = $this->_fetchPage($path, $transcriptIndex, $defaultIndex);
    		$page['title'] = (isset($page['title'])) ? $page['title'] : null;
    		$page['metaKeywords'] = (isset($page['metaKeywords'])) ? $page['metaKeywords'] : null;
    		$page['metaDescription'] = (isset($page['metaDescription'])) ? $page['metaDescription'] : null;
    		$page['SNIPPETS'] = array();
			$page['FILES'] = array();
			
    		if(isset($page['id'])) {
    			$page['SNIPPETS'] = $this->_fetchSnippets($page['id'], $transcriptIndex, $defaultIndex);
    			$page['FILES'] = $this->_fetchFiles($page['id']);
    		}
    		unset($page['id']);
    		#Sitengine_Debug::print_r($page);
    		return $page;
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('get page item error', $exception);
		}
    }
    
    
    
    protected function _fetchPage($path, $transcriptIndex, $defaultIndex)
    {
    	try {
    		if(array_key_exists($path, $this->_fetches)) {
    			return $this->_fetches[$path];
    		}
    		
			$keywords = explode('/', $path);
			$itemId = '';
			$item = array();
			
			for($x=0; $x<sizeof($keywords); $x++)
			{
				$q  = 'SELECT';
				$q .= ' id,';
				#$q .= ' type,';
				$q .= ' IF(titleLang'.$transcriptIndex.'="", titleLang'.$defaultIndex.', titleLang'.$transcriptIndex.') AS title,';
				$q .= ' IF(metaKeywordsLang'.$transcriptIndex.'="", metaKeywordsLang'.$defaultIndex.', metaKeywordsLang'.$transcriptIndex.') AS metaKeywords,';
				$q .= ' IF(metaDescriptionLang'.$transcriptIndex.'="", metaDescriptionLang'.$defaultIndex.', metaDescriptionLang'.$transcriptIndex.') AS metaDescription';
				$q .= ' FROM '.$this->_table;
				$q .= ' WHERE keyword = "'.$keywords[$x].'"';
				$q .= ' AND pid = "'.$itemId.'"';
				#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_permiso->getOrganization()->getId().'"';
				#Sitengine_Db_Debug::printQuery($this->_database, $q);
				$statement = $this->_database->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				#Sitengine_Debug::print_r($result);
				if(is_array($result) && sizeof($result)) {
					$item = $result[0];
					if($x == sizeof($keywords)-1) {
						$this->_fetches[$path] = $item;
						return $item;
					}
					else { $itemId = $item['id']; }
				}
			}
			return array();
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('fetch page error', $exception);
		}
    }
    
    
    /*
    protected function _fetchSnippet($path, $transcriptIndex, $defaultIndex)
    {
    	try {
    		if(array_key_exists($path, $this->_fetches)) {
    			return $this->_fetches[$path];
    		}
    		
			$keywords = explode('/', $path);
			$itemId = '';
			$item = array();
			
			for($x=0; $x<sizeof($keywords); $x++)
			{
				$q  = 'SELECT';
				$q .= ' id,';
				#$q .= ' type,';
				$q .= ' IF(htmlLang'.$transcriptIndex.'="", htmlLang'.$defaultIndex.', htmlLang'.$transcriptIndex.') AS html';
				$q .= ' FROM '.$this->_table;
				$q .= ' WHERE keyword = "'.$keywords[$x].'"';
				$q .= ' AND pid = "'.$itemId.'"';
				#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_permiso->getOrganization()->getId().'"';
				#Sitengine_Db_Debug::printQuery($this->_database, $q);
				$statement = $this->_database->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				#Sitengine_Debug::print_r($result);
				if(is_array($result) && sizeof($result)) {
					$item = $result[0];
					if($x == sizeof($keywords)-1) {
						#Sitengine_Debug::print_r($item['html']);
						$this->_fetches[$path] = $item['html'];
						return $this->_parseSnippet($item['html'], $transcriptIndex, $defaultIndex);
					}
					else { $itemId = $item['id']; }
				}
			}
			return null;
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('fetch snippet error', $exception);
		}
    }
    */
    
    
    protected function _fetchSnippets($pid, $transcriptIndex, $defaultIndex)
    {
    	try {
    		require_once 'Sitengine/Sitemap.php';
    		$snippets = array();
    		
			$q  = 'SELECT';
			$q .= ' id,';
			#$q .= ' type,';
			$q .= ' keyword,';
			$q .= ' IF(htmlLang'.$transcriptIndex.'="", htmlLang'.$defaultIndex.', htmlLang'.$transcriptIndex.') AS html';
			$q .= ' FROM '.$this->_table;
			$q .= ' WHERE pid = "'.$pid.'"';
			$q .= ' AND type = "'.Sitengine_Sitemap::ITEMTYPE_SNIPPET.'"';
			#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_permiso->getOrganization()->getId().'"';
			#Sitengine_Db_Debug::printQuery($this->_database, $q);
			$statement = $this->_database->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			
			foreach($result as $snippet) {
				$snippets[$snippet['keyword']] = $this->_parseSnippet($snippet['html'], $transcriptIndex, $defaultIndex);
			}
			#Sitengine_Debug::print_r($snippets);
			return $snippets;
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('fetch snippets error', $exception);
		}
    }
    
    
    
    protected function _parseSnippet($s, $transcriptIndex, $defaultIndex)
    {
    	$find = array();
    	$replace = array();
    	
    	if(preg_match_all('/(?<=%)[\w\/]*(?=%)/', $s, $matches))
    	{
    		#Sitengine_Debug::print_r($matches);
    		if(isset($matches[0]))
    		{
    			foreach($matches[0] as $path)
    			{
    				# avoid looking for the same path multiple times
    				if(!array_search('/%'.$path.'%/', $find))
    				{
    					$find[] = '/%'.preg_replace('/\//', '\/', $path).'%/';
						$replace[] = $this->_fetchSnippet($path, $transcriptIndex, $defaultIndex);
    				}
    			}
    		}
    	}
    	#Sitengine_Debug::print_r($find);
    	#Sitengine_Debug::print_r($replace);
    	$s = preg_replace($find, $replace, $s);
    	return preg_replace('/%[\w\/]*%/', '', $s);
    }
    
    
    
    protected function _fetchFiles($pid)
    {
    	try {
    		require_once 'Sitengine/Sitemap.php';
    		
    		$files = array();
    		
			$q  = 'SELECT';
			$q .= ' id,';
			#$q .= ' type,';
			$q .= ' keyword,';
			$q .= ' file1OriginalName,';
			$q .= ' file1OriginalSource,';
			$q .= ' file1ThumbnailName,';
			$q .= ' file1ThumbnailWidth,';
			$q .= ' file1ThumbnailHeight,';
			$q .= ' file1ThumbnailMime,';
			$q .= ' file1ThumbnailSize';
			$q .= ' FROM '.$this->_table;
			$q .= ' WHERE pid = "'.$pid.'"';
			$q .= ' AND type = "'.Sitengine_Sitemap::ITEMTYPE_FILE.'"';
			#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_permiso->getOrganization()->getId().'"';
			#Sitengine_Db_Debug::printQuery($this->_database, $q);
			$statement = $this->_database->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			
			foreach($result as $file) {
				$files[$file['keyword']] = $file;
			}
			return $files;
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('fetch files error', $exception);
		}
    }
    
}


?>