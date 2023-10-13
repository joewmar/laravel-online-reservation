
let imgElements = document.getElementsByClassName('show_img');
let input = document.getElementById('image');
let inputAmount = document.getElementById('amount');
let inputRefNo = document.getElementById('reference_no');
let inputPayName = document.getElementById('payment_name');
let contentInfo = document.getElementById('info');
let btnDone = document.getElementById('done');
let imgReceipt = document.getElementById('receipt');
let lowerTextIMG = null;

contentInfo.style.display = "none";

input.addEventListener("change", () => {
    let files = input.files;
    for (let i = 0; i < imgElements.length; i++) {
        console.log(files); // Tingnan kung tama ang file na nakukuha
        console.log(imgElements[i]); // Tingnan kung tama ang image element na napipili
        if (files[0]) {
            imgElements[i].src = URL.createObjectURL(files[0]);
            contentInfo.style.display = "block";
            imgReceipt.classList.add('h-full');
            imgReceipt.classList.add('w-full');
            imgReceipt.classList.add('object-cover');
            btnDone.style.display = "block";
        }
        else{
            btnDone.style.display = "none";
            imgReceipt.classList.remove('h-full');
            imgReceipt.classList.remove('w-full');
            imgReceipt.classList.remove('object-cover');
            contentInfo.style.display = "none";
        }
    }
});