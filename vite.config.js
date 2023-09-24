import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/sass/app.scss',
                'resources/css/loader.css', 
                'resources/css/scrollcolor.css',
                'resources/js/app.js', 
                'resources/js/flatpickr.js',
                'resources/js/flatpickr2.js',
                'resources/js/navbar.js',
                'resources/js/passcode.js',
                'resources/js/reservation-calendar.js',
                'resources/js/payment-image.js',
                'resources/js/camera.js',
                'resources/js/phone.js',
            ],
            refresh: true,
        }),
    ],
});

