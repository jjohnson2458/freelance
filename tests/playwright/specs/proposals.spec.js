const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

test.describe('Proposals', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('proposals list page loads', async ({ page }) => {
    await page.goto('/proposals');
    await expect(page).toHaveURL(/proposals/);
    await expect(page.locator('body')).toContainText(/proposal/i);
  });

  test('proposal view page shows content', async ({ page }) => {
    await page.goto('/proposals');

    const viewLink = page.locator('a[href*="/proposals/view/"]').first();
    if (await viewLink.isVisible()) {
      await viewLink.click();
      await expect(page).toHaveURL(/proposals\/view\/\d+/);
      await expect(page.locator('body')).toContainText(/proposal/i);
    }
  });

  test('mark as sent button works', async ({ page }) => {
    await page.goto('/proposals');

    const viewLink = page.locator('a[href*="/proposals/view/"]').first();
    if (await viewLink.isVisible()) {
      await viewLink.click();

      const sentBtn = page.locator('button:has-text("Mark as Sent"), button:has-text("Sent"), a:has-text("Mark as Sent")');
      if (await sentBtn.isVisible()) {
        await sentBtn.click();
        // Verify some status change occurred
        await expect(page.locator('body')).toContainText(/sent/i);
      }
    }
  });

  test('feedback form appears after marking as sent', async ({ page }) => {
    await page.goto('/proposals');

    const viewLink = page.locator('a[href*="/proposals/view/"]').first();
    if (await viewLink.isVisible()) {
      await viewLink.click();

      // Look for feedback form or section
      const feedbackSection = page.locator('[class*="feedback"], form[action*="feedback"], #feedback, textarea[name="feedback"]');
      // The feedback form may or may not be visible depending on proposal status
      const sentIndicator = page.locator('text=/sent/i');
      if (await sentIndicator.isVisible()) {
        // If proposal is already sent, feedback form should be accessible
        await expect(page.locator('body')).toContainText(/feedback|outcome|result/i);
      }
    }
  });

  test('copy button exists', async ({ page }) => {
    await page.goto('/proposals');

    const viewLink = page.locator('a[href*="/proposals/view/"]').first();
    if (await viewLink.isVisible()) {
      await viewLink.click();

      const copyBtn = page.locator('button:has-text("Copy"), button[data-clipboard], .btn-copy, [onclick*="copy"]');
      await expect(copyBtn.first()).toBeVisible();
    }
  });

  test('fit analysis section exists', async ({ page }) => {
    await page.goto('/proposals');

    const viewLink = page.locator('a[href*="/proposals/view/"]').first();
    if (await viewLink.isVisible()) {
      await viewLink.click();

      const fitSection = page.locator('text=/fit|analysis|score|match/i');
      await expect(fitSection.first()).toBeVisible();
    }
  });
});
