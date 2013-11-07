(function($) {
	
	var self;
	
	/*
		Mandatory methods. Called when user want to add an element
	*/
	RTckeditor.prototype.displayForm = function(){
		if( $('#richtext .selectedColumn').length > 0 ){
			self.initRTckeditor();
		}
	}
	
	RTckeditor.prototype.initRTckeditor = function(){
		var selectedElement = $('#richtext .selectedColumn');
		var count = $('#richtext textarea').length+1;
		var currentHTML = selectedElement.html();
		selectedElement.html('');
		selectedElement.attr('data-type','ckeditor');
		selectedElement.append('<textarea name="editor' + count + '" id="editor' + count + '" class="RTckeditor">' + currentHTML + '</textarea>');
		
		CKEDITOR.replace( 'editor' + count, {
			extraPlugins : 'autogrow',
			toolbar: [
				{ name: 'document', items : [ 'Source' ] },
				{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
				{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
					'HiddenField' ] },
				{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
				{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
				'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
				{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
				{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
				{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
				{ name: 'colors', items : [ 'TextColor','BGColor' ] },
				
			],
			language: LANG,
			removePlugins : 'resize, elementspath',
			toolbarLocation : 'bottom'
		});
	}
	
	RTckeditor.prototype.save = function(){
		$('#richtext textarea.RTckeditor').each( function(index){
			
			myEditorID = $(this).attr('id');
			
			var val = trim( CKEDITOR.instances[ myEditorID ].getData() );
			val = val.replace(/[\n]/gi, "" );
			val = val.replace(/[\t]/gi, "" );
			
			var container = $(this).parent('div.column');
			container.html(val);
			container.addClass('ckeditor');
				
		});	
	}
	
	function RTckeditor() {
		self = this;
		self.displayForm();
	}
	
	$.extend({
		RTckeditor : function () {
			RTckeditor_object = new RTckeditor();
		}
	});
	

})( jQuery );
 



//$.getScript('elements/ckeditor/ckeditor_compressed.js');