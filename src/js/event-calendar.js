(function(awl){
	"use strict";

	var monthNames = [
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	];

	var dayNames = [
		'SUN',
		'MON',
		'TUE',
		'WED',
		'THU',
		'FRI',
		'SAT',
	];
	var currentDayName = 0;
	var currentDayEntryId = 0;
	var calendarTemplate =
		'<div class="awl-calendar-container">' +
			// controls
			'<div class="row">' +
				'month' +
				'year' +
			'</div>' +
			// calendar
			'<div class="row">' +
				'<div class="col-sm-12">' +
					'<table>' +
						'<tr>' +
							(function(){
								var result = ""
								for (var i=0;i<dayNames.length;i++) {
									result += '<th>' + dayNames[i] + '</th>' +
								}
								return result;
							})() +
						'</tr>' +
						// day entries
						(function(){
							var result = "";
							for (var i=0; i<5; i++) {
								result += '<tr>';
								for (var j=0; j<5; j++) {
									var currentId = i*5+j;
									result += '<td id=awl-calendar-"'+currentId+'">'
									result += '<div class="awl-date-content">';
									
									result += '<div class="awl-date-content-main">';
									result += currentId;
									result += '</div>';
									
									result += '<div class="awl-date-content-footer">';
									result += '</div>';
									
									result += '</div>';
									result += '</td>';
								}
								result += '</tr>';
							}
							return result;
						})() +
					'</table>' +
				'</div>' +
			'</div>' +
		'</div>';
	

	// from 
	// http://stackoverflow.com/questions/1184334/get-number-days-in-a-specified-month-using-javascript

	// Month is 0 based
	function daysInMonth(month, year) {
		return new Date(year, month-1, 0).getDate();
	}

	var date = Date();
	var currentMonth = date.getMonth();
	var currentYear = date.getFullYear();


	var events = [
		{
			dateStart: '2015-09-10',
			dateEnd: '2015-09-10',
			name: "event name",
			description: "event description",
			url: "http://someevent.org",
			img: "http://arc.arts.ualberta.ca/wp-content/themes/arc/images/arclogo_text.png"
		},
		{
			dateStart: '2015-09-12',
			dateEnd: '2015-09-13',
			name: "event name two",
			description: "event description two",
			url: "http://slashdot.org"
		},

	];
 	
	var injectTemplate = function(id) {
		$("#"+id).html(calendarTemplate);
	}

 	awl.eventCalendar = function(id) {
 		injectTemplate(id);
 	}

})(awl);