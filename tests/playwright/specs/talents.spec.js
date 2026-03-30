const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

test.describe('Talents', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('talents page loads', async ({ page }) => {
    await page.goto('/talents');
    await expect(page).toHaveURL(/talents/);
    await expect(page.locator('body')).toContainText(/talent/i);
  });

  test('create talent form', async ({ page }) => {
    await page.goto('/talents/create');
    await expect(page).toHaveURL(/talents\/create/);
    await expect(page.locator('form')).toBeVisible();
  });

  test('talent appears in list after creation', async ({ page }) => {
    await page.goto('/talents/create');

    // Fill in the talent form
    const nameInput = page.locator('input[name="name"], input[name="title"]');
    if (await nameInput.isVisible()) {
      await nameInput.fill('Playwright Test Talent');
    }

    const descField = page.locator('textarea[name="description"], textarea[name="details"], textarea[name="summary"]');
    if (await descField.isVisible()) {
      await descField.fill('A test talent created by Playwright for end-to-end testing.');
    }

    await page.click('button[type="submit"]');

    // Should redirect to talents list
    await page.goto('/talents');
    await expect(page.locator('body')).toContainText('Playwright Test Talent');
  });

  test('edit talent', async ({ page }) => {
    await page.goto('/talents');

    const editLink = page.locator('a[href*="/talents/edit/"]').first();
    if (await editLink.isVisible()) {
      await editLink.click();
      await expect(page).toHaveURL(/talents\/edit\/\d+/);
      await expect(page.locator('form')).toBeVisible();

      // Modify a field and save
      const nameInput = page.locator('input[name="name"], input[name="title"]');
      if (await nameInput.isVisible()) {
        await nameInput.fill('Updated Talent Name');
      }

      await page.click('button[type="submit"]');
      await expect(page).not.toHaveURL(/talents\/edit/);
    }
  });

  test('toggle talent active/inactive', async ({ page }) => {
    await page.goto('/talents');

    // Look for a toggle button or link
    const toggleBtn = page.locator('button:has-text("Deactivate"), button:has-text("Activate"), form[action*="toggle"] button, a[href*="toggle"]').first();
    if (await toggleBtn.isVisible()) {
      await toggleBtn.click();

      // Page should reload or update to reflect the toggle
      await expect(page.locator('body')).toContainText(/talent/i);
    }
  });
});
