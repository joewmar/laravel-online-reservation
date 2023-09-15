import './bootstrap';
import '../css/app.css';
import './flatpickr.js';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Scroll to the element with the provided ID
document.getElementById("disabledAll").disabled = true;
var nodes = document.getElementById("disabledAll").getElementsByTagName('*');
for(var i = 0; i < nodes.length; i++){
    nodes[i].disabled = true;
}
document.querySelectorAll(".DISABLED-ALL").disabled = true;
var nodes = document.querySelectorAll(".DISABLED-ALL").getElementsByTagName('*');
for(var i = 0; i < nodes.length; i++){
    nodes[i].disabled = true;
}



