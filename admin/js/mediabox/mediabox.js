var MEDIABOX_CONFIRM_DELETE_FILE = 'Are you sure to delete this file ?';
var MEDIABOX_CONFIRM_DELETE_FOLDER = 'Are you sure to delete this folder and all of its content ?';
var MEDIABOX_CONFIRM_MOVE_FILE = 'Are you sure to move this file ?';
var MEDIABOX_REACH_FILE_LIMIT = "You can only add %n% files to your selection.";
var MEDIABOX_NONOK_EXT = "You cannot send this type of file.";

(function($) {
	
	var self;
	var multiple;
	var medias;
	var id_current_directory;
	var id_directory_tree;
	
	var settings = {
		'limit' : 999,
		'random_id' : '',
		'callback_function' : '',
		'sizes' : new Array(),
		'filter' : ''
	}
	
	
	Mediabox.prototype.open = function(){
		$('#mediabox, #overlay').show();		
	}
	
	Mediabox.prototype.close = function(){
		$('#mediabox, #overlay').hide();		
	}
	
	Mediabox.prototype.getDirectoryTree = function(id_parent,callback){
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "getDirectoryTree", id_parent : id_parent },
			dataType : 'json'
		}).done(function( json ) {
			
			if( !json || json.length == 0 )
				return false;

			callback(json);
			
		});			
	}
	
	Mediabox.prototype.initMoveBox = function(){
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "getDirectoryTree" },
			dataType : 'json'
		}).done(function( json ) {
			self.setOptionIntoMoveBox(json, 0);
			$('#moveBox').on('change', function(){ 
				if( confirm(MEDIABOX_CONFIRM_MOVE_FILE) ){
					
					
					if( $(".moveFile:checked").length > 0 ){
						var ids_files = new Array();
						$(".moveFile:checked").each( function() {
							ids_files.push( $(this).val() );
							$(".moveFile:checked").parent().parent().fadeOut(500, function(){ $(".moveFile:checked").parent().parent().remove() });
						});
						self.moveFiles(ids_files, $('#moveBox').val() );							
					}
					
					if( $(".moveDirectory:checked").length > 0 ){
						var ids_directories = new Array();
						$(".moveDirectory:checked").each( function() {
							ids_directories.push( $(this).val() );
							$(".moveDirectory:checked").parent().parent().fadeOut(500, function(){ $(".moveFile:checked").parent().parent().remove() });
						});
						self.moveDirectories(ids_directories, $('#moveBox').val() );							
					}
					
					
					$('#moveBox').attr('disabled','disabled');
					$('#moveBox').prop('selectedIndex',0);
				}
			});
		});	
	}
	
	Mediabox.prototype.setOptionIntoMoveBox = function(json, iteration){
		$.each( json, function(i, folder){
			
			var nbsp = "&nbsp;&nbsp;&nbsp;";
			for( i=0; i<iteration; i++ )
				nbsp += "&nbsp;&nbsp;&nbsp;";
			
			var option = $('<option value="' + folder.id_directory + '" >' + nbsp + folder.dirname + '</option>');				
			$('#moveBox').append(option);
			if( folder.children ){
				if( folder.children.length > 0 )
					self.setOptionIntoMoveBox( folder.children, iteration+1 );
			}
		});
	}
	
	Mediabox.prototype.moveFiles = function( ids_files, id_directory_to ){
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "moveFiles", files : JSON.stringify(ids_files), id_directory_to : id_directory_to },
			dataType : 'json'
		})
	}
	
	Mediabox.prototype.moveDirectories = function( ids_directories, id_directory_to ){
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "moveDirectories", directories : JSON.stringify(ids_directories), id_directory_to : id_directory_to },
			dataType : 'json'
		})
	}
	
	Mediabox.prototype.mountDirectoryTree = function(folder){
		$.each( json, function(folder, i){
			var li = $('<li id="folder' + folder.id_directory + '"></li>')							
						.html(folder.name)
						.off().click( function(){ 
							self.getDirectoryContent(folder.id_directory);							
						});
			if( $('#folder' + id_parent + ' > ul').length < 1 )
				$('#folder' + id_parent).append('<ul></ul>');
			
			$('#folder' + id_parent + ' > ul').append(li);
			
			if( folder.children.length > 0 )
				self.mountDirectoryTree( folder.children );
				
		});
	}
	
	Mediabox.prototype.runEditFolderName = function(id_directory, id_parent){
		var folder_name = $('#changeName' + id_directory).prev('.name').children('span').text();
		var elem = $('<input type="text" class="text" name="edit_folder_' + id_directory + '" value="' + folder_name + '" />');
		$('#changeName' + id_directory).prev('.name').children('span').remove();
		$('#changeName' + id_directory).prev('.name').append( elem );
		$('#changeName' + id_directory).prev('.name').off();
		var ok_button = $('<a href="#"><img src="images/iconic/gray_dark/check_16x13.png" /></a>').off().on('click', function(){
			var name = elem.val();
			self.updateDirectoryName(name, id_directory, id_parent);
		});
		$('#changeName' + id_directory).prev('.name').append(ok_button);
		
		$('#changeName' + id_directory).remove();
	}
	
	
	Mediabox.prototype.updateDirectoryName = function( name, id_directory, id_parent ){
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "updateDirectoryName", id_directory : id_directory, name : name }			
		}).complete(function( ) {
			self.getDirectoryContent(id_parent);	
		});
	}
	
	Mediabox.prototype.getDirectoryContent = function(id_directory){
		var id_parent = id_directory;
		
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "getDirectoryContent", id_directory : id_directory },
			dataType : 'json'
		}).done(function( json ) {
			
			id_current_directory = id_directory;
			if( id_current_directory > 0 && id_directory_tree[id_directory_tree.length-1] !== id_current_directory )
				id_directory_tree.push(id_current_directory);
			
			$('#directory_content').html('');
			var folders = json.folders;
			$.each( folders, function(z, folder){
				var html = '<img src="/admin/images/iconic/gray_dark/folder_fill_24x24.png" class="icon">' +
							'<p class="cmd_button"></p>' +
							'<p class="name"><span>' + folder.dirname + '</span></p>' +
							'<a href="#" id="changeName' + folder.id_directory + '" class="edit_folder_name"><img src="images/iconic/gray_dark/pen_alt_fill_12x12.png" /></a>' + 
							'<p class="clear" style="margin:0"></p>';							
				var li = $('<li class="folder" id="folder' + folder.id_directory + '"></li>')							
						.html(html);

				li.find('.name').off().on('click', function(){ 
					self.getDirectoryContent(folder.id_directory);							
				});
				
				li.find('.edit_folder_name').off().click( function(){
					self.runEditFolderName(folder.id_directory, id_parent);
					return false;
				});
				
				var button_delete = $('<a class="cmd_button_delete" title="Delete"></a>').off().click(function(){ 
					self.deleteFolder(folder.id_directory);
				}).appendTo( li.find('.cmd_button') );
				
				var checkbox = $('<input type="checkbox" name="move" class="moveDirectory" value="' + folder.id_directory + '" />')
					.off().on('click', function(){
						$('#moveBox').removeAttr('disabled');
					})
					.appendTo( li.find('.cmd_button') );
				
				$('#directory_content').append(li);
			});
			
			var files = json.files;
			$.each( files, function(z, file){
				
				// Images				
				if( file.mimetype.match( new RegExp("image","g") ) ){
					
					var html = '<img src="/medias' + file.path + 'admin/' + file.filename + '" class="image">' +
								'<p class="cmd_button">' +
									 '<a href="/medias' + file.path + file.filename + '" class="cmd_button_download" target="_blank" title="Download"></a>' +
								'</p>' +
								'<p class="name">' + file.filename + '</p><br class="clear" />';
				} else {
					
					if( file.mimetype.match( new RegExp("word","g") ) )
						var icon = 'word-2-l.png';
					else if( file.mimetype.match( new RegExp("excel","g") ) )
						var icon = 'excel-2-l.png';
					else if( file.mimetype.match( new RegExp("pdf","g") ) ) 
						var icon = 'pdf-file-l.png';
					else
						var icon = 'blank-file-l.png';
					
					var html = '<img src="images/icon/' + icon + '" class="image" style="height:48px; width:48px;">' +
								'<p class="cmd_button">' +
									 '<a href="/medias' + file.path + file.filename + '" class="cmd_button_download" target="_blank" title="Download"></a>'+
								'</p>' +
								'<p class="name">' + file.filename + '</p><br class="clear" />';
					
				}
				
				var li = $('<li class="file" id="file' + file.id_media + '" data-type="' + file.mimetype + '"></li>')							
						.html(html);
						/*.click( function(){ 
							self.openDetails(file.id_media, file.type);							
						});*/
				
				
				// filter ?
				if( settings.filter ){
					
					var file_can_be_added = false;
					
					var filter = settings.filter.replace(" ","");
					filter = filter + ',' + filter.toUpperCase();
					
					var filters = filter.split(',');
					var explode = file.filename.split('.');
					var ext = explode[ explode.length-1 ];
					
					if( $.inArray(ext, filters) > -1 ){
						file_can_be_added = true;
					}
					
				}else
					file_can_be_added = true;
				
				if( file_can_be_added ) {
					var button_add = $('<a class="cmd_button_add" title="Add to selection"></a>').off().click(function(){ 
						self.addSelection(file.id_media, html, '');
					}).prependTo( li.find('.cmd_button') );
				}
				
				var button_delete = $('<a class="cmd_button_delete" title="Delete"></a>').off().click(function(){ 
					self.deleteMedia(file.id_media);
				}).appendTo( li.find('.cmd_button') );
				
				var checkbox = $('<input type="checkbox" name="move" class="moveFile" value="' + file.id_media + '" />')
					.off().on('click', function(){
						$('#moveBox').removeAttr('disabled');
					})
					.appendTo( li.find('.cmd_button') );
				
				$('#directory_content').append(li);
			});

		});
	}
	
	
	Mediabox.prototype.goBack = function(){
		id_directory_tree.pop();
					
		var d = 0;
		if( id_directory_tree.length > 0 )
			d = id_directory_tree[id_directory_tree.length-1];
		
		self.getDirectoryContent( d );
	}
	
	
	Mediabox.prototype.addSelection = function(id_media, html, params){
		// Don't have many times the same image in the selection
		if( $('#selection_content').find('li#selection' + id_media).length > 0 )
			return false;
		
		// limit ?
		if( $('#selection_content').find('li').length >= settings.limit ){
			MEDIABOX_REACH_FILE_LIMIT = MEDIABOX_REACH_FILE_LIMIT.replace('%n%', settings.limit);
			alert(MEDIABOX_REACH_FILE_LIMIT);	
			return false;
		}
		
		var media = {
			'id_media' : id_media,
			'params' : params,
			'path' :  $('#file' + id_media + ' .cmd_button_download').attr('href'),
			'icon' : $('#file' + id_media).find('img').attr('src'),
			'name' : $('#file' + id_media).find('p.name').text(),	
		}
		
		medias.push(media);
		
		var li = $('#file' + id_media).clone();
		li.find('.cmd_button a').remove();
		li.find('input:checkbox').remove();
		li.attr('id', 'selection' + id_media );
		
		var delete_button = $('<a class="cmd_button_delete" title="Remove"></a>').off().click(function(){ 
								self.removeSelection(id_media);
								self.refreshSectionPositionCmd();
							}).appendTo( li.find('.cmd_button') );
		
		var top_button = $('<a class="cmd_button_top" title="Move up"></a>').off().click(function(){ 
								$(this).parent().parent('li').insertBefore( $(this).parent().parent('li').prev() );
								self.refreshSectionPositionCmd();
							}).appendTo( li.find('.cmd_button') );
		
		var bottom_button = $('<a class="cmd_button_bottom" title="Move down"></a>').off().click(function(){ 
								$(this).parent().parent('li').insertAfter( $(this).parent().parent('li').next() );
								self.refreshSectionPositionCmd();
							}).appendTo( li.find('.cmd_button') );					
		
		li.appendTo( $('#selection_content') );
		self.saveData();		
		self.refreshSectionPositionCmd();
	}
	
	Mediabox.prototype.refreshSectionPositionCmd = function(){
		$('#selection_content .cmd_button_top, #selection_content .cmd_button_bottom').show();		
		$('#selection_content li').first().find('.cmd_button_top').hide();
		$('#selection_content li').last().find('.cmd_button_bottom').hide();	
		self.saveData();
	}
	
	Mediabox.prototype.removeSelection = function(id_media){
		var z = 0;
		var new_medias_array = new Array();
		for( var i=0 ; i< medias.length ;i++ ){
			if( medias[i]['id_media'] !== id_media ){
				new_medias_array[z] = medias[i];
				z++;
			}
		}
		medias = new_medias_array;
		$('#selection' + id_media).remove();
		self.saveData();
	}	
	
	Mediabox.prototype.setSelectionParams = function(id_media, params){
		for( var i=0 ; i< medias.length ;i++ ){
			if( medias[i]['id_media'] == id_media ){
				medias[i]['params'] = params;
			}
		}	
		self.saveData();
	}
	
	Mediabox.prototype.saveData = function(){
		$('#mediabox' + settings.random_id + '_data').data('data', medias);
	}
	
	Mediabox.prototype.returnSelection = function(){			
		$('#mediabox' + settings.random_id + '_data').val( JSON.stringify(medias, null, 0) );
		settings.callback_function();
		self.close();
	}
	
	Mediabox.prototype.defaultSelectionCallback = function(){
		var rand = settings.random_id;
		
		var ul = $('<ul></ul>');
		
		if( medias && medias.length > 0 ){
			for(i=0; i<medias.length; i++ ){
				var li = $('<li></li>').addClass('file');
				li.html('<img src="' + medias[i].icon + '" class="thumb_image"> ' + medias[i].name);			
				li.appendTo( ul );
			}
		}
		$('#results_images_' + rand).html(ul);
	}
	
	Mediabox.prototype.openParams = function(){
		// size
		// title
		// zoom on click ?
		// force download ?
		// INSERT or ADD TO LIST		
		
	}
	
	Mediabox.prototype.initSelection = function(){
		if( medias.length == 0 )
			return false;
			
		for(i=0; i<medias.length; i++ ){
			
			var li = $('<li></li>').attr('id','selection' + medias[i].id_media).addClass('file');
			li.html('<img src="' + medias[i].path + '" class="image">' +
				'<p class="cmd_button"></p>' +
				'<p class="name">' + medias[i].name + '</p>' +
				'<br class="clear">');
			
			//li.find('.cmd_button a').remove();
			//li.attr('id', 'selection' + medias[i].id_media );

			var delete_button = $('<a class="cmd_button_delete" title="Remove"></a>').off().click(function(){ 
									var id_media = $(this).parent().parent().attr('id').replace('selection', '');
									self.removeSelection(id_media);
									self.refreshSectionPositionCmd();
								}).appendTo( li.find('.cmd_button') );
			
			var top_button = $('<a class="cmd_button_top" title="Move up"></a>').off().click(function(){ 
									$(this).parent().parent('li').insertBefore( $(this).parent().parent('li').prev() );
									self.refreshSectionPositionCmd();
								}).appendTo( li.find('.cmd_button') );
			
			var bottom_button = $('<a class="cmd_button_bottom" title="Move down"></a>').off().click(function(){ 
									$(this).parent().parent('li').insertAfter( $(this).parent().parent('li').next() );
									self.refreshSectionPositionCmd();
								}).appendTo( li.find('.cmd_button') );					
			
			li.appendTo( $('#selection_content') );	
		}
		self.refreshSectionPositionCmd();	
	}
	
	
	
	Mediabox.prototype.addDirectory = function(){
		var directory_name = $('#create_directory_name').val();
		$.ajax({
			type: "POST",
			url: "index.php?ajax=1&mediabox=1",
			data: { action : "addDirectory", directory_name : directory_name, id_parent : id_current_directory }
		}).done(function( json ) {
			self.getDirectoryContent( id_current_directory );
		});	
	}
	
	
	Mediabox.prototype.initUploader = function(){
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		
		$('#fileupload').fileupload({
			dataType: 'json',
			formData : { sizes : settings.sizes },
			dropZone : $('#mediabox_dropzone'),	
			dragover : function (e) {
				var dropZone = $('#mediabox_dropzone'),
					timeout = window.dropZoneTimeout;
				if (!timeout) {
					dropZone.addClass('in');
				} else {
					clearTimeout(timeout);
				}
				var found = false,
					node = e.target;
				do {
					if (node === dropZone[0]) {
						found = true;
						break;
					}
					node = node.parentNode;
				} while (node != null);
				if (found) {
					dropZone.addClass('hover');
				} else {
					dropZone.removeClass('hover');
				}
				window.dropZoneTimeout = setTimeout(function () {
					window.dropZoneTimeout = null;
					dropZone.removeClass('in hover');
				}, 100);
			},
			send : function (e, data) {
				$('.mediabox_errors').html('');
				if( data.files.length > 0 ){
					for(var i=0; i<data.files.length; i++ ){
						var explode = data.files[i].name.split('.');
						var ext = explode[ explode.length-1 ];
						
						var non_allowed_extensions = [ 'php4','php','php3','php5','exe','cgi','html' ];
						
						if( $.inArray(ext, non_allowed_extensions) > -1 ){
							$('.mediabox_errors').html(MEDIABOX_NONOK_EXT);
							return false;
						}else
							$('#uploading_loading').show();
						
					}
									
				}				
			},
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('.progress .bar').css(
					'width',
					progress + '%'
				);
				if( progress == '100' ) {
					
					//$('.fileupload-progress').fadeOut();
					$('.progress .bar').css(
						'width',
						'0%'
					);
					setTimeout( function(){ 
						self.getDirectoryContent( id_current_directory );
						var objDiv = document.getElementById("mediabox_content_window");
						objDiv.scrollTop = objDiv.scrollHeight;
						$('#uploading_loading').hide();
					}, 1000);
				}
			}
		});	
	}
	
	
	Mediabox.prototype.deleteMedia = function(id_media){
		if( confirm(MEDIABOX_CONFIRM_DELETE_FILE) ){
			$.ajax({
				type: "POST",
				url: "index.php?ajax=1&mediabox=1",
				data: { action : "deleteFile", id_media : id_media }
			}).done( function( json ) {
				$('#file' + id_media).fadeOut();
			});	
		}
	}
	
	Mediabox.prototype.deleteFolder = function(id_directory){
		if( confirm(MEDIABOX_CONFIRM_DELETE_FOLDER) ){
			$.ajax({
				type: "POST",
				url: "index.php?ajax=1&mediabox=1",
				data: { action : "deleteFolder", id_directory : id_directory }
			}).done( function( json ) {
				$('#folder' + id_directory).fadeOut();
			});	
		}
	}
	
	
	Mediabox.prototype.resizeElements = function(){
		var h = $('#mediabox').height();
		$('#mediabox_menu, #mediabox_content, #mediabox_selection').outerHeight(h);		
		$('#mediabox_content_window, #mediabox_selection_window').height( h-82 )
	}
	
	
	Mediabox.prototype.attachButtonEvents = function(){
		$('#mediabox_content_command #create_directory').off().click( function(){ self.addDirectory(); return false; } );		
		$('#mediabox_content_command #back').off().click( function(){  self.goBack(); return false; } )
		$(window).resize( function(){ self.resizeElements(); return false; } );
		$('#get_selection').off().click( function(){ self.returnSelection(); return false; });
		$('#close_button').off().click( function(){ $('#mediabox, #overlay').hide(); return false; });
	}
	
	/* To delete */
	/*Mediabox.prototype.returnSelection = function(){
		
		var return_medias = new Array();
		var i = 0;
		
		$('#selection_content li').each( function(){
			var li = $(this);
			$.each( medias, function(z, media){
				if( li.attr('id').replace('selection','') == media.id_media ){
					return_medias[i] = media;	
				}
			});			
			i++;
		});
		
		return return_medias;	
	}*/
	
	Mediabox.prototype.init = function(){
		medias = new Array();
		$('#mediabox_selection_window #selection_content').html('');
		id_directory_tree = new Array();
		$('#mediabox, #overlay').show();
		self.initUploader();
		
		self.getDirectoryContent(0);
		self.attachButtonEvents();
		self.resizeElements();
		self.initMoveBox();
		
		/* get data */		
		if( $('#mediabox' + settings.random_id + '_data').val() )
			medias = JSON.parse( $('#mediabox' + settings.random_id + '_data').val() );
		else if( $('#mediabox' + settings.random_id + '_data').data('data') )
			medias = $('#mediabox' + settings.random_id + '_data').data('data');
		
		if( medias.length )
			self.initSelection();
		/* end get data */
	}
	
	function Mediabox(options) {
		self = this;
		if ( options ) { 
			$.extend( settings, options );
		}
		
		if( !settings.callback_function )
			settings.callback_function = function(){ self.defaultSelectionCallback() };
		
			
		self.init();
	}
	
	$.extend({
		Mediabox : function (options) {
			var Mediabox_object = new Mediabox(options);
		}
	});


 })( jQuery );
