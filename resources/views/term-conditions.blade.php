<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A&A Terms and Conditions</title>
    @vite('resources/css/app.css')

</head>

<body class="bg-gray-100 text-gray-900 leading-normal font-sans">
    <section class="container mx-auto flex flex-col items-center justify-center py-8">
        <h1 class="text-3xl font-bold mb-8">Terms and Conditions</h1>

        <article class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-6 prose">
            <p class="mb-6">
                Welcome to Alvin and Angie Mt. Pinatubo Guesthouse and Tours! By accessing or using our online reservation
                system for guesthouse accommodations and tours, you agree to the following terms and conditions.
            </p>

            <h2 class="text-xl font-bold mb-4">1. Information Collection:</h2>
            <ul class="list-disc list-inside mb-6">
                <li>First and Last Name: For the fulfillment of reservation requirements.</li>
                <li>Nationality and Country: To facilitate effective communication between our business and you, enabling the provision of necessary reservation information and services.</li>
                <li>Contact Number and Email: For necessary communication pertaining to reservation details, updates, and confirmations.</li>
                <li>Birthday: To calculate your age for reservation purposes</li>
                <li>Valid ID Image: For identity verification and fraud prevention. This information is securely stored and not shared with third parties.</li>
            </ul>

            <h2 class="text-xl font-bold mb-4">2. Use of Your Information:</h2>
            <ul class="list-disc list-inside mb-6">
                <li>Reservation Processing: Facilitating the completion of necessary reservation information.</li>
                <li>Communication: Providing updates and essential communication related to your reservation requirements.</li>
                <li>Customer Support: Assisting with inquiries and providing support related to reservation details.</li>
                <li>Improvement of Services: Enhancing our website, services, and user experience.</li>
                {{-- <li>Marketing: Sending promotional offers, newsletters, and service updates.</li> --}}
            </ul>

            <h2 class="text-xl font-bold mb-4">3. Data Security:</h2>
            <p class="mb-6">
                We have implemented measures to protect your personal information from unauthorized access, disclosure, alteration, or destruction. Your valid ID image is securely stored solely for verification purposes.
            </p>
            <h2 class="text-xl font-bold mb-4">4. Third-Party Services:</h2>
            <p class="mb-6">
                We may utilize third-party services, and their respective privacy policies should be reviewed for reference.
            </p>
            <h2 class="text-xl font-bold mb-4">5. Data Retention:</h2>
            <p class="mb-6">
                Your account and data will be deleted upon your request. Your reservation information will be automatically deletes after 180 days if have confirmed and after check-out. If a reservation remains unaccepted for 30 days, your reservation information will be also automatically delete.
            </p>
            <h2 class="text-xl font-bold mb-4">6. Data Privacy Act of 2012 (Republic Act No. 10173):</h2>
            <p class="mb-6">
                We adhere to the provisions of the Data Privacy Act of 2012 (Republic Act No. 10173) to ensure the protection of your personal information.
            </p>
            <h2 class="text-xl font-bold mb-4">7. Your Rights:</h2>
            <ul class="list-disc list-inside mb-6">
                <li>Access to your personal information</li>
                <li>Correction of inaccuracies in your personal information.</li>
                <li>Withdrawal of consent for marketing communications.</li>
                <li>Request for the deletion of your account and data.</li>
                <li>Raising concerns about our handling of your personal information.</li>
            </ul>
            <h2 class="text-xl font-bold mb-4">8. Payment Receipt Screenshot:</h2>
            <p class="mb-6">
                We may request a screenshot of the payment receipt for the downpayment to valid your reservation.
            </p>
            <h2 class="text-xl font-bold mb-4">9. Contact Information:</h2>
            <ul class="list-disc list-inside mb-6">
                <li>Contact: {{isset($contacts['contactno']) ? $contacts['contactno'] : 'None'}}</li>
                <li>Email: <a href="{{isset($contacts['email']) ? 'https://mail.google.com/mail/?view=cm&fs=1&to='.$contacts['email'] : ''}}" target="_blank" rel="noopener noreferrer" class="link link-primary">{{isset($contacts['email']) ? $contacts['email'] : 'None'}}</a></li>
                <li>WhatsApp: <a href="{{isset($contacts['whatsapp']) ? 'https://wa.me/'.$contacts['whatsapp'] : ''}}" target="_blank" rel="noopener noreferrer" class="link link-primary">{{isset($contacts['whatsapp']) ? $contacts['whatsapp'] : 'None'}}</a></li>
                <li>Other Link: <a href="{{isset($contacts['fbuser']) ? $contacts['fbuser'] : ''}}" target="_blank" rel="noopener noreferrer" class="link link-primary">{{isset($contacts['fbuser']) ? 'Facebook Page' : 'None'}}</a></li>
            </ul>

        </article>
    </section>
    @vite('resources/js/app.js')

</body>

</html>