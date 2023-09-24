import flatpickr from "flatpickr";
import 'flatpickr/dist/themes/material_green.css';


flatpickr(".flatpickr-reservation", {
    minDate: md,
    disable: [mop],

    altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d",
});

flatpickr(".flatpickr-bithday", {
    altInput: true,
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",

});


