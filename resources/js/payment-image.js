import { createWorker } from 'tesseract.js';

let imgElements = document.getElementsByClassName('show_img');
let input = document.getElementById('image');
let inputAmount = document.getElementById('amount');
let inputRefNo = document.getElementById('reference_no');
let inputPayName = document.getElementById('payment_name');
let btnVerify = document.getElementById('verify');
let btnDone = document.getElementById('done');
let contentInfo = document.getElementById('info');
let lowerTextIMG = null;

contentInfo.style.display = "none";
btnDone.style.display = "none";
btnVerify.style.display = "none";


let processImage = async (imgFile) => {
    const worker = await createWorker({
        logger: m => {
            console.log(m)
        }
    });
    await worker.loadLanguage('eng');
    await worker.initialize('eng');
    let { data: { text } } = await worker.recognize(imgFile);
    lowerTextIMG = String(text.toLowerCase());
    console.log(typeof text)
    await worker.terminate();
}

input.addEventListener("change", () => {

    let files = input.files;
    for (let i = 0; i < imgElements.length; i++) {
        console.log(files); // Tingnan kung tama ang file na nakukuha
        console.log(imgElements[i]); // Tingnan kung tama ang image element na napipili
        if (files[0]) {
            imgElements[i].src = URL.createObjectURL(files[0]);
            console.log(imgElements);
            contentInfo.style.display = "block";
            btnVerify.style.display = "block";
            btnDone.style.display = "none";
            processImage(imgElements[0].src);
        }
        else{
            btnVerify.style.display = "none";
            btnDone.style.display = "none";
            contentInfo.style.display = "none";
        }
    }
});
btnVerify.addEventListener("click", () => {
    let amount = Number(inputAmount.value).toFixed(2).toLocaleString('en-US');
    amount = Number(amount).toLocaleString('en-US');
    let refNo = inputRefNo.value
    let payName = inputPayName.value;

    if((!amount) && (!refNo) && (!payName)) alert('Error: Fill up completely');
    else{

        let isVerified = false;
          console.log(lowerTextIMG);
          console.log(amount);
          console.log(refNo);
          console.log(payName);
          if(lowerTextIMG.includes(amount) && lowerTextIMG.includes('gcash') && lowerTextIMG.includes(refNo)){
              isVerified = true;
          }
          else if(lowerTextIMG.includes(amount)){
            alert('Error: Does not match between your amount and image you send');
            btnDone.style.display = "none";
          }
          else if(lowerTextIMG.includes(refNo)){
            alert('Error: Does not match between your reference no. and image you send');
            btnDone.style.display = "none";
          }
          else if(lowerTextIMG.includes("gcash")){
            alert('Error: You sent the wrong image.');
            btnDone.style.display = "none";
          }
          else{
            alert('Error: Does not match between your information and image you send');
            btnDone.style.display = "none";
        }
        if(isVerified){
            btnDone.style.display = "block";
            btnVerify.style.display = "none";
            alert('Success! Let be continue');
        }

    }
});
