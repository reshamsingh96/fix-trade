import { fileURLToPath, URL } from "url";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import vuetify from "vite-plugin-vuetify";

export default defineConfig({
    server: {
        proxy: {
            "/api": process.env.APP_URL, // Proxy to Laravel backend
        },
    },
    plugins: [
        vue(),
        laravel({
            input: [
                "resources/scss/app.scss",
                "resources/scss/frontend.scss",
                "resources/scss/media.scss",
                "resources/js/app.js",
                "resources/js/frontend.js",
            ],
            refresh: true,
        }),
        vuetify({
            autoImport: true,
            styles: { configFile: "resources/scss/variables.scss" },
        }),
    ],
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./resources", import.meta.url)), // Alias for 'resources'
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                // additionalData: `@use "@/scss/variables.scss";`, // Automatically include global variables
                additionalData: `@use "@/scss/variables.scss" as *;`, // ✅ Use `as *` to expose variables without namespace
                includePaths: ['resources/scss']
            },
        },
    },
    optimizeDeps: {
        exclude: ["vuetify"],
        entries: ["./resources/**/*.vue"],
    },
});
