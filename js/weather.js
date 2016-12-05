
jQuery(document).ready(function($) {

	var weatherStatus = 'Getting current weather conditions...';
	$('.header-image').prepend('<p class="status">' + weatherStatus + '</p>');
	var currentDate = new Date();
	var month = currentDate.getMonth()+1;
	var day = currentDate.getDate();
	var hour = (currentDate.getHours());
	hour =  hour > 12 ? hour-12 : hour;
	var mins = ('0' + currentDate.getMinutes()).slice(-2);

	var date = month + '/' + day;
	var time = hour + ':' + mins;

	function lr_source(request, response) {
		$.ajax({
			url: Wapp.ajaxurl,
			// type: 'GET',
			dataType: "text",
			cache: false,
			data: {
				action: 'lr_wapp',
				_wpnonce: Wapp._wpnonce
			},
			success: function( data ) {
				console.log ("Data returned from the API call: " + data);
				
				weatherStatus = parseJson(data);
				if(typeof weatherStatus === 'object'){
					$('.header-image p.status').replaceWith('<p class="status">Today is ' + date +  '. Current Seattle weather at ' + time + ' is <br/><span class="details">' + weatherStatus.weather[0].main + ' (' + weatherStatus.weather[0].description + '). Temp: ' + parseInt(weatherStatus.main.temp) + '&deg; </span></p>');
				}
			},
			error: function(obj, status, error){
				console.log('there was an ' + status + ' error retreiving data: ' + error);
				var err = JSON.parse(obj.responseText);
				console.log("Contents of Message: " + err.Message);
			}
			});
		}
	lr_source();


});

function parseJson(data){
	try {
	  	return JSON.parse(data) ; 
	}
	catch (e) {
	   // statements to handle any exceptions
	   console.log('Catch error: ' + e); // pass exception object to error handler
	   return "Error getting weather data..."
	}
}