## Laravel 9 - Vite - Traefik - Tailwind - Filament

This project presents the vite hot reload feature working through reverse proxy with traefik.

If you have any improvement ideas, please let me know.

### Execution

To execute the project:

1. Rename `.env.exemple` to `.env`
2. Run `docker-compose up`

Check if all node related files are not with root permission before running.

If you have certificate problems, generate another one using mkcert.

### Vite configuration

No extra action is required, the following information is for educational purposes.

Vite configuration for traefik reverse proxy:

```
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
```

Vite configuration for localhost:

```
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        hmr: {
            host: '0.0.0.0'
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

### License

The MIT License (MIT).