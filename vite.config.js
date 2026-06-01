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
                'resources/js/pages/menu-cart.js',
                'resources/js/pages/admin-products.js',
                'resources/js/pages/admin-product-form.js',
                'resources/js/pages/admin-product-options.js',
                'resources/js/pages/admin-locale-tabs.js',
                'resources/js/pages/admin-tables.js',
                'resources/js/pages/admin-waiters.js',
                'resources/js/pages/admin-categories.js',
                'resources/js/pages/admin-catalog-view.js',
                'resources/js/pages/waiter-dashboard.js',
                'resources/js/pages/live-orders.js',
                'resources/js/pages/admin-manual-order.js',
                'resources/js/pages/admin-shell.js',
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
