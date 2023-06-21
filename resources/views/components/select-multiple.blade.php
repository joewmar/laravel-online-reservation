<input type="text   " class="form-select" id="selectedValues" list="list" />
<datalist id="list">
  <option value="1">First one</option>
  <option value="2">Second one</option>
</datalist>
  
  <script>
    const selectElement = document.getElementById("list");
    const inputElement = document.getElementById("selectedValues");
    let inputElement.value = "";
    selectElement.addEventListener("change", function() {
        const selectedOptions = Array.from(this.selectedOptions).map(option => option.value);
        inputElement.value += selectedOptions.join(", ");
    });
  </script>