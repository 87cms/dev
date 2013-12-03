(function($) {

	var self;
	var container;
	var type_html = new Array();
	var count = 0;
	var selectBox;
	var modelsBox;
	
	function formCreator() {
		self = this;
		self.attachEvents();
		self.selectAttributeForm();
		self.selectModelsForm();
	}
	
	formCreator.prototype.attachEvents = function(){
		$('#fields_list ul li').click( function(){ self.addElement( $(this).attr('id') ) } );	
		$('#submitModel').click( function(){ self.submitModel(); });
		$('.field').on('click', function(){
			$('.field').removeClass('active');
			$(this).addClass('active');
		});
	}
	
	formCreator.prototype.addElement = function(type){
			count = count+1;
			var div = $('<div class="field" data-type="' + type + '" id="line' + count + '"><div class="line_command"></div><div class="left"><p class="title">&nbsp;&nbsp;&nbsp; TYPE : ' + type + '</p></div><div class="right"></div><div class="clear"></div></div');
			
			var prefield = $('#field_model .content').html();
			div.find('.left').append(prefield);
			
			var command = $('<a href="#" class="formcreator_delete_line button_delete">Delete line</a>').click( function(){
				$(this).parent().parent('.field').remove(); return false;
			});
			command.appendTo( div.children('.line_command') );
			var command = $('<a href="#" class="formcreator_position_up button_up">Up</a>').click( function(){
				 if( div.prev().attr('id') !== 'name' ){
					 div.insertBefore( div.prev() );
				 	 $('.field').removeClass('active');
					 div.addClass('active');
				 }
				 return false;
			});
			command.appendTo( div.children('.line_command') );
			var command = $('<a href="#" class="formcreator_position_down button_down">Down</a>').click( function(){
				div.insertAfter( div.next() );
				$('.field').removeClass('active');
				div.addClass('active');
				return false;
			});
			command.appendTo( div.children('.line_command') );
			
			
			/*var params = $('#form_creator_command .content').clone(true);
			params.appendTo( div.children('.left') );*/
            var params = '';
				
			if( type == "inputText" ){
				params = '<p><label>' + lang('Maxlength') + ' : </label> <input type="text" name="maxlength" class="text maxlength" value="255" /></p>';
			}
			else if( type == "inputPassword" ){
				params = ' ';
			}
			else if( type == "inputFile" ){
				params = '<p><label>' + lang('Extensions (empty = no filter)') + ' : </label> <input type="text" name="extensions" value="" class="text" /></p>';
				params += '<p><label>' + lang('Number of file (0 = no limit)') + ' </label> <input type="text" name="number" value="0" length="5" class="text" style="width:50px;" /></p>';				
			}
			else if( type == "inputImage" ){
				/*params = '<p><label>' + lang('Extensions (empty = no filter)') + ' : </label> <input type="text" name="extensions" value="" class="text" /></p>';*/
				params += '<p><label>' + lang('Number of file (0 = no limit)') + ' </label> <input type="text" name="number" value="0" length="5" class="text" style="width:50px;" /></p>';
				params += '<p><label>' + lang('Thumbs size') + ' : </label> Width : <input type="text" name="thumb_width" value="" length="3" class="text" style="width:50px;" />px &nbsp;&nbsp;&nbsp; Height : <input type="text" name="thumb_height" value="" length="3" class="text" style="width:50px;" />px</p>';
				params += '<p><label>' + lang('Medium size') + ' : </label> Width : <input type="text" name="medium_width" value="" length="3" class="text" style="width:50px;" />px &nbsp;&nbsp;&nbsp; Height : <input type="text" name="medium_height" value="" length="3" class="text" style="width:50px;" />px</p>';
				params += '<p><label>' + lang('Large size') + ' : </label> Width : <input type="text" name="large_width" value="" length="3" class="text" style="width:50px;" />px &nbsp;&nbsp;&nbsp; Height : <input type="text" name="large_height" value="" length="3" class="text" style="width:50px;" />px</p>';
			}
			else if( type == "radio" ){
				params = '<p><label>' + lang('Attributes list') + ': </label> ' + selectBox + '</p>';
			}
			else if( type == "checkbox" ){
				params = '<p><label>' + lang('Attributes list') + ': </label> ' + selectBox + '</p>';
			}
			else if( type == "select" ){
				params = '<p><label>' + lang('Attributes list') + ': </label> ' + selectBox + '</p>';
			}
			else if( type == "textarea" ){
				params = ' ';
			}
			else if( type == "linkedEntities" ){
				params = '<p><label>Linked to :</label> ' + modelsBox + '</p>';
				
			}
			else if( type == "richtext" ){
				
			}
			else if( type == "markdown" ){
				
			}
			else if( type == "date" ){
				params = '<p><label>Format : </label> <input type="text" name="format" value="yy-mm-dd" class="text" /></p>';
			}
			
			div.children('.right').html(params);
			div.on('click', function(){
				$('.field').removeClass('active');
				$(this).addClass('active');
			});
			$('.field').removeClass('active');
			div.addClass('active');
			
			$('#entity_fields').append( div );
			
			var objDiv = document.getElementById("content");
			objDiv.scrollTop = objDiv.scrollHeight;
			
	}
	
	formCreator.prototype.submitModel = function(){
		
		var entity = { };
		
		$('#overlay').show();
		$('#overlay_container').html('<img src="images/loader.gif" />');
		$('#overlay_container').show();
		
		if( $('#id_entity_model').val() > 0 )
			entity.id_entity_model = $('#id_entity_model').val();
		else
			entity.id_entity_model = 0;
			
		entity.slug = $('#entity_slug').val();
		entity.templates =  $('#entity_templates').val();
		entity.entities_templates =  $('#entities_templates').val();
		entity.is_hierarchic =  ( $('#entity_hierarchic:checked').length > 0 ? 1 : 0 );
		
		entity.id_parent =  $('#entity_parent').val();
		entity.fields = new Array();
		entity.name = new Array();
		$.each(languages, function(code, lang){
			var entityName = {
				'id_lang' : lang.id_lang,
				'name' : $('input[name="entity_name#' + lang.id_lang + '"]').val()				
			}
			entity.name.push(entityName);
		});
		
		entity.changeforall = 0;
		if( $('#changeforall:checked').length > 0 )
			entity.changeforall = 1;
		
		entity.link_rewrite = new Array();
		entity.meta_title = new Array();
		entity.meta_keywords = new Array();
		entity.meta_description = new Array();
		
		$.each(languages, function(code, lang){
			var link_rewrite = {
				'id_lang' : lang.id_lang,
				'link_rewrite' : $('input[name="link_rewrite#' + lang.id_lang + '"]').val()				
			}
			entity.link_rewrite.push(link_rewrite);
			
			var meta_title = {
				'id_lang' : lang.id_lang,
				'meta_title' : $('input[name="meta_title#' + lang.id_lang + '"]').val()				
			}
			entity.meta_title.push(meta_title);
			
			var meta_keywords = {
				'id_lang' : lang.id_lang,
				'meta_keywords' : $('input[name="meta_keywords#' + lang.id_lang + '"]').val()				
			}
			entity.meta_keywords.push(meta_keywords);
			
			var meta_description = {
				'id_lang' : lang.id_lang,
				'meta_description' : $('input[name="meta_description#' + lang.id_lang + '"]').val()				
			}
			entity.meta_description.push(meta_description);
		});
		
		var count = 0;
		$('.field').each( function(index, element){
			var params = new Array();
			
			var current_field = $(this);
			
			current_field.find('.right').find('input, select').each( function(index, element){
				var inputName = $(this).attr('name');
				array = {
					"name" : inputName,
					"value" : $(this).val()
				};
				params.push( array );
			});
			
			var name = new Array();			
			$.each(languages, function(code, lang){
				var entityName = {
					'id_lang' : lang.id_lang,
					'name' : current_field.find('.left input[name="field_name#' + lang.id_lang + '"]').val()				
				}
				name.push(entityName);
			});
			
			var id_field_model = $(this).children('.left').find('input[name="id_field_model"]').val();
			if( typeof id_field_model == "undefined" )
				id_field_model = '';
			
			entity['fields'][index] = {
				'slug' : $(this).children('.left').find('input[name="slug"]').val(),
				'type' : $(this).attr('data-type'),
				'position' : index+1,
				'params' : params,
				'name' : name,
				'id_field_model' : id_field_model				
			}
			count = count+1;
			
		});
		
		$.ajax({
			url: "index.php",
			type: "POST",
			data: { ajax:1, action : 'addEntityModel', data : JSON.stringify(entity) },
			dataType: "json"
		})
		/*.complete(function() {
			location.href = 'index.php?p=entityModel&added=1';
		});*/
		
	}
	
	formCreator.prototype.threatElements = function(elem){
		if( elem.attr('data-type') )
			self.olk();
		
	}
	
	formCreator.prototype.selectAttributeForm = function(){
		$.ajax({
			url: "index.php?ajax=1",
			type: "POST",
			data: { ajax:1, action : 'getAttributesList'},
			dataType: "json"
		})
		.done(function(data) {
			
			selectBox = '<select name="attributes_list">';
			$.each(data, function(index, attribute){
				selectBox += '<option value="' + attribute.id_attribute + '">' + attribute.slug + '</option>';				
			});	
			selectBox += '</select>';	
			
		});
		
	}
	
	formCreator.prototype.selectModelsForm = function(){
		$.ajax({
			url: "index.php?ajax=1",
			type: "POST",
			data: { ajax:1, action : 'getModelsList'},
			dataType: "json"
		})
		.done(function(data) {
			
			modelsBox = '<select name="models_list">';
			$.each(data, function(index, model){
				modelsBox += '<option value="' + model.id_entity_model + '">' + model.slug + '</option>';				
			});	
			modelsBox += '</select>';	
			
		});	
		
	}
	
	$.fn.formCreator = function () {
		
		formCreator_object = new formCreator();
		
	}
	
 })( jQuery );
 
