import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';

const phoneInputField = document.querySelector("#phone");
const phoneInput = intlTelInput(phoneInputField);

// Set the initial country code based on the old input value
const initialCountryCode = document.querySelector("#phone_code").value;
if (initialCountryCode) {
    phoneInput.setCountry(initialCountryCode);
}

// Listen for changes in the phone number input
phoneInputField.addEventListener("countrychange", function () {
    const selectedCountryData = phoneInput.getSelectedCountryData();
    const countryCode = selectedCountryData.iso2;

    // Update the hidden input field with the selected country code
    document.querySelector("#phone_code").value = countryCode;
});