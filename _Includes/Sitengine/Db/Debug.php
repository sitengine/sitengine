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



require_once 'Sitengine/String.php';


class Sitengine_Db_Debug
{
    
    public static function printQuery(Zend_Db_Adapter_Abstract $database, $query)
    {
    	try {
			$s = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			$s .= '<html>';
			$s .= '<head>';
			$s .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
			$s .= '</head>';
			$s .= '<body>';
			
			$statement = $database->prepare($query);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			
			$s .= "<table width=600 border=1>";
			$s .= "<tr><th colspan=\"2\" align=\"left\" bgcolor=\"#eeeeee\"><h2>Database - Select Query</h2></td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Database Class</nobr></nobr></td><td>".get_class($database)."&nbsp;</td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Statement Class</nobr></td><td>".get_class($statement)."&nbsp;</td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Rows</nobr></td><td>".sizeof($result)."&nbsp;</td></tr>";
			#$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Columns</nobr></td><td>".$numCols."&nbsp;</td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Query</nobr></td><td>".Sitengine_String::html($query)."&nbsp;</td></tr>";
			$s .= "</table><br />";
			
			$count = 0;
			
			if(sizeof($result)>0)
			{
				$s .= "<table border=1>";
				foreach($result as $row)
				{
					if($count==0) {
						# column index
						$colCount = 0;
						$s .= "<tr>";
						foreach($row as $k => $v) {
							$s .= '<th align="left" bgcolor="#eeeeee">'.$colCount++.'</th>';
						}
						$s .= "</tr>";
						
						# column names
						$s .= "<tr>";
						foreach($row as $k => $v) {
							$s .= '<th align="left" bgcolor="#eeeeee">'.Sitengine_String::html($k).'</th>';
						}
						$s .= "</tr>";
					}
					$s .= "<tr>";
					foreach($row as $k => $v) {
						$v = mb_substr($v, 0, 100);
						$s .= '<td>'.Sitengine_String::html($v).'&nbsp;</td>';
					}
					$s .= "</tr>";
					$count++;
				}
				$s .= "</table><br />";
			}
			
			
			# EXPLAIN
			$statement = $database->prepare('EXPLAIN '.$query);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				
			$count = 0;
			
			if(sizeof($result)>0)
			{
				$s .= "<table border=1>";
				foreach($result as $row)
				{
					if($count==0) {
						# column names
						$s .= "<tr>";
						foreach($row as $k => $v) { $s .= '<th align="left" bgcolor="#eeeeee">'.Sitengine_String::html($k).'</th>'; }
						$s .= "</tr>";
					}
					$s .= "<tr>";
					foreach($row as $k => $v) {
						$v = mb_substr($v, 0, 100);
						$s .= '<td>'.Sitengine_String::html($v).'&nbsp;</td>';
					}
					$s .= "</tr>";
					$count++;
				}
				$s .= "</table><br />";
			}
			$s .= '</body>';
			$s .= '</html>';
			print $s;
		}
		catch (Exception $exception)
		{
			$s  = "<table width=600 border=1>";
			$s .= "<tr><th colspan=\"2\" align=\"left\" bgcolor=\"#eeeeee\"><h2>Database - Select Query</h2></td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Database Class</nobr></td><td>".get_class($database)."&nbsp;</td></tr>";
			#$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Statement Class</nobr></td><td>".get_class($result)."&nbsp;</td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Code</nobr></td><td>".$exception->getCode()."&nbsp;</td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Error</nobr></td><td>".$exception->getMessage()."&nbsp;</td></tr>";
			#$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Native Code</nobr></td><td>".$this->getNativeErrorCode()."&nbsp;</td></tr>";
			#$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Native Error</nobr></td><td>".Sitengine_String::html($this->getNativeError())."&nbsp;</td></tr>";
			$s .= "<tr><th valign=\"top\" align=\"left\" bgcolor=\"#eeeeee\"><nobr>Query</nobr></td><td>".Sitengine_String::html($query)."&nbsp;</td></tr>";
			$s .= "</table><br />";
			print $s;
		}
    }
    
    
    
    public static function profiler(Zend_Db_Adapter_Abstract $database)
    {
    	if($database->getProfiler()->getEnabled())
		{
			$totalTime = $database->getProfiler()->getTotalElapsedSecs();
			$queryCount = $database->getProfiler()->getTotalNumQueries();
			$longestTime = 0;
			$longestQuery = null;
			
			$s = '<ol>';
			
			foreach($database->getProfiler()->getQueryProfiles() as $profile)
			{
				$s .= '<li>'.$profile->getQuery();
				$s .= ' <span style="color: #green;">('.$profile->getElapsedSecs().')</span></li>';
				
				if ($profile->getElapsedSecs() > $longestTime) {
					$longestTime = $profile->getElapsedSecs();
					$longestQuery = $profile->getQuery();
				}
			}
			$s .= '</ol>';
			$s .= '<hr />';
			$s .= '<ul>';
			$s .= '<li>Executed '.$queryCount.' queries in '.$totalTime.' seconds</li>';
			$s .= '<li>Average query length: '.$totalTime / $queryCount.' seconds</li>';
			$s .= '<li>Queries per second: '.$queryCount / $totalTime.'</li>';
			$s .= '<li>Longest query length: '.$longestTime.'</li>';
			$s .= '<li>Longest query: '.$longestQuery.'</li>';
			$s .= '</ul>';
			print $s;
		}
		else {
			print 'Profiler must be enabled first: $database->getProfiler()->setEnabled(true);';
		}
    }
    
    
    
    
    
    
    
    /*
    public function printExec($affectedRows, $query='')
    {
        $s = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $s .= '<html>';
        $s .= '<head>';
        $s .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        $s .= '</head>';
        $s .= '<body>';
        
        $database =& $this->getDBInstance();
        if(PEAR::isError($database)) { die($database->getMessage()); }
        
        if(PEAR::isError($affectedRows))
        {
            $s .= "<table width=600 border=1>";
            $s .= "<tr><th colspan=\"2\" align=\"left\"><h2>Database - Modifier Query</h2></td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Database class</nobr></td><td>".get_class($database)."&nbsp;</td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>MDB2 Code</nobr></td><td>".$affectedRows->getCode()."&nbsp;</td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>MDB2 Error</nobr></td><td>".Sitengine_String::html($affectedRows->getMessage())."&nbsp;</td></tr>";
            #$s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Native Code</nobr></td><td>".$this->getNativeErrorCode()."&nbsp;</td></tr>";
            #$s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Native Error</nobr></td><td>".Sitengine_String::html($this->getNativeError())."&nbsp;</td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Query</nobr></td><td>".Sitengine_String::html($query)."&nbsp;</td></tr>";
            $s .= "</table><br />";
        }
        else {
            $s .= "<table width=600 border=1>";
            $s .= "<tr><th colspan=\"2\" align=\"left\"><h2>Database - Modifier Query</h2></td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Database Class</nobr></td><td>".get_class($database)."&nbsp;</td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Affected Rows</nobr></td><td>".$affectedRows."&nbsp;</td></tr>";
            $s .= "<tr><th valign=\"top\" align=\"left\"><nobr>Query</nobr></td><td>".Sitengine_String::html($query)."&nbsp;</td></tr>";
            $s .= "</table><br />";
        }
        
        $s .= '</body>';
        $s .= '</html>';
        print $s;
    }
    */
}

?>