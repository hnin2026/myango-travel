import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/frontend/app.css',
                'resources/css/frontend/navbar.css',
                'resources/css/frontend/hero.css',
                'resources/css/frontend/tours.css',
                'resources/css/frontend/tour-detail.css',
                'resources/css/frontend/booking.css',
                'resources/css/frontend/footer.css',
                'resources/css/frontend/responsive.css',
                'resources/css/admin/admin.css',
                'resources/js/frontend/app.js',
                'resources/js/admin/admin.js',
            ],
            refresh: true,
        }),
    ],
});