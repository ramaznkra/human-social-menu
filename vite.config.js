import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/order-status.js',
                'resources/js/pages/admin-dashboard.js',
                'resources/js/pages/kitchen.js',
                'resources/js/pages/bar-screen.js',
                'resources/js/pages/menu-spotted.js',
                'resources/js/pages/live-orders.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
