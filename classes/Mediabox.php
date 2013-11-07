<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */


class MediaboxCore extends Core {
	
	public $mimetype;
	
	function __construct($mimetype = NULL){
		$this->mimetype = $mimetype;	
	}

	/**
	* Get directory content
	* @param Int $id_directory
	* @return Array
	*/
	public function getDirectoryContent($id_directory){
		$files = array();
		$files['folders'] = MediaboxDir::getDirectories($id_directory);
		$files['files'] = MediaboxFile::getFilesFromDir($id_directory);
		return $files;
	}
	
	
	/**
	* Get the complete tree from the id_parent (0 = complete tree)
	* @param Int $id_parent
	* @return Array
	*/
	public function getDirectoryTree($id_parent=0){
		$directories = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'media_directory WHERE id_parent=:id_parent AND deleted=0 ORDER BY dirname ASC', array('id_parent' => $id_parent));
		foreach( $directories as &$directory ){
			$directory['children'] = $this->getDirectoryTree( $directory['id_directory'] );
		}
		return $directories;
	}
	
	
	/**
	* Create a new directory
	* @param Array $data Array of data | array = ('id_parent' => Integer, 'dirname' => String, 'id_directory' => 0);
	*/
	public function createDirectory($data){
		$directory = new MediaboxDir($data['id_directory']);
		$directory->dirname = $data['dirname'];
		$directory->id_parent = $data['id_parent'];
		if( $data['id_directory'] > 0 )
			$directory->update();	
		else
			$directory->add();
	}
	
	
	/**
	* Update directory
	* @param Array $data Array of data | array = ('id_parent' => Integer, 'dirname' => String, 'id_directory' => Integer);
	*/
	public function updateDirectory($data){
		$this->createDirectory($data);	
	}
	
	
	/**
	* Delete a directory
	* @param Int $id_directory
	*/
	public function deleteDirectory($id_directory){
		$directory = new MediaboxDir($id_directory);
		$directory->delete();
	}
	
	
	/*
	* Manage medias
	*/
	
	/**
	* Create a new media
	* @param Array $data Array of data | array = ('id_directory' => Integer, 'filename' => String, 'path' => String, "mimetype" => String, 'id_media' => 0);
	* @return Int Id of new media
	*/
	public function addMedia($data){
		$media = new MediaboxFile($data['id_media']);
		$media->filename = $data['filename'];
		$media->mimetype = $data['mimetype'];
		$media->path = $data['path'];
		$media->id_directory = $data['id_directory'];
		if( $data['id_media'] > 0 )
			$media->update();	
		else
			$media->add();	
		return $media->id_media;	
	}
	
	/**
	* Update media
	* @param Array $data Array of data | array = ('id_directory' => Integer, 'filename' => String, 'path' => String, "mimetype" => String, 'id_media' => Integer);
	*/
	public function updateMedia($data){
		$this->addMedia($data);	
	}
	
	/**
	* Delete a media
	* @param Int $id_media
	*/
	public function deleteMedia($id_media){
		$media = new MediaboxFile($id_media);
		$media->delete();			
	}
	
	
	/**
	* Upload
	* Les images sont stockés dans le dossier medias sous la forme /ANNEE/MOIS/IDIMAGE-SIZE-IMAGENAME
	*
	*/
	public static function getImagesSizes(){
		$sizes = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'image_size');
		$out = array();
		foreach( $sizes as $size ){
			$out[ $size['name'] ] = array(
				'max_width' => $size['width'],
				'max_height' => $size['height'],
				'jpeg_quality' => 100
			);			
		}
		return $out;
	}
	
	/**
	* Move files in a different folder
	* @param Array $ids_files ID of files to move
	* @param Int $id_directory_to Destination directory Id 
	*/
	public function moveFiles($ids_files, $id_directory_to){
		if( count($ids_files) > 0 ){
			foreach( $ids_files as $id_file )
				Db::getInstance()->UpdateDB(_DB_PREFIX_.'media', array('id_directory' => (int)$id_directory_to), array('id_media'=>$id_file));
		}
	}
	
	/**
	* Move directories in a different folder
	* @param Array $ids_files ID of files to move
	* @param Int $id_directory_to Destination directory Id 
	*/
	public function moveDirectories($ids_directories, $id_directory_to){
		if( count($ids_directories) > 0 ){
			foreach( $ids_directories as $id_dir ){
				if( $id_dir !== $id_directory_to )
					Db::getInstance()->UpdateDB(_DB_PREFIX_.'media_directory', array('id_parent' => (int)$id_directory_to), array('id_directory'=>$id_dir));
			}
		}
	}
	
}




?>