(function(awl){
	"use strict";

	var currentMonth,
		currentYear,
		currentDayName = 0,
		currentDayEntryId = 0,
		calendarDate = new Date(),
		eventList = {};

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

	var calendarTemplate =
		'<div class="awl-calendar-container">' +
			// controls
			'<div id="awl-calendar-header">'+
				'<div id="awl-calendar-month-container">'+
					'<span id="awl-calendar-month-prev">'+
					'<span class="awl-calendar-month-button glyphicon glyphicon-triangle-left">'+
					'</span></span>'+
					'<span id="awl-calendar-month-name"></span>'+
					'<span id="awl-calendar-month-next">'+
					'<span class="awl-calendar-month-button glyphicon glyphicon-triangle-right">'+
					'</span></span>'+
				'</div>' +
				'<span id="awl-calendar-year-container">'+
					'<span id="awl-calendar-year-name"></span>'+
				'</span>' +
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
						var result = "",
						currentId = 0;
						for (var i=0; i<6; i++) {
							result += '<tr>';
							for (var j=0; j<7; j++) {
								result += '<td class="awl-calendar-day" id="awl-calendar-'+ 
									currentId++ +'">';
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
	var daysInMonth = function(month, year) {
		return new Date(year, month+1, 0).getDate();
	};

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
		clearEvents();
		highlightToday();
		getEvents();
	};

	var setCalendarDays = function() {
		
		calendarDate.setFullYear(currentYear);
		calendarDate.setMonth(currentMonth);
		calendarDate.setDate(1);

		var totalDays = daysInMonth(currentMonth, currentYear),
			dayOfWeek = calendarDate.getDay(),
			calendarDayIdBase = 'awl-calendar-',
			currentDay = 1;

		$(".awl-date-content-main").html('');
		var id= '';
		for (var i=dayOfWeek; currentDay<=totalDays; i++, currentDay++){
			id = '#' + calendarDayIdBase + 
						i + '> .awl-date-content > .awl-date-content-main';
			$(id).html(currentDay);
		}

		// set previous month
		// set next month

	};

	var setMonthYearNames = function() {
		$("#awl-calendar-month-name").html(monthNames[currentMonth]);
		$("#awl-calendar-year-name").html(currentYear);
	};

	var highlightToday = function() {
		var today,
			firstDay,
			date = new Date();
		// remove today class
		if (date.getMonth() === currentMonth && 
				date.getFullYear() === currentYear) {
			// set today class

			today = date.getDate();
			date.setDate(1);
			firstDay = date.getDay();
			var id = '#awl-calendar-'+ 
							(today + firstDay - 1) + 
							' > .awl-date-content > .awl-date-content-main';
			$(id).addClass('awl-calendar-today');

		}
	};

	var clearEvents = function() {
		$('.awl-date-content-main').removeClass("awl-calendar-today");
		$('.awl-date-content-footer').removeClass('awl-calendar-event');
		$('.awl-calendar-day').off('click');
	};


	var basePrevNextButtonBehaviour = function(direction, limit){			
			var m = 12;
			if (currentMonth == limit) {
				currentYear += direction;
			}			
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

	var highlightEvent = function(index){
		var id = '#awl-calendar-'+ index +
			' > .awl-date-content > .awl-date-content-footer';
		$(id).addClass('awl-calendar-event');
	};

	var getDateFromString = function(dateString) {
		// dateString format YYYY-MM-DD
		var dateArray = dateString.split('-'),
			date = new Date(dateArray[0], dateArray[1]-1, dateArray[2]);
		return date;
	};

	var getDateIndex = function(date) {
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
		return firstDay + date.getDate() - 1;		
	};

	var addEvent = function(currentEvent, date) {
		var index = getDateIndex(date);
		highlightEvent(index);		
		if (! (index in eventList)) {
			eventList[index] = [];
		}
		eventList[index].push(currentEvent);
		setEventPopover(index, date);
	};

	var setEventPopover = function(index, date) {
		var id = '#awl-calendar-' + index;
		$(id).on('click', function(event) {
			var id = $(event.target).parent().parent().attr('id');
			var index = id.replace('awl-calendar-', '');
			var events = eventList[index];
			$('#' + id).popover({
				title: "Events for " + date.toDateString() ,
				placement: 'auto',
				html: true,
				content: function() {
					return generateEventHtmlList(events);
				}
			});
		});
	};

	var getExtraEventInfo = function(currentEvent) {
		var needToAdd = false;
		var result = "<ul>";
		var startDate = getDateFromString(currentEvent._arc_start_date);
		var endDate = getDateFromString(currentEvent._arc_end_date);
		var eventDuration = Math.round((endDate-startDate)/(1000*60*60*24));

		if (currentEvent._arc_venue && currentEvent._arc_venue !== "") {
			needToAdd = true;
			result += '<li>Venue: ' + currentEvent._arc_venue + '</li>';
		}
		
		if (eventDuration > 1) {
			needToAdd = true;
			result += 
				'<li>Event starts on ' + 
				startDate.toDateString() + 
				' and ends on ' + 
				endDate.toDateString() + 
				'</li>';								
		}

		if (needToAdd) {
			result += '</ul>';
			return result;
		}
		return '';
	};

	var generateEventHtmlList = function(events) {
		var result = "";

		for (var i in events) {
			var currentEvent = events[i];
			result +=
				'<div class="media">'+
				'	<div class="media-left">'+
				'		<a href="#">'+
				'			<img class="media-object img-thumbnail awl-media-image" src="'+
							currentEvent._arc_image_grid_img+
							'">'+
				'		</a>'+
				'	</div>'+
				'	<div class="media-body">'+
				'		<h4 class="media-heading">'+ currentEvent.post_title +'</h4>'+
						currentEvent.post_content +
						getExtraEventInfo(currentEvent) +
				'	</div>'+
				'</div>';
		}
		return result;
	};

	var processIncomingEvents = function(data) {
		var currentEvent,
			startDate,
			endDate,
			currentDate,
			totalDays = daysInMonth(currentYear, currentMonth);

		eventList = {};

		for (var currentDay = 1; currentDay<=totalDays; currentDay++) {
			for (var i in data) {
				currentEvent = data[i];
				startDate = getDateFromString(currentEvent._arc_start_date);
				endDate = getDateFromString(currentEvent._arc_end_date);
				currentDate = new Date(currentYear, currentMonth, currentDay);
				
				if ((currentDate >= startDate && currentDate <= endDate) || 
					(currentDate <= endDate && currentDate >= startDate)) {
					addEvent(currentEvent, currentDate);
				}	
			}				
		}
	};

	var getEvents = function() {
		// awlAjax is set from wordpress in awl.php using wp_localize_script
		var url = awlAjax.ajaxurl,
		currentMonthStarting1 = ('0' + (currentMonth+1)).slice(-2),
		data = {
			action: 'events',
			start_date: currentYear + '-' + 
									currentMonthStarting1 + '-01',
			end_date: currentYear + '-' + 
								currentMonthStarting1 + '-' + 
								(daysInMonth(currentMonth, currentYear)),
			category: 'Event'
		};
		console.log(data);
		$.get(url, data)
		.done(processIncomingEvents);	
	};

	awl.eventCalendar = function(id) {
		$(function(){
			injectTemplate(id);	
			setToday();
			setCalendar();
			setButtonBehaviour();
		});
	};

})(awl);
