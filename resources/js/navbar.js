var bgNavbar = document.getElementById("navbar");
var toToggle = document.querySelectorAll(".toggleColour");
var screen = window.matchMedia("(max-width: 640px)");



document.addEventListener("scroll", function () {
/*Apply classes for slide in bar*/
scrollpos = window.scrollY;


if (scrollpos > 200) {
    bgNavbar.classList.add("bg-base-100");
    bgNavbar.classList.remove("bg-transparent");
    bgNavbar.classList.add("shadow-md");

    // bgNavbar.classList.remove("pt-3");
    for (var i = 0; i < toToggle.length; i++) {
        toToggle[i].classList.remove("text-white");
        toToggle[i].classList.add("text-neutral");

    }

} 
else {
//   bgNavbar.classList.add("pt-3");
  bgNavbar.classList.add("bg-transparent");
  bgNavbar.classList.remove("shadow-md");
  bgNavbar.classList.remove("bg-base-100");
  for (var i = 0; i < toToggle.length; i++) {
        toToggle[i].classList.add("text-white");
        toToggle[i].classList.remove("text-neutral");
    }
}

});


