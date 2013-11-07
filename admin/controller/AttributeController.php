<?php


class AttributeController extends AdminController {
	
	
	public function run(){
		
		$this->action = Tools::getSuperglobal('action');
		
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "form" )
				$this->displayForm( (int)Tools::getSuperglobal('id_attribute') );
		
		}else{
			
			$attributesList = Attribute::getAttributesList();
			$this->smarty->assign('attributesList', $attributesList);
			$this->smarty->display('attribute.html');
			
		}	
		
	}
	
	public function preprocess(){

		if( Tools::verifyToken(Tools::getSuperglobal('token')) OR 1==1 ){
			
			
			// Attribute form
			if( Tools::getSuperglobal('submitAttribute') ){
				$attribute = new Attribute(	Tools::getSuperglobal('idAttribute') );
				$attribute->slug = Tools::getSuperglobal('slug');
				$name = array();
				
				foreach( Lang::getLanguages() as $lang )
					$name[ $lang['id_lang'] ] = Tools::getSuperglobal('name#'.$lang['id_lang']);
				$attribute->name = $name;
				
				if( Tools::getSuperglobal('idAttribute') )
					$attribute->update();
				else
					$attribute->add();
					
			}
			
			
			// Attribute value form
			
			if( Tools::getSuperglobal('addLine') ){
				$attribute_value = new AttributeValue();
				$value = array();
				foreach( Lang::getLanguages() as $lang )
					$value[ $lang['id_lang'] ] = '';	
				
				$attribute_value->value = $value;
				$attribute_value->id_attribute = (int)Tools::getSuperglobal('id_attribute');
				$attribute_value->add();				
			}
			
			if( Tools::getSuperglobal('submitValues') ){
				$id_attribute = (int)Tools::getSuperglobal('idAttribute');
				
				foreach( $_POST as $key => $value ){
					
					if( preg_match('/value/', $key) ){
						$empty = 0;
						$explode = explode('#', $key);
						$id_lang = (int)$explode[1];
						$id_attribute_value = ( is_numeric($explode[2]) ? $explode[2] : 0 );
						
						$attribute_value = new AttributeValue( $id_attribute_value );
						$attribute_value->value = array($id_lang => $value);
						$attribute_value->id_attribute = $id_attribute;
						
						if( $id_attribute_value > 0 )
							$attribute_value->update();
						else
							$attribute_value->add();
						
					}
				}

			}
			
			
			
			// Delete attribute value
			if( Tools::getSuperglobal('deleteAttributeValue') ){
				$attribute_value = new AttributeValue( Tools::getSuperglobal('id_attribute_value') );
				$attribute_value->delete();
			}
			
			
			// Delete attribute
			if( Tools::getSuperglobal('deleteAttribute') ){
				$attribute = new Attribute( Tools::getSuperglobal('id_attribute') );
				$attribute->delete();
			}
			
		}
		
	}
	
	public function displayForm($id=0){
		$attribute = new Attribute(	$id );
		$this->smarty->assign('attribute', $attribute);
		$this->smarty->display('attribute_form.html');
	}


}