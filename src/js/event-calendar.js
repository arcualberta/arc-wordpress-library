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


	// var events = [
	// 	{
	// 		dateStart: '2015-09-10',
	// 		dateEnd: '2015-09-10',
	// 		name: "event name",
	// 		description: "event description",
	// 		url: "http://someevent.org",
	// 		img: "http://arc.arts.ualberta.ca/wp-content/themes/arc/images/arclogo_text.png"
	// 	},
	// 	{
	// 		dateStart: '2015-09-12',
	// 		dateEnd: '2015-09-13',
	// 		name: "event name two",
	// 		description: "event description two",
	// 		url: "http://slashdot.org"
	// 	},

	// ];
	var calendarDate = new Date();
	var currentMonth;
	var currentYear;
	var currentDayName = 0;
	var currentDayEntryId = 0;
	var calendarTemplate =
		'<div class="awl-calendar-container">' +
			// controls
			'<div id="awl-calendar-header">'+
				'<div id="awl-calendar-month-container">'+
					'<div id="awl-calendar-month-prev"><button class="btn btn-default"><div class="glyphicon glyphicon-triangle-left"></div></button></div>'+
					'<div id="awl-calendar-month-name"></div>'+
					'<div id="awl-calendar-month-next"><button class="btn btn-default"><div class="glyphicon glyphicon-triangle-right"></span></button></span>'+
				'</div>' +
				'<div id="awl-calendar-year-container">'+
					'<div id="awl-calendar-year-name"></div>'+
				'</div>' +
			'</div>' +
			// calendar
			'<div id="awl-calendar-body">' +
				'<table>' +
					'<tr>' +
						(function(){
							var result = "";
							for (var i=0;i<dayNames.length;i++) {
								result += '<th>' + dayNames[i] + '</th>';
							}
							return result;
						})() +
					'</tr>' +
					// day entries
					(function(){
						var result = "";
						var currentId = 0;
						for (var i=0; i<6; i++) {
							result += '<tr>';
							for (var j=0; j<7; j++) {
								result += '<td class="awl-calendar-day" id="awl-calendar-'+ currentId++ +'">';
								result += '<div class="awl-date-content">';
								
								result += '<div class="awl-date-content-main">';
								// result += currentId;
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
		'</div>';
	

	// from 
	// http://stackoverflow.com/questions/1184334/get-number-days-in-a-specified-month-using-javascript

	// Month is 1 based
	function daysInMonth(month, year) {
		return new Date(year, month+1, 0).getDate();
	}

	var injectTemplate = function(id) {
		$("#"+id).html(calendarTemplate);
	};

	var setToday = function() {				
		currentMonth = calendarDate.getMonth();
		currentYear = calendarDate.getFullYear();
	};

	var setCalendar = function() {
		setCalendarDays();
		setMonthYearNames();
		highlightToday();
	};

	var setCalendarDays = function() {
		
		calendarDate.setFullYear(currentYear);
		calendarDate.setMonth(currentMonth);
		calendarDate.setDate(1);

		var totalDays = daysInMonth(currentMonth, currentYear);
		var dayOfWeek = calendarDate.getDay();
		var calendarDayIdBase = 'awl-calendar-';
		var currentDay=1;
		// $('.awl-date-content-main').html('2');
		// Error aqui de donde metes contenido
		$(".awl-date-content-main").html('');
		for (var i=dayOfWeek; currentDay<=totalDays; i++, currentDay++){			
			$('#' + calendarDayIdBase + i + '> .awl-date-content > .awl-date-content-main').html(currentDay);
		}

		// set previous month
		// set next month

	};

	var setMonthYearNames = function() {
		$("#awl-calendar-month-name").html(monthNames[currentMonth]);
		$("#awl-calendar-year-name").html(currentYear);
	};

	var highlightToday = function() {
		var date = new Date();
		// remove today class
		$('.awl-date-content-main').removeClass("awl-calendar-today");
		if (date.getMonth() === currentMonth && 
				date.getFullYear() === currentYear) {
			// set today class

			var today = date.getDate();
			date.setDate(1);
			var firstDay = date.getDay();

			$('#awl-calendar-'+ (today + firstDay - 1) +' > .awl-date-content > .awl-date-content-main').addClass('awl-calendar-today');

		}
	};

	var basePrevNextButtonBehaviour = function(direction, limit){
			if (currentMonth == limit) {
				currentYear += direction;
			}
			var m = 12;
			currentMonth = (((currentMonth+direction)%m)+m)%m;
			setCalendar();
	};

	var setButtonBehaviour = function() {
		$("#awl-calendar-month-prev").click(function(){
			basePrevNextButtonBehaviour(-1, 0);
		});
		$("#awl-calendar-month-next").click(function(){
			basePrevNextButtonBehaviour(1, 11);			
		});
	};

	var highlightEvent = function(){
		$('#awl-calendar-4 > .awl-date-content > .awl-date-content-footer').addClass('awl-calendar-event');
	};

 	awl.eventCalendar = function(id) {
 		$(function(){
 			injectTemplate(id);	
 			setToday();
 			setCalendar();
 			setButtonBehaviour();
 			highlightEvent();
 			highlightToday();
 		});
 	};

})(awl);