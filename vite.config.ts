import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { bunny } from 'laravel-vite-plugin/fonts';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import inertia from '@inertiajs/vite';
import path from 'path';

// Change this to your host computer's local network IP address
const localIp = '172.16.0.123'; 

export default defineConfig({
    server: {
        host: '0.0.0.0', // Tells Vite to listen to all network interfaces
        port: 5173,
        cors: true,
        hmr: {
            host: localIp, // Tells the browser where to actually fetch the assets from
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react(),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
});