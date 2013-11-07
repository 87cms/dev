(function($) {
	
	var self;
	
	/*
		Mandatory methods. Called when user want to add an element
	*/
	Medias.prototype.displayForm = function(){
		if( $('#richtext .selectedColumn').length > 0 ){
			
			self.convertIntoData();
			
			$.Mediabox({
				'limit' : 1,
				'random_id' : 'richtext',
				'sizes' : '',
				'filter' : 'gif,jpg,jpeg,png',
				'callback_function' : function(){ self.insertMedia() }
			});
		}
	}
	
	Medias.prototype.insertMedia = function(){
		var selectedElement = $('#richtext .selectedColumn');
		var medias = $.parseJSON( $('#mediaboxrichtext_data').val() );
		
		var img = '<img src="' + medias[0].path + '" style="max-width:100%" data-id="' + medias[0].id_media + '" data-name="' + medias[0].name + '" />';
		selectedElement.html(img);
	}
	
	
	Medias.prototype.convertIntoData = function(){
		var data = new Array();
		$('#richtext .selectedColumn img').each( function(){
			var img = {};
			img.path = $(this).attr('src');
			img.id_media = $(this).attr('data-id');
			img.name = $(this).attr('data-name');
			data.push( img );
		});
		$('#mediaboxrichtext_data').val( JSON.stringify(data) );
	}
	
	
	function Medias() {
		self = this;
		self.displayForm();
	}
	
	$.extend({
		RTmedias : function () {
			var Medias_object = new Medias();
		}
	});
	

})( jQuery );
 



//$.getScript('elements/ckeditor/ckeditor_compressed.js');