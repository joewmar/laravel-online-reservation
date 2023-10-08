import flatpickr from "flatpickr";
import 'flatpickr/dist/themes/material_green.css';

let datesDisabled = [];
if (typeof mrsh === 'undefined') {
    datesDisabled = [mop]
}
else{
    datesDisabled = [mop, mrsh];
}

flatpickr(".flatpickr-reservation", {
    minDate: md,
    disable: datesDisabled,

    altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d",
});

flatpickr(".flatpickr-bithday", {
    altInput: true,
    altFormat: "F j\\, Y",
    dateFormat: "Y-m-d",

});


