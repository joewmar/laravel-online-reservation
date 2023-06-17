const inputs = document.querySelectorAll(".passcode-input");
const result = document.getElementById("passcode");
const form = document.getElementById("passform");
const btnhidden = document.getElementById("btn-hidden");

    inputs.forEach((input, key) => {
      if (key !== 0) {
        input.addEventListener("loadeddata", function () {
          inputs[0].focus();
          reset();
        });
      }
      input.addEventListener("keyup", function () {
        if (input.value) {
          if (key === 3) {
            // Last one tadaa
            const userCode = [...inputs].map((input) => input.value).join("");
            result.value = userCode;
            inputs.forEach((input) => {
                input.disabled = true;
            });
            btnhidden.classList.remove('hidden');
          } 
          else {
            inputs[key + 1].focus();
          }
        }
      });
    });

    const reset = () => {
        inputs.forEach((input, key) => {
            input.value = "";
            input.disabled = false;
        });
        result.value = "";
        btnhidden.classList.add('hidden');
    };
