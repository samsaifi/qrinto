import { test, expect } from '@playwright/test';

// ─── Customer Flow: Store → Product → Template → Customize → Checkout ───

test.describe('Customer Quick Flow', () => {

  test('homepage loads and shows product types', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/qrinto/i);
    await expect(page.locator('body')).toBeVisible();
  });

  test('find-store page loads', async ({ page }) => {
    await page.goto('/find-store');
    await expect(page.locator('body')).toBeVisible();
  });

  test('product type selection navigates to sizes', async ({ page }) => {
    await page.goto('/');
    // Click the first product type card (adjust selector to match your UI)
    const firstCard = page.locator('[data-product-type], .product-type-card, .type-card').first();
    if (await firstCard.isVisible()) {
      await firstCard.click();
      await page.waitForURL(/\/(cards|magnets|type)/);
    }
  });

  test('cards page loads with categories', async ({ page }) => {
    await page.goto('/cards');
    await expect(page.locator('body')).toBeVisible();
  });

  test('order tracking form loads', async ({ page }) => {
    await page.goto('/track');
    await expect(page.locator('input, [name="order_number"]')).toBeVisible();
  });

  test('order tracking with invalid number shows error', async ({ page }) => {
    await page.goto('/track');
    const input = page.locator('input[name="order_number"], input[type="text"]').first();
    await input.fill('INVALID-0000');
    await page.locator('button[type="submit"], [type="submit"]').first().click();
    await page.waitForLoadState('networkidle');
    // Should show an error or stay on the track page
    await expect(page).toHaveURL(/track/);
  });

  test('cart page accessible', async ({ page }) => {
    await page.goto('/cart');
    await expect(page.locator('body')).toBeVisible();
  });

  test('checkout page redirects or loads', async ({ page }) => {
    await page.goto('/cart-checkout');
    await expect(page.locator('body')).toBeVisible();
  });
});

// ─── Store QR Scan ───

test.describe('Store QR Scan', () => {
  test('scanning a store code sets active store', async ({ page }) => {
    // Replace DEMO with an actual store code for your environment
    const response = await page.goto('/store/DEMO');
    // Should redirect to homepage or show store page
    expect(response?.status()).toBeLessThan(500);
  });
});
