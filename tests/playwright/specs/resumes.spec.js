const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

test.describe('Resumes', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('resumes list page loads', async ({ page }) => {
    await page.goto('/resumes');
    await expect(page).toHaveURL(/resumes/);
    await expect(page.locator('body')).toContainText(/resume/i);
  });

  test('create resume page loads', async ({ page }) => {
    await page.goto('/resumes/create');
    await expect(page).toHaveURL(/resumes\/create/);
    await expect(page.locator('form')).toBeVisible();
  });

  test('resume creation', async ({ page }) => {
    await page.goto('/resumes/create');

    // Fill in resume form fields
    const nameInput = page.locator('input[name="name"], input[name="title"]');
    if (await nameInput.isVisible()) {
      await nameInput.fill('Test Resume - Playwright');
    }

    const contentField = page.locator('textarea[name="content"], textarea[name="summary"], textarea[name="text"]');
    if (await contentField.isVisible()) {
      await contentField.fill('Experienced developer with 10 years of PHP, JavaScript, and Python expertise.');
    }

    await page.click('button[type="submit"]');

    // Should redirect to resumes list or resume view
    await expect(page).not.toHaveURL(/resumes\/create/);
  });
});
