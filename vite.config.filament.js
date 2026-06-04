import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/filament/admin/theme.css',
                'resources/css/shepherd-tour.css',
            ],
            buildDirectory: 'build/filament',
            refresh: true,
        }),
        tailwindcss(),
    ],
});
