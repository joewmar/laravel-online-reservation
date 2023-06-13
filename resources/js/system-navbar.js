let clicked = true;
let txt ='';

let menu = document.getElementById("menu");
let sidebar = document.getElementById("sidebar");
let sbList = document.querySelectorAll(".title");

menu.addEventListener("click", function () {
    if(clicked){
        txt
        sidebar.classList.add("w-full");
        sidebar.classList.add("md:w-56");
        sidebar.classList.remove("w-[5rem]");
        for (let i = 0; i < sbList.length; i++) {
            sbList[i].classList.remove("opacity-0");
        }
        clicked = false;
        txt = '<svg class="swap-on fill-current" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><polygon points="400 145.49 366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49"/></svg>';
    }
    else{

        for (let i = 0; i < sbList.length; i++) {
            sbList[i].classList.add("opacity-0");
        }
        sidebar.classList.remove("w-full");
        sidebar.classList.remove("md:w-56");
        sidebar.classList.add("w-[5rem]");
        clicked = true;
        txt = '<svg class="swap-off fill-current" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M64,384H448V341.33H64Zm0-106.67H448V234.67H64ZM64,128v42.67H448V128Z"/></svg>';
    }
    menu.innerHTML = txt;
});
