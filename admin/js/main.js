/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */
 
$(document).ready( function(){
	addDefaultParent();	
	$('#entity_parent .parents').click( function(){ addDefaultParent() } );		
	$('.return_ok').fadeOut(5000);				
});

function displayOverlay(){
	$('#overlay').show();
	$('#overlay_container').show();
}

function hideOverlay(){
	$('#overlay').hide();
	$('#overlay_container').hide();
}

function uniqueId() { 
	var date = new Date();
	return date.getTime(); 
}; 

function autocompleteSEO(){
	
	
}

function addDefaultParent(){
	$('#default_parent option').remove();
	$('#entity_parent .parents:checked').each( function(){
		var id = $(this).val();
		var name = $(this).next('span').html();
		var option = $('<option></option>').text(name).val(id);
		
		if( $('#id_default_parent').val() == id )
			option.attr('selected','selected');
		
		option.appendTo('#default_parent');
	});
}

function addEntity(stay){
	var entity = { };
	
	$('#overlay').show();
	$('#overlay_container').html('<img src="images/loader.gif" />');
	$('#overlay_container').show();
	
	entity.fields = new Array();
	
	entity.id_entity = $('#id_entity').val();
	entity.link_rewrite = new Array();
	entity.meta_title = new Array();
	entity.meta_keywords = new Array();
	entity.meta_description = new Array();
	entity.state = $('select[name="state"]').val();
	entity.templates = $('#entity_templates').val();
	entity.id_entity_model = $('#id_entity_model').val();
	entity.id_default_parent = $('#default_parent').val();
	
	entity.parents = new Array();
	
	$('#entity_parent').find('.parents:checked').each( function(){
		entity.parents.push( $(this).val() );
	});
	
	$.each(languages, function(code, lang){
		
		var link_rewrite = {
			'id_lang' : lang.id_lang,
			'link_rewrite' : $('input[name="link_rewrite#' + lang.id_lang + '"]').val()				
		}
		entity.link_rewrite.push(link_rewrite);
		
		var meta_title = {
			'id_lang' : lang.id_lang,
			'meta_title' : escape($('input[name="meta_title#' + lang.id_lang + '"]').val())
		}
		entity.meta_title.push(meta_title);
		
		var meta_keywords = {
			'id_lang' : lang.id_lang,
			'meta_keywords' : $('input[name="meta_keywords#' + lang.id_lang + '"]').val()				
		}
		entity.meta_keywords.push(meta_keywords);
		
		var meta_description = {
			'id_lang' : lang.id_lang,
			'meta_description' : escape($('input[name="meta_description#' + lang.id_lang + '"]').val())				
		}
		entity.meta_description.push(meta_description);
		
	});
	
	$('#entity_fields_list .form_line').each( function(){
		
		var formatted_field = new Array();
		
		if( $(this).attr('data-type') == "inputText"  ){
			 
			 var type = $(this).attr('data-type');
			 var d = $(this).find('input').first().attr('name').split('#');
			 var id_field_model = d[0];
			 
			 var values = new Array();
			 
			 $(this).find('input').each( function(){ 
				var d = $(this).attr('name').split('#');
				var id_lang = d[1];
				values.push({
					'id_lang' : id_lang,
					'value' : escape($(this).val())
				});				
			});
			
			formatted_field = {
				'type' : 'inputText',
				'id_field_model' : id_field_model,				
				'values' : values
			};

		}
		
		else if( $(this).attr('data-type') == "inputPassword" ){
			var type = $(this).attr('data-type');
			var id_field_model = $(this).find('input').attr('name');
			
			var values = new Array();
			
			formatted_field = {
				'type' : 'inputPassword',
				'id_field_model' : id_field_model,				
				'raw_value' : $(this).find('input').val()
			};
		}
		
		else if( $(this).attr('data-type') == "radio" || $(this).attr('data-type') == "select" ){
			
			var type = $(this).attr('data-type');
			if( $(this).attr('data-type') == "radio" )
				var field = $(this).find('input:checked');
			else
				var field = $(this).find('select');
			
			var id_field_model = field.attr('name');
			//console.log(field);
			formatted_field = {
				'type' : $(this).attr('data-type'),
				'id_field_model' : id_field_model,
				'raw_value' : field.val()
			};
			
		}
		else if( $(this).attr('data-type') == "textarea" ){
			
			var d = $(this).find('textarea').first().attr('name').split('#');
			var id_field_model = d[0];
			
			var values = new Array();
			
			$(this).find('textarea').each( function(){ 
				var d = $(this).attr('name').split('#');
				var id_field_model = d[0];
				var id_lang = d[1];
				var val = trim( CKEDITOR.instances[ d[0] + '-' + d[1] ].getData() );
				val = val.replace(/[\n]/gi, "" );
				val = val.replace(/[\t]/gi, "" );
				values.push({
					'id_lang' : id_lang,
					'value' : escape(val)
				});
			});
			
			formatted_field = {
				'type' : 'textarea',
				'id_field_model' : id_field_model,				
				'values' : values
			};
			
		}
		else if( $(this).attr('data-type') == "checkbox" ){
			
			var id_field_model = $(this).attr('data-name');
			//console.log(id_field_model+"aaa");
			var raw_value = ",";
			$(this).find('input[type="checkbox"]:checked').each( function(){ 				
				raw_value += $(this).val() + ',';								
			});
			
			formatted_field = {
				'type' : 'checkbox',
				'id_field_model' : id_field_model,
				'raw_value' : raw_value,
			};
			
		}
		
		else if( $(this).attr('data-type') == "linkedEntities" ){
			
			var id_field_model = $(this).find("select").attr('name');
			
			var raw_value = "";
			$(this).find('option:selected').each( function(){ 				
				raw_value += $(this).val() + ',';								
			});
			
			formatted_field = {
				'type' : 'linkedEntities',
				'id_field_model' : id_field_model,
				'raw_value' : raw_value
			};
			
		}
		
		else if( $(this).attr('data-type') == "richtext" ){
			
			var html = richtextObject.save();
			var id_field_model = $(this).attr('data-id-field-model');
			var html = $('#richtext').html();
			var values = new Array();
			
			values.push({
				'id_lang' : $(this).attr('data-lang'),
				'value' : escape(html)
			});				
			
			formatted_field = {
				'type' : $(this).attr('data-type'),
				'id_field_model' : id_field_model,
				'values' : values
			};
		}
		
		else if( $(this).attr('data-type') == "inputImage" || $(this).attr('data-type') == "inputFile" ){

			var field = $(this).find('input[type="hidden"]');
			var id_field_model = field.attr('name');
			
			formatted_field = {
				'type' : $(this).attr('data-type'),
				'id_field_model' : id_field_model,
				'raw_value' : escape(field.val()) // Hack
			};
			
		}
		
		else if( $(this).attr('data-type') == "date" ){
			
			var id_field_model = $(this).attr('data-name');
			
			var raw_value = $(this).find('input[type="text"]').val();
			
			formatted_field = {
				'type' : 'date',
				'id_field_model' : id_field_model,
				'raw_value' : raw_value,
			};
			
		}
		
		else if( $(this).attr('data-type') == "markdown" ){
			
			var id_field_model = $(this).find('textarea').first().attr('data-id-model');
			
			var values = new Array();
			
			$(this).find('textarea').each( function(){ 
				
				var id_lang = $(this).attr('data-lang');
				val = $(this).val();
				
				values.push({
					'id_lang' : id_lang,
					'value' : escape(val)
				});
			});
			
			formatted_field = {
				'type' : 'markdown',
				'id_field_model' : id_field_model,				
				'values' : values
			};
		}
		
		entity.fields.push( formatted_field );
			
	});
	
	/*console.log(entity);
	return false;*/
	
	$.ajax({
		url: "index.php",
		type: "POST",
		data: { ajax:1, action : 'addEntity', data : JSON.stringify(entity) },
		dataType: "json"
	})
	.complete(function(ret) {
		if( stay )
			location.reload();	
		else
			location.href = 'index.php?p=entity&id_entity_model=' + entity.id_entity_model + '&added=1&sort=id_entity-asc';		
	});
	
	
}


function trim (myString)
{
	return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
} 