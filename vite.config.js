import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/custom-slider.css', 
                'resources/css/loader.css', 
                'resources/css/scrollcolor.css', 
                'resources/js/app.js',
                'resources/js/reservation-calendar.js',
                'resources/js/system-navbar.js',
                'resources/js/navbar.js',
                'resources/js/custom-slider.js',
            ],
            refresh: true,
        }),
    ],
});
