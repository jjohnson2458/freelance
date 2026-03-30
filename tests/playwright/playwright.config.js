const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './specs',
  timeout: 30000,
  retries: 1,
  use: {
    baseURL: process.env.BASE_URL || 'https://freelance.visionquest2020.net',
    headless: true,
    screenshot: 'only-on-failure',
    trace: 'on-first-retry',
  },
  projects: [
    { name: 'chromium', use: { browserName: 'chromium' } },
  ],
});
