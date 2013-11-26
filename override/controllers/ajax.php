<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class AjaxController extends Core {
	
	function __construct(){
		
		$this->cookie = new Cookie();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		$cookie = $this->cookie;
		
	}
	
	public function start(){
	
	}
	
}