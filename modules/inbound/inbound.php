<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Modules
 */

class Inbound extends Module implements ModuleInterface {
	
	public $name = "Inbound";
	public $description = "Collecte & export d'adresses emails";
	public $menu = "Newsletter";
	public $slug = "inbound";
	
	public $hook_name = "HOOK_INBOUND_COLLECTOR";
	public $method_name = "displayInboundCollector";
	
	public function start(){
			
	}	
	
	public function displayAdmin(){
		
		parent::displayAdmin();	
		
		$inbounds = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'inbound');
		$content = '';
		foreach( $inbounds as $inbound ){
			$content .= $inbound['email'].";\r\n";				
		}
		$name = uniqid();
		$fp = fopen(_ABSOLUTE_PATH_.'/tmp/inbound'.$name.'.csv', 'w+');
		fwrite($fp, $content);
		fclose($fp);
		
		$this->smarty->assign('linktocsv', '/tmp/inbound'.$name.'.csv');
		$this->smarty->display('../modules/inbound/admin.html');
	}
	
	
	public function installModule(){
		parent::installModule();
		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'inbound` (
			  `id_inbound` int(11) NOT NULL AUTO_INCREMENT,
			  `email` varchar(255) NOT NULL,
			  `date_add` datetime NOT NULL,
			  PRIMARY KEY (`id_inbound`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
		';	
		Db::getInstance()->query($sql);
	}
	
	public function displayInboundCollector(){
		
		$this->initController();
		
		if( Tools::getValue('submitInbound') && filter_var(Tools::getValue('courriel'), FILTER_VALIDATE_EMAIL) ){
			
			$inDB = Db::getInstance()->Select('SELECT id_inbound FROM '._DB_PREFIX_.'inbound WHERE email=:email', array('email'=>Tools::getValue('courriel')));
			if( !$inDB ){
				Db::getInstance()->Insert( _DB_PREFIX_.'inbound',
				array(
					'email' => Tools::getValue('courriel'),
					'date_add' => date('Y/m/d h:i:s')
				));
			}
			
		}
		
		elseif( Tools::getValue('deleteInbound') && Tools::getValue('iid') ){
			
			$inbounds = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'inbound');
			$id = 0;
			foreach( $inbounds as $inbound ){
				if( md5($inbound['email'].$inbound['id_inbound']) == Tools::getValue('iid') )
					$id = $inbound['id_inbound'];				
			}
			if( $id > 0 )
				Db::getInstance()->Delete( _DB_PREFIX_.'inbound', array('id_inbound' => $id) );
		}
					
		$this->smarty->display('modules/inbound/inbound.html');
	
	}
	
}




