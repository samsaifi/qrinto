import { test, expect } from '@playwright/test';

// ─── Admin Panel (requires authentication) ───

// Helper: log in before admin tests
async function adminLogin(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.fill('input[name="email"]', process.env.ADMIN_EMAIL || 'admin@example.com');
  await page.fill('input[name="password"]', process.env.ADMIN_PASSWORD || 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(/admin|store|dashboard/);
}

test.describe('Admin Panel', () => {

  test('login page loads', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('admin dashboard loads after login', async ({ page }) => {
    await adminLogin(page);
    await expect(page.locator('body')).toBeVisible();
    await expect(page).toHaveURL(/admin|store|dashboard/);
  });

  test('admin products page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/products');
    await expect(page.locator('body')).toBeVisible();
  });

  test('admin orders page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/orders');
    await expect(page.locator('body')).toBeVisible();
  });

  test('admin categories page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/categories');
    await expect(page.locator('body')).toBeVisible();
  });

  test('admin coupons page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/coupons');
    await expect(page.locator('body')).toBeVisible();
  });

  test('admin stores page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/stores');
    await expect(page.locator('body')).toBeVisible();
  });

  test('admin product-types page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/product-types');
    await expect(page.locator('body')).toBeVisible();
  });
});

// ─── Store Panel ───

test.describe('Store Panel', () => {

  test('store orders page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/store/orders');
    await expect(page.locator('body')).toBeVisible();
  });

  test('store QR page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/store/qr');
    await expect(page.locator('body')).toBeVisible();
  });

  test('store trays page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/store/trays');
    await expect(page.locator('body')).toBeVisible();
  });

  test('store print-logs page loads', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/store/print-logs');
    await expect(page.locator('body')).toBeVisible();
  });
});
