import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

export default defineConfig({
    server: {
        https: {
            key: fs.readFileSync('./certs/local-key.pem'),
            cert: fs.readFileSync('./certs/local-cert.pem'),
        },
        host: '0.0.0.0',
        hmr: {
            host: 'template.docker.localhost'
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
