import { test, expect } from '@playwright/test';

// ─── Cross-page Navigation & Smoke Tests ───

test.describe('Navigation & Responsiveness', () => {

  test('homepage responds with 200', async ({ request }) => {
    const response = await request.get('/');
    expect(response.status()).toBe(200);
  });

  test('all public pages return non-500', async ({ request }) => {
    const publicPages = [
      '/',
      '/find-store',
      '/cards',
      '/track',
      '/cart',
      '/print',
      '/print/setup',
      '/print/trays',
    ];
    for (const url of publicPages) {
      const response = await request.get(url);
      expect(response.status(), `${url} should not return 500`).toBeLessThan(500);
    }
  });

  test('no console errors on homepage', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    // Filter out known benign errors (e.g. favicon, third-party)
    const real = errors.filter(e => !e.includes('favicon') && !e.includes('ERR_CONNECTION_REFUSED'));
    expect(real).toHaveLength(0);
  });

  test('mobile viewport renders correctly', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    await expect(page.locator('body')).toBeVisible();
    // No horizontal overflow
    const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 5);
  });
});
