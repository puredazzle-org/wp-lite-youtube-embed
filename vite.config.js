import tailwindcss from '@tailwindcss/vite';
import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';

const currentDir = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  publicDir: 'resources/static',
  server: {
    cors: true,
    port: 5174,
    strictPort: true,
    fs: {
      strict: false,
    },
  },
  build: {
    manifest: true,
    emptyOutDir: true,
    outDir: path.resolve(currentDir, 'build'),
    assetsDir: '',
    rollupOptions: {
      input: {
        index: 'resources/js/index.js',
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[ext]',
      },
    },
  },
  plugins: [tailwindcss()],
});
