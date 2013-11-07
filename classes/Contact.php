<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class ContactCore extends Core {
	
	protected $table = "contact";	
	protected $identifier = 'id_contact';
	
	public $contact_to;
	public $contact_from;
	public $subject;
	public $message;
	
	public function send(){
		$this->sendEmail();
		$this->add();
		return true;
	}

	public function sendEmail(){

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: '._DOMAIN_.' <no-reply@'._DOMAIN_.'>' . "\r\n";
		
		$raw = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="contact_form_emails"');
		
		$this->contact_to = $raw;
		
		$emails = explode(',', str_replace(' ', '', $raw));
		
		foreach( $emails as $email ){
			if( filter_var($email, FILTER_VALIDATE_EMAIL) )
				mail($email, $this->subject, $this->message, $headers);
		}
		
		return true;
	}

	
	public static function getContacts(){
		return Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'contact ORDER BY id_contact DESC');
	}
	
	public static function getTemplates(){
		return Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="contact_form_templates"');
	}
	
	
	
}


