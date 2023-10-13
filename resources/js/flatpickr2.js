import flatpickr from "flatpickr";
import 'flatpickr/dist/themes/material_green.css';

flatpickr(".flatpickr-reservation-one", {
    altInput: true,
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",
});
flatpickr(".flatpickr-room-today", {
    altInput: true,
    minDate: "today",
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",
});
flatpickr(".flatpickr-reservation-month", {
    altInput: true,
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",
    disable: [
        function(date) {
          // Get the current date
          const today = new Date();
          
          // Calculate the first day of the current month
          const firstDayOfCurrentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
          
          // Calculate the first day of the previous month
          const firstDayOfPreviousMonth = new Date(firstDayOfCurrentMonth);
          firstDayOfPreviousMonth.setMonth(firstDayOfCurrentMonth.getMonth());
          
          // Disable dates that are before the first day of the previous month
          return date < firstDayOfPreviousMonth;
        }
      ],
});
