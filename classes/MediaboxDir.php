<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class MediaboxDirCore extends Core {

	protected $table = 'media_directory';	
	protected $identifier = 'id_directory';
	
	
	public static function getDirectories($id_parent){
		return Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'media_directory WHERE deleted=0 AND id_parent='.(int)$id_parent.' ORDER BY dirname');
	}	
	
	
}


?>