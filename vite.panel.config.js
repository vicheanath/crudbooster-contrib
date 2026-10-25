import { defineConfig } from 'vite'
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
  build: {
    outDir: 'src/assets/panel',
    emptyOutDir: true,
    target: 'es2020',
    reportCompressedSize: false,
    rollupOptions: {
      input: 'core/resources/js/panel.jsx',
      output: {
        entryFileNames: 'panel.js',
        chunkFileNames: 'panel-[name].js',
        assetFileNames: 'panel[extname]',
      },
    },
  },
  test: {
    environment: 'jsdom',
    include: ['core/resources/js/**/*.test.{js,jsx}'],
    setupFiles: ['core/resources/js/test/setup.js'],
  },
})
