// @ts-check
import { defineConfig } from 'astro/config';
import { loadEnv } from 'vite';

import tailwindcss from '@tailwindcss/vite';
import sitemap from '@astrojs/sitemap';

const env = loadEnv(process.env.MODE ?? 'production', process.cwd(), '');
const siteUrl = env.SITE || 'https://example.by';

// https://astro.build/config
export default defineConfig({
  site: siteUrl,

  vite: {
    plugins: [tailwindcss()],
  },

  integrations: [
    sitemap({
      filter: (page) => !page.includes('/api/'),
    }),
  ],
});
