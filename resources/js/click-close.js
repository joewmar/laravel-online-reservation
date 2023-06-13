function deleteChild() {
    let e = document.getElementById("close");
    
    //e.firstElementChild can be used.
    let child = e.lastElementChild; 
    while (child) {
        e.removeChild(child);
        child = e.lastElementChild;
    }
}
document.getElementById("btn-close").onclick = function() {
    deleteChild();
}