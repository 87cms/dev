$(document).ready( function(){
	$('.chart canvas').attr('width', $(window).width() - 290 );
	$('.chart canvas').attr('height', 400 );
	$.ajax({
		url: "gapi.php",
		type: "POST",
		dataType: "json"
	})
	.done(function(json) {
		
		// visits
		var data = { };
		data.labels = new Array();
		var dots = new Array();
		$.each(json.graph, function(i, item) {
			data.labels[i] = item[0];
			dots[i] = parseInt(item[1]);
		});
		data.datasets = new Array({
			fillColor : "rgba(151,187,205,0.5)",
			strokeColor : "rgba(151,187,205,1)",
			pointColor : "rgba(151,187,205,1)",
			pointStrokeColor : "#fff",
			data : dots
		});
		
		var ctx = $("#visits_chart").get(0).getContext("2d");
		var myNewChart = new Chart(ctx).Line(data);
		
		
		//stats
		$('#ga_visits span.number').text( json.numbers[0][1] );
		$('#ga_pageview span.number').text( json.numbers[0][2] );
		$('#ga_bouncerate span.number').text( Math.round(parseInt(json.numbers[0][4])) + '%' );
		$('#ga_avgsite span.number').text( Math.round(parseInt(json.numbers[0][6])*100/100) + 'sec');
		$('#ga_newvisits span.number').text( json.numbers[0][7] );
		$('#ga_pagepervisit span.number').text( Math.round(parseInt(json.numbers[0][8])*100/100) );
		
		// browser
		var browsers = new Array();
		var nbv = parseInt(json.numbers[0][1]);
		var color = new Array('#1abc9c', "#3498db", "#9b59b6", "#e74c3c", "#e67e22", "#34495e");
		var data = new Array();
		$.each(json.browser, function(i, item) {
			var tr = '<tr>'	
			tr += '<td style="background-color:' + color[i] +'"> </td>';
			tr += '<td><a href="#">' + item[0] + '</a></td>';
            tr += '<td class="col_center">' + item[1] + '</td>';
            tr += '<td class="col_center">' + Math.round(parseInt(item[1])*100 / nbv) + '</td>';
			tr += '</tr>';
			
			$('#browsers_stats tbody').append(tr);
			data[i] = {
					value: Math.round(parseInt(item[1])*100/nbv),
					color: color[i]
			}
			
		});
		
		$('#browsers_charts').attr('width', $('#browsers_stats').width()-10);
		$('#browsers_charts').attr('height',200);
		var ctx = $("#browsers_charts").get(0).getContext("2d");
		new Chart(ctx).Doughnut(data);
		
		
		// mobile
		var browsers = new Array();
		var nbv = parseInt(json.numbers[0][1]);
		var color = new Array('#1abc9c', "#3498db", "#9b59b6", "#e74c3c", "#e67e22", "#34495e");
		var data = new Array();
		$.each(json.mobile, function(i, item) {
			var tr = '<tr>'	
			tr += '<td style="background-color:' + color[i] +'"> </td>';
			tr += '<td><a href="#">' + item[0] + '</a></td>';
            tr += '<td class="col_center">' + item[1] + '</td>';
            tr += '<td class="col_center">' + Math.round(parseInt(item[1])*100 / nbv) + '</td>';
			tr += '</tr>';
			
			$('#mobile_stats tbody').append(tr);
			data[i] = {
					value: Math.round(parseInt(item[1])*100/nbv),
					color: color[i]
			}
			
		});
		
		$('#mobile_charts').attr('width', $('#mobile_stats').width()-10);
		$('#mobile_charts').attr('height',200);
		var ctx = $("#mobile_charts").get(0).getContext("2d");
		new Chart(ctx).Doughnut(data);
		
	});
	
	
});