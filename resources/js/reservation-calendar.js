import { Calendar } from 'fullcalendar'

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var calendar = new Calendar(calendarEl, {
      themeSystem: 'sketchy',
      aspectRatio: 2,
      initialView: 'dayGridMonth',zz
      headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
  });

  calendar.render();
});