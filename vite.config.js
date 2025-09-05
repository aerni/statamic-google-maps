import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import statamic from '@statamic/cms/vite-plugin';
import tailwind from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        statamic(),
        tailwind(),
        laravel({
            input: [
                'resources/js/google-maps.js',
                'resources/css/google-maps.css'
            ],
            hotFile: 'dist/hot',
            publicDirectory: 'dist',
        }),
    ],
});
