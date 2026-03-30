const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');
const path = require('path');

test.describe('Jobs', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('jobs list page loads', async ({ page }) => {
    await page.goto('/jobs');
    await expect(page).toHaveURL(/jobs/);
    await expect(page.locator('body')).toContainText(/job/i);
  });

  test('create job page loads', async ({ page }) => {
    await page.goto('/jobs/create');
    await expect(page).toHaveURL(/jobs\/create/);
    await expect(page.locator('form')).toBeVisible();
  });

  test('create job with required fields', async ({ page }) => {
    await page.goto('/jobs/create');

    await page.fill('input[name="title"]', 'Test Job - Playwright');
    await page.fill('textarea[name="description"]', 'This is a test job created by Playwright automated tests.');

    // Select platform if dropdown exists
    const platformSelect = page.locator('select[name="platform_id"]');
    if (await platformSelect.isVisible()) {
      await platformSelect.selectOption({ index: 1 });
    }

    await page.click('button[type="submit"]');

    // Should redirect to jobs list or job view
    await expect(page).not.toHaveURL(/jobs\/create/);
  });

  test('job view page shows details', async ({ page }) => {
    await page.goto('/jobs');

    // Click on the first job link if one exists
    const jobLink = page.locator('a[href*="/jobs/view/"]').first();
    if (await jobLink.isVisible()) {
      await jobLink.click();
      await expect(page).toHaveURL(/jobs\/view\/\d+/);
      await expect(page.locator('body')).toContainText(/job/i);
    }
  });

  test('edit job page loads', async ({ page }) => {
    await page.goto('/jobs');

    const editLink = page.locator('a[href*="/jobs/edit/"]').first();
    if (await editLink.isVisible()) {
      await editLink.click();
      await expect(page).toHaveURL(/jobs\/edit\/\d+/);
      await expect(page.locator('form')).toBeVisible();
    }
  });

  test('file upload on job create', async ({ page }) => {
    await page.goto('/jobs/create');

    const fileInput = page.locator('input[type="file"]');
    if (await fileInput.isVisible()) {
      const fixturePath = path.resolve(__dirname, '../fixtures/sample.txt');
      await fileInput.setInputFiles(fixturePath);
      await expect(fileInput).not.toHaveValue('');
    }
  });
});
