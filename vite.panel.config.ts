import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

/**
 * Builds the Tailwind theme's assets (core/resources/js, core/resources/css) into
 * src/assets/panel, which `php artisan vendor:publish --tag=cb_asset` copies to
 * public/vendor/crudbooster/panel. The output is committed, so installs need no Node.js.
 */
export default defineConfig({
  plugins: [react(), tailwindcss()],
  publicDir: false,
  // Asset URLs relative to the stylesheet, wherever public/vendor/crudbooster/panel is served from.
  base: './',
  resolve: {
    alias: { '@': fileURLToPath(new URL('./core/resources/js', import.meta.url)) },
  },
  build: {
    outDir: 'src/assets/panel',
    emptyOutDir: true,
    target: 'es2020',
    reportCompressedSize: false,
    rollupOptions: {
      input: 'core/resources/js/panel.tsx',
      output: {
        entryFileNames: 'panel.js',
        chunkFileNames: 'panel-[name].js',
        // The stylesheet is panel.css; fonts keep their own (hashed) names next to it.
        assetFileNames: (asset) => (asset.names?.some((name) => name.endsWith('.css')) ? 'panel[extname]' : 'fonts/[name]-[hash][extname]'),
      },
    },
  },
  test: {
    environment: 'jsdom',
    include: ['core/resources/js/**/*.test.{ts,tsx}'],
    setupFiles: ['core/resources/js/test/setup.ts'],
  },
})
