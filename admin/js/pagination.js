/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

$(document).ready(function(e) {
    $('#sorter select').on('change', function(){
		var sorter = $(this).val();
		var id_entity_model = $('#id_entity_model_value').val();
		var id_parent = $('#entity_parent .parents:checked').val();
		
		if( typeof id_parent == "undefined" )
			id_parent = '';
		else
			id_parent = '&id_parent=' + id_parent;
			
		var url = 'http://' + document.domain + '/admin/index.php?p=entity&id_entity_model=' + id_entity_model + '&sort=' + sorter + id_parent;
		location.href = url;
	});
	
	$('#pagination_block select').on('change', function(){
		var sorter = $('#sorter select').val();
		var page = $(this).val();
		var id_parent = $('#entity_parent .parents:checked').val();
		
		if( typeof id_parent == "undefined" )
				id_parent = '';
			else
				id_parent = '&id_parent=' + id_parent;
		
		var id_entity_model = $('#id_entity_model_value').val();
		var url = 'http://' + document.domain + '/admin/index.php?p=entity&id_entity_model=' + id_entity_model + '&sort=' + sorter + '&page=' + page + id_parent;
		location.href = url;	
	});
	
	$('#pagination_prev, #pagination_next').on('click', function(){
		if( $(this).hasClass('disabled') )
			return false;
		else{
			var current_page = parseInt($('#pagination_block select').val());
			var nbpage = $('#pagination_block select option').length;
			
			if( $(this).attr('id') == "pagination_prev" )
				var page = current_page-1;
			else
				var page = current_page+1;
			
			var sorter = $('#sorter select').val();
			
			var id_parent = $('#entity_parent .parents:checked').val();
		
			if( typeof id_parent == "undefined" )
				id_parent = '';
			else
				id_parent = '&id_parent=' + id_parent;
			
			var id_entity_model = $('#id_entity_model_value').val();
			var url = 'http://' + document.domain + '/admin/index.php?p=entity&id_entity_model=' + id_entity_model + '&sort=' + sorter + '&page=' + page + id_parent;
			location.href = url;	
		}
	});
	
	$('#reset_sorting').on('click', function(){
		var id_entity_model = $('#id_entity_model_value').val();
		
		var id_parent = $('#entity_parent .parents:checked').val();
		
		if( typeof id_parent == "undefined" )
			id_parent = '';
		else
			id_parent = '&id_parent=' + id_parent;
		
		var url = 'http://' + document.domain + '/admin/index.php?p=entity&id_entity_model=' + id_entity_model + id_parent;
		location.href = url;
	});
});

