<?php


class ContactController extends AdminController {

	public function run(){
		
		$this->action = Tools::getSuperglobal('action');
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "show")
				$this->showMessage();

			elseif( $this->action == "delete")
				$this->deleteMessage();

		}else{
			$this->smarty->assign('contacts', Contact::getContacts());

			$this->smarty->display('contact.html');
		}	
	}


	public function showMessage(){

		$id_contact = Tools::getSuperglobal('id_contact');
		
		$contact = new Contact($id_contact);
		
		if(isset($contact->id_contact) && $contact->id_contact > 0){
			$contact->has_been_read	= 1;
			$contact->update();
			$this->smarty->assign('contact', $contact);
		}
		else
			Tools::redirect('/admin/index.php?p=contact');
		
		$this->smarty->display('contactShow.html');
	}

	public function deleteMessage(){
		$id_contact = Tools::getSuperglobal('id_contact');
		if(isset($id_contact) && !empty($id_contact)){
			$msg = Db::getInstance()->Delete(_DB_PREFIX_.'contact', array("id_contact" => $id_contact));
			Tools::redirect('/admin/index.php?p=contact');
		}
	}
	
	public function preprocess(){ }

}