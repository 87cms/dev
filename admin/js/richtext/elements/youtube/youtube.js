(function($) {
	
	var self;
	
	Youtube.prototype.displayForm = function(){
		
		$('#overlay_container').load('js/richtext/elements/youtube/' + _LANG_ + '_youtube.html?uid' + uniqueId(), function(response, status, xhr) {
			
			if (status == "error") {
				$('#overlay_container').load('js/richtext/elements/youtube/en_youtube.html?uid' + uniqueId(), function(response, status, xhr) {
					self.initForm();
					displayOverlay();
				});
			}else{
				self.initForm();
				displayOverlay();
			}
		});		
	}
	
	Youtube.prototype.initForm = function(){
		$('#overlay_container #youtube_button_add').on('click', function(){
			var selectedElement = $('#richtext .selectedColumn');
			var selectedElementWidth = selectedElement.width();
			var iframe = $('#youtube_input').val();
			iframe = $(iframe);
			var iframeWidth = iframe.attr('width');
			var iframeHeight = iframe.attr('height');
			
			if( iframeWidth > selectedElementWidth  ){
				iframeWidth = "100%";
				iframeHeight = selectedElementWidth*iframeHeight/iframeWidth;
				iframe.attr('width', iframeWidth).attr('height', iframeHeight);
			}
			
			selectedElement.html('');
			selectedElement.append(iframe);
			selectedElement.height( iframe.height() );
			hideOverlay();
		});
	}
	
	function Youtube() {
		self = this;
		self.displayForm();
	}
	
	$.extend({
		RTyoutube : function () {
			var Youtube_object = new Youtube();
		}
	});
	

})( jQuery );
 



//$.getScript('elements/ckeditor/ckeditor_compressed.js');