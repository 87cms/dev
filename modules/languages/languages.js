$(document).ready( function(){
	var location = window.location.href;
	$('#lang_block a').each( function(){
		$(this).on('click', function(){
			/*location = location.replace('http://','').replace('https://','');
			var exploded = location.split('/');
			var new_location = $(this).attr('href');
			
			for(i=2; i<exploded.length; i++)
				new_location += exploded[i] + '/';	
			
			$(this).attr('href', new_location.substring(0, new_location.length-1));*/
		});
	});
});
