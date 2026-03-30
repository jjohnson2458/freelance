const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

test.describe('Authentication', () => {
  test('login page loads', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveURL(/login/);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('login with valid credentials redirects to dashboard', async ({ page }) => {
    await login(page);
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('body')).toContainText(/dashboard/i);
  });

  test('login with invalid credentials shows error', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'bad@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/login/);
    await expect(page.locator('.alert-danger, .alert-error, .text-danger')).toBeVisible();
  });

  test('logout works', async ({ page }) => {
    await login(page);
    await page.goto('/logout');
    await expect(page).toHaveURL(/login/);
  });

  test('protected routes redirect to login', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/login/);
  });
});
