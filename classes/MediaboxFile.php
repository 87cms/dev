<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class MediaboxFileCore extends Core {

	protected $table = 'media';	
	protected $identifier = 'id_media';
	
	
	public static function getFilesFromDir($id_directory){
		$sql = 'SELECT * FROM '._DB_PREFIX_.'media WHERE id_directory=:id_directory AND deleted=0 ORDER BY filename '; 
		//.( $this->mimetype ? ' AND mimetype LIKE %'.Tools::cleanSQL($this->mimetype).'%' : '' );
		return Db::getInstance()->Select($sql, array('id_directory' => $id_directory));
	}
	
	public function delete(){
		$images = Db::getInstance()->Select('SELECT id_entity_field FROM '._DB_PREFIX_.'entity_field WHERE raw_value LIKE "%'.Tools::cleanSQL($this->path.$this->filename).'%"');
		if( !$images ){
			@unlink(_MEDIAS_DIR_.$this->path.'large/'.$this->filename);
			@unlink(_MEDIAS_DIR_.$this->path.'medium/'.$this->filename);
			@unlink(_MEDIAS_DIR_.$this->path.'thumb/'.$this->filename);
			@unlink(_MEDIAS_DIR_.$this->path.$this->filename);
		}
		parent::delete();		
	}

}




?>