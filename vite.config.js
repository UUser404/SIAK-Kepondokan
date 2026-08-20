// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
//     server: {
//         host: '0.0.0.0',
//         port: 5173, // <--- Kembalikan ke port default Vite
//         strictPort: true,
//         hmr: {
//             host: '192.168.193.143',
//         },
//     },
// });

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Dihapus config `server.hmr.host` yang di-hardcode ke IP LAN
    // ('192.168.0.119') -- itu cuma perlu kalau kamu akses dari device LAIN
    // di jaringan yang sama (misal test dari HP). Karena kamu akses dari
    // 127.0.0.1:8000 (mesin yang sama), Vite otomatis pakai host yang benar
    // tanpa perlu di-hardcode -- kalau di-hardcode ke IP yang beda dari cara
    // akses sebenarnya, browser gagal connect ke dev server & asset CSS/JS
    // bisa ikut gagal ke-load.
});