<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class DbCore extends Core {
	
	protected static $sql;
	
	public static function getInstance(){
		if( !isset(self::$sql) )
			self::$sql = new MySQL();
		return self::$sql;
	}	
	
}

?>