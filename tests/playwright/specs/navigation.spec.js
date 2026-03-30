const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

test.describe('Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('all sidebar links are present', async ({ page }) => {
    await page.goto('/dashboard');

    // Check for sidebar navigation links
    const sidebar = page.locator('nav, .sidebar, #sidebar, [class*="sidebar"]');
    await expect(sidebar.first()).toBeVisible();

    // Verify key navigation links exist
    await expect(page.locator('a[href*="dashboard"]').first()).toBeVisible();
    await expect(page.locator('a[href*="jobs"]').first()).toBeVisible();
    await expect(page.locator('a[href*="proposals"]').first()).toBeVisible();
    await expect(page.locator('a[href*="resumes"]').first()).toBeVisible();
    await expect(page.locator('a[href*="talents"]').first()).toBeVisible();
  });

  test('sidebar links navigate correctly', async ({ page }) => {
    await page.goto('/dashboard');

    // Test jobs link
    await page.locator('a[href*="jobs"]').first().click();
    await expect(page).toHaveURL(/jobs/);

    // Test proposals link
    await page.locator('a[href*="proposals"]').first().click();
    await expect(page).toHaveURL(/proposals/);

    // Test resumes link
    await page.locator('a[href*="resumes"]').first().click();
    await expect(page).toHaveURL(/resumes/);

    // Test talents link
    await page.locator('a[href*="talents"]').first().click();
    await expect(page).toHaveURL(/talents/);
  });

  test('dashboard loads', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('body')).toContainText(/dashboard/i);
  });

  test('user guide page loads', async ({ page }) => {
    await page.goto('/guide');
    await expect(page).toHaveURL(/guide/);
    await expect(page.locator('body')).toContainText(/guide/i);
  });
});
