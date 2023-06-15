@props(['id'=>'', 'formId'=> '', 'title'=> ''])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle" id="{{$id}}">
    <div class="modal-box">
      <h3 class="font-bold text-lg">{{$title}}</h3>
      <p class="py-4"><x-input type="password" id="passcode" name="passcode" placeholder="Enter passcode to confirm" maxlength="4" /></p>
      <div class="modal-action">
        <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl">✕</label>
        <label for="{{$id}}" class="btn btn-primary" onclick="event.preventDefault(); document.getElementById('{{$formId}}').submit();">Proceed</label>
      </div>
    </div>
  </div>

  {{-- <div>
    <form>
      <input type="password" maxlength=1 />
      <input type="password" maxlength=1 />
      <input type="password" maxlength=1 />
      <input type="password" maxlength=1 />
    </form>
    <div id="code-block" class="special hidden">
      Wait your special code is <span id="code"></span>?
      <br />
      <i onclick="reset()">Reset
      </i>
    </div>
  </div> --}}

  {{-- Css --}}
  {{-- body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    font-family: Roboto, "Helvetica Neue", Arial, sans-serif;
  }
  form {
    display: flex;
    justify-content: center;
  }
  input {
    margin: 0 0.5rem;
    padding: 0.5rem;
    border: 1px solid #333;
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 3rem;
  }
  .special {
    margin-top: 2rem;
    font-size: 2rem;
    opacity: 1;
    visibility: visible;
    transition: all 0.3s ease;
    i {
      font-size: 1rem;
      cursor: pointer;
    }
    &.hidden {
      opacity: 0;
      visibility: hidden;
    }
  }
   --}}

   {{-- Javascript --}}
    {{-- const inputs = document.querySelectorAll("input");
    const codeBlock = document.getElementById("code-block");
    const code = document.getElementById("code");
    const form = document.querySelector("form");

    inputs.forEach((input, key) => {
      if (key !== 0) {
        input.addEventListener("click", function () {
          inputs[0].focus();
        });
      }
      input.addEventListener("keyup", function () {
        if (input.value) {
          if (key === 3) {
            // Last one tadaa
            const userCode = [...inputs].map((input) => input.value).join("");
            codeBlock.classList.remove("hidden");
            code.innerText = userCode;
          } else {
            inputs[key + 1].focus();
          }
        }
      });
    });

    const reset = () => {
      form.reset();
      codeBlock.classList.add("hidden");
      code.innerText = "";
    }; --}}
