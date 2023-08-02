
let imgElements = document.getElementsByClassName('show_id');
let input = document.getElementById('valid_id');
let imageModal = document.getElementById('image_modal');
input.addEventListener("change", () => {
    let files = input.files;
    for (let i = 0; i < imgElements.length; i++) {
        console.log(files); // Tingnan kung tama ang file na nakukuha
        console.log(imgElements[i]); // Tingnan kung tama ang image element na napipili
        if (files[0]) {
            imgElements[i].src = URL.createObjectURL(files[0]);
            imageModal.checked = true;
        }
        else{
            imageModal.checked = false;
        }
    }
});