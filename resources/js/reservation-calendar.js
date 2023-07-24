import { Calendar } from 'fullcalendar'

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var calendar = new Calendar(calendarEl, {
      themeSystem: 'sketchy',
      aspectRatio: 2,
      initialView: 'dayGridMonth',
      headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      events: window.location + '/calendar',
      eventClick: function(info) {
          if (info.event.url) {
              window.location.href = info.event.url;
          }
      },
      
        // Add more events here as needed
      
  });

  calendar.render();
});
