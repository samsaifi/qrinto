import { test, expect } from '@playwright/test';

// ─── Local Print Flow (Direct 931BL Printing) ───

test.describe('Local Print Flow', () => {

  test('start page loads', async ({ page }) => {
    await page.goto('/print');
    await expect(page.locator('body')).toBeVisible();
  });

  test('design selection page loads', async ({ page }) => {
    await page.goto('/print/design');
    await expect(page.locator('body')).toBeVisible();
  });

  test('size selection page loads', async ({ page }) => {
    await page.goto('/print/own-design/sizes');
    await expect(page.locator('body')).toBeVisible();
  });

  test('PDF upload page loads', async ({ page }) => {
    await page.goto('/print/pdf');
    await expect(page.locator('body')).toBeVisible();
  });

  test('tray setup page loads', async ({ page }) => {
    await page.goto('/print/trays');
    await expect(page.locator('body')).toBeVisible();
  });

  test('printer setup page loads', async ({ page }) => {
    await page.goto('/print/setup');
    await expect(page.locator('body')).toBeVisible();
  });
});
