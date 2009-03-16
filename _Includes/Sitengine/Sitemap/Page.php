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



class Sitengine_Sitemap_Page
{
    
    protected $_database = null;
    protected $_table = null;
    protected $_data = array();
    protected $_snippets = array();
    protected $_files = array();
    protected $_fetches = array();
    protected $_title = null;
    protected $_metaKeywords = null;
    protected $_metaDescription = null;
    
    
    
    public function __construct(
    	Zend_Db_Adapter_Abstract $database,
    	$table = 'sitemap'
    )
    {
		$this->_database = $database;
		$this->_table = $table;
    }
    
    
    public function getTitle()
    {
    	return $this->_title;
    }
    
    public function getMetaKeywords()
    {
    	return $this->_metaKeywords;
    }
    
    public function getMetaDescription()
    {
    	return $this->_metaDescription;
    }
    
    public function getSnippets()
    {
    	return $this->_snippets;
    }
    
    public function getFiles()
    {
    	return $this->_files;
    }
    
    
    
    public function fetch($path, $translationIndex = 0, $obfuscateEmail = true)
    {
    	try {
    		$defaultIndex = 0;
    		$this->_data = $this->_fetchPage($path, $translationIndex, $defaultIndex);
    		$this->_title = (isset($this->_data['title'])) ? $this->_data['title'] : '';
    		$this->_metaKeywords = (isset($this->_data['metaKeywords'])) ? $this->_data['metaKeywords'] : '';
    		$this->_metaDescription = (isset($this->_data['metaDescription'])) ? $this->_data['metaDescription'] : '';
			
    		if(isset($this->_data['id'])) {
    			$this->_snippets = $this->_fetchSnippets($this->_data['id'], $translationIndex, $defaultIndex, $obfuscateEmail);
    			$this->_files = $this->_fetchFiles($this->_data['id']);
    		}
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('get page item error', $exception);
		}
    }
    
    
    
    protected function _fetchPage($path, $translationIndex, $defaultIndex)
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
				$q .= ' IF(titleLang'.$translationIndex.'="", titleLang'.$defaultIndex.', titleLang'.$translationIndex.') AS title,';
				$q .= ' IF(metaKeywordsLang'.$translationIndex.'="", metaKeywordsLang'.$defaultIndex.', metaKeywordsLang'.$translationIndex.') AS metaKeywords,';
				$q .= ' IF(metaDescriptionLang'.$translationIndex.'="", metaDescriptionLang'.$defaultIndex.', metaDescriptionLang'.$translationIndex.') AS metaDescription';
				$q .= ' FROM '.$this->_table;
				$q .= ' WHERE keyword = "'.$keywords[$x].'"';
				$q .= ' AND pid = "'.$itemId.'"';
				$statement = $this->_database->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				#Sitengine_Debug::print_r($result);
				if(is_array($result) && sizeof($result))
				{
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
    
    
    
    protected function _fetchSnippets($pid, $translationIndex, $defaultIndex, $obfuscateEmail)
    {
    	try {
    		require_once 'Sitengine/String.php';
    		require_once 'Sitengine/Regex.php';
    		require_once 'Sitengine/Sitemap.php';
    		$snippets = array();
    		
			$q  = 'SELECT';
			$q .= ' id,';
			$q .= ' keyword,';
			$q .= ' IF(htmlLang'.$translationIndex.' = "", htmlLang'.$defaultIndex.', htmlLang'.$translationIndex.') AS html';
			$q .= ' FROM '.$this->_table;
			$q .= ' WHERE pid = "'.$pid.'"';
			$q .= ' AND type = "'.Sitengine_Sitemap::ITEMTYPE_SNIPPET.'"';
			$statement = $this->_database->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			foreach($result as $snippet)
			{
				if($obfuscateEmail)
				{
					$snippet['html'] = preg_replace_callback(
						'/('.Sitengine_Regex::getEmail().')/',
						create_function(
							'$matches',
							'return Sitengine_String::obfuscateEmail($matches[0]);'
						),
						$snippet['html']
					);
				}
				$snippets[$snippet['keyword']] = $snippet['html'];
			}
			return $snippets;
		}
		catch(Exception $exception) {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('fetch snippets error', $exception);
		}
    }
    
    
    
    protected function _fetchFiles($pid)
    {
    	try {
    		require_once 'Sitengine/Sitemap.php';
    		
    		$files = array();
    		
			$q  = 'SELECT';
			$q .= ' id,';
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