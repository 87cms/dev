(function($) {

	var self;
	var container;
	var container_width = 0;
	
	richText.prototype.initialize = function() {
	
	}
	
	richText.prototype.addLine = function(){
		var line = $('<div></div>').addClass('row-fluid line');
		container.append(line);
		self.attachLinesClickHandler();	
	}
	
	richText.prototype.addColumn = function(){
		var column = $('<div></div>').addClass('column');
		
		// Clear hack
		container.children('.selectedLine').children('.clear').remove();
		
		container.children('.selectedLine').append(column);
		
		container.children('.selectedLine').append('<div class="clear"></div>');
		
		self.setColumnsWidth();
		self.attachColumnsClickHandler();
	}
	
	richText.prototype.initContent = function(){
		container.children('.line').each( function(){
			$(this).children('.column').each( function(){
				var grid_size = $(this).attr('data-width');
				$(this).outerWidth( container_width*grid_size/12 + 'px' );
							
			});
			
			$(this).addClass('selectedLine');
			self.attachColumnsClickHandler();
			$(this).removeClass('selectedLine');
			
		});
	}
	
	richText.prototype.setColumnsWidth = function(){
		var nb_child = container.children('.selectedLine').children('.column').length;
		
		var columnPaddingLeft = parseInt($('.column').css('padding-left').replace('px',''));
		var columnPaddingRight = parseInt($('.column').css('padding-right').replace('px',''));
		
		var width = Math.round( (container_width/nb_child)  );
		container.children('.selectedLine').children('.column').outerWidth(width);
		
		container.children('.selectedLine').children('.column').removeClass('last');
		container.children('.selectedLine').children('.column').removeClass('first');
		container.children('.selectedLine').children('.column:last').addClass('last');
		container.children('.selectedLine').children('.column:first').addClass('first');
		
		/*-- Bootstrap --*/
		container.children('.selectedLine').children('.column').attr('data-width', 12/nb_child);
		container.children('.selectedLine').children('.column').removeClass('span1 span2 span3 span4 span5 span6 span7 span8 span9 span10 span11 span12').addClass('span' + (12/nb_child));
		
		self.attachColumnsClickHandler();
	}
	
	richText.prototype.setColumnWidth = function(selected_column_width){
		var total = 0;
		container.children('.selectedLine').children('.column').each( function(){
			total += parseInt($(this).attr('data-width'));
		});
		/*if( total > 12 )
			alert('The width number must be under 12.');
		else{*/
			container.children('.selectedLine').children('.selectedColumn').attr('data-width', selected_column_width);
			container.children('.selectedLine').children('.selectedColumn').outerWidth( container_width*selected_column_width/12 + 'px' );
		//}
		self.updateColumnHeight();
	}
	
	richText.prototype.updateColumnHeight = function(){
		var maxh = 0;
		container.children('.selectedLine').children('.column').each( function(){
			if( $(this).height() > maxh )
				maxh = $(this).height();
		});
		container.children('.selectedLine').children('.column').css('min_height', maxh );
	}
	
	richText.prototype.attachColumnsClickHandler = function(){
		container.children('.selectedLine').children('.column').each( function(){
			$(this).off().on('click', function(){				
				
				// Reset line event 
				self.attachLinesClickHandler();
				container.children('.selectedLine').off();
				
				container.children('.selectedLine').children('.column').removeClass('selectedColumn');				
				var width = $(this).width;
				$(this).css('width', (width-2) + 'px')
				$(this).addClass('selectedColumn');
				
				if( !$(this).attr('data-width') )
					$(this).attr('data-width','12');
					
				$('#cmd_column #column_size input').val( $(this).attr('data-width') );
				
				var line_position = $(this).offset();
				line_position_top = line_position.top - $('#richtext').offset().top;
				line_position_left = line_position.left - $('#richtext').offset().left;
				$('#cmd_column').css({
					'margin-top' : line_position_top-16 + 'px',
					'margin-left' : (line_position_left + 5) + 'px',
					'display' : 'block',
					'z-index' : '9'
				});
				
				if( container.children('.selectedLine').children('.column').length > 1 ){
					
					var leftBrother = $(".selectedColumn").prev('.column');
					var rightBrother = $(".selectedColumn").next('.column');
					var leftBrotherWidth = leftBrother.width();
					var rightBrotherWidth = rightBrother.width();
					
					var handles = 'e, w';
					if( $(".selectedColumn").hasClass('first') )
						handles = 'e';
					else if( $(".selectedColumn").hasClass('last') ) 
						handles = 'w';
									
				}
			});
		});
	}
	
	richText.prototype.attachLinesClickHandler = function(){
		$('#cmd_column').hide();
		container.children('.line').each( function(){
			$(this).off().on('click', function(){				
				
				$('#cmd_column').hide();
				$('.selectedColumn').removeClass('selectedColumn');
				
				container.children('.line').removeClass('selectedLine');				
				$(this).addClass('selectedLine');
				
				var line_position = $(this).offset();
				line_position_top = line_position.top - $('#richtext').offset().top;
				$('#cmd_line').css({
					'margin-top' : line_position_top+2 + 'px',
					'left' : '-16px',
					'display' : 'block'
				});
				
				
				
			});
		});
	}
	
	richText.prototype.initElements = function(){
		$('#elementsList li').each( function(){
			var elementName = $(this).attr('data-name');
			$(this).on('click', function(){
				var selectedColumn = $('#richtext .selectedColumn');
				if( selectedColumn )
					eval('$.RT' + elementName + '();');
			});

		});		
	}
	
	richText.prototype.emptyColumn = function(){
		var selectedColumn = $('#richtext .selectedColumn');
		if( confirm(_MESSAGE_EMPTY_COLUMN_) )
			selectedColumn.html('');
	}
	
	richText.prototype.deleteColumn = function(){
		var selectedColumn = $('#richtext .selectedColumn');
		if( confirm(_MESSAGE_DELETE_COLUMN_) ){
			$('#cmd_column').hide();
			selectedColumn.remove();
			self.setColumnsWidth();	
		}
	}
	
	richText.prototype.deleteLine = function(){
		var selectedLine = $('#richtext .selectedLine');
		if( confirm(_MESSAGE_DELETE_COLUMN_) ){
			selectedLine.remove();	
		}
	}
	
	richText.prototype.setCmdEvents = function(){
		$('#cmd_add_line').on( 'click', function(){ self.addLine(); return false; } );
		
		$('#cmd_add_column').on( 'click', function(){ self.addColumn(); return false;  } );	
		$('#cmd_delete_line').on( 'click', function(){ self.deleteLine(); return false;  } );	
		
		$('#cmd_set_column_width').on( 'click', function(){ self.setColumnWidth(); return false;  } );	
		$('#cmd_empty_column').on( 'click', function(){ self.emptyColumn(); return false;  } );	
		$('#cmd_delete_column').on( 'click', function(){ self.deleteColumn(); return false;  } );
		
		$('#column_size input').on( 'change', function(){ self.setColumnWidth( $(this).val() ); } );	
		
		$('#saverichtext').on( 'click', function(){ self.save(); return false;  } );	
			
	}
	
	richText.prototype.save = function(){
		$('#elementsList li').each( function(){
			var elementName = $(this).attr('data-name');
			if( typeof elementName !== 'undefined' ) {
				if( typeof(  window['RT' + elementName + '_object'] ) !== 'undefined'  ){
					if( typeof  window['RT' + elementName + '_object'].save === 'function')
						eval('RT' + elementName + '_object.save();');			
				}
			}
		});	
		
		$('#richtext div').removeAttr('style').removeClass('selectedColumn selectedLine');
		return $('#richtext').html();
	}
	
	function richText() {
		self = this;
		
		container = $("#richtext");
		container_width = container.width();		
		container.css('width', container_width+2);
		
		self.setCmdEvents();		
		self.initElements();
		self.attachLinesClickHandler();
		self.attachColumnsClickHandler();
		self.initContent();
	}
	
	$.fn.richText = function () {
		return new richText();
	}
	

 })( jQuery );
 
