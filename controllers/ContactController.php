<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class ContactControllerCore extends FrontController {

	public function run(){

		if( Tools::getValue("sendContactForm") )
			$this->sendMessage();
		
		if( Tools::getValue('action') == "ok" )
			$this->smarty->assign('success', 1);
			
		$this->setSEO();
		$this->display();
	}
	
	public function sendMessage(){
		
		$spam = Tools::getValue('email');
		if( empty($spam) ){
			
			$contact = new Contact();
			$contact->contact_from = htmlentities(Tools::getValue('courriel'));
			$contact->subject = 'Contact';
			
			$message = '
				
				Prénom : '.htmlentities(Tools::getValue('forname')).'<br />
				Nom : '.htmlentities(Tools::getValue('name')).'<br />
				Entreprise : '.htmlentities(Tools::getValue('company')).'<br />
				Email : '.htmlentities(Tools::getValue('courriel')).'<br />
				Téléphone : '.htmlentities(Tools::getValue('phone')).'<br />
				<br />
				<br />
				'.nl2br(htmlentities(Tools::getValue('message'))).'
			';
			
			$contact->message = $message;
			
			Tools::redirect('/'.Lang::getLangCode($this->cookie->id_lang).'/contact/ok');
			
		}
	}

	/**
	* The classic display method
	*/
	public function display(){
		$tpls = Contact::getTemplates();
		$this->sendTemplatesToSmarty($tpls);		
	}
	
	/**
	* Set SEO elements as meta elements, or canonical
	*/
	public function setSEO(){
		$seo = array(
			'meta_title' => 'Contact',
			'meta_description' => '',			
		);
		$sitename = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="sitename"');
		$slogan = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="slogan"');
		
		$this->smarty->assign(array(
			'seo' => $seo,
			'slogan' => $slogan,
			'sitename' => $sitename
		));
		$this->smarty->assign('seo', $seo);			
	}
	
	
}