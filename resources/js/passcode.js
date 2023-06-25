const inputs = document.querySelectorAll(".passcode-input");
const result = document.getElementById("passcode");
const form = document.getElementById("passform");
const btnhidden = document.getElementById("btn-hidden");

    inputs.forEach((input, key) => {
      if (key !== 0) {
        form.addEventListener("load", function () {
          inputs[0].focus();
        });
      }
      input.addEventListener("keyup", function () {

        if (input.value) {
            if (key === 3) {
              // Last one tadaa
              const userCode = [...inputs].map((input) => input.value).join("");
              result.value = userCode;

              inputs[key].disabled = true;
              btnhidden.classList.remove('hidden');
            }
            else {
              inputs[key].disabled = true;
              inputs[key + 1].focus();
              btnhidden.classList.add('hidden');
            }
        }
      });
      input.addEventListener("keydown", function (event) {
        if (event.keyCode == 8) {
          inputs[key].value = null;
          inputs[key].focus();

          // inputs[key - 1].disabled = false;
          // inputs[key - 1].focus();
          btnhidden.classList.add('hidden');
          if(key == 0) inputs[key].focus();
          
        }
      });
    });

