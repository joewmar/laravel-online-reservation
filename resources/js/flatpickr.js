import flatpickr from "flatpickr";
import rangePlugin from "flatpickr/dist/plugins/rangePlugin";
// import 'flatpickr/dist/flatpickr.css';
import 'flatpickr/dist/themes/material_green.css';


flatpickr(".flatpickr-reservation", {
    minDate: "today",
    "plugins": [new rangePlugin({ input: ".flatpickr-input2"})],
    altInput: true,
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",

});
flatpickr(".flatpickr-bithday", {
    altInput: true,
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",

});

