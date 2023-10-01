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
