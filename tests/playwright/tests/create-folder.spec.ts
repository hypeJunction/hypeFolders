import { test, expect } from '@playwright/test';
import { loginAs, getEntitiesBySubtype } from '../helpers/elgg';

test.describe('hypeFolders: create main folder', () => {
  test('user creates a main resource folder via form', async ({ page }) => {
    const title = `E2E Folder ${Date.now()}`;

    await loginAs(page, 'testuser');
    await page.goto('/folders/add');

    await expect(page.locator('form')).toBeVisible();
    await page.fill('input[name="title"]', title);
    await page.fill('textarea[name="description"]', 'Created by Playwright');
    await page.click('button[type="submit"], input[type="submit"]');

    // UI: redirect to folders/view/<guid>
    await expect(page).toHaveURL(/\/folders\/view\/\d+/);
    await expect(page.locator('h1, .elgg-heading-main')).toContainText(title);

    // DB: entity with subtype main_resource_folder exists
    const entities = await getEntitiesBySubtype('main_resource_folder');
    expect(entities.length).toBeGreaterThan(0);
    const latest = entities[0];
    expect(latest.type).toBe('object');
    expect(Number(latest.guid)).toBeGreaterThan(0);
  });

  test('unauthenticated user is blocked from folders/add', async ({ page }) => {
    const response = await page.goto('/folders/add');
    // Gatekeeper middleware should redirect to /login (302) or render a 403.
    const status = response?.status() ?? 0;
    expect([200, 302, 401, 403]).toContain(status);
    // If redirected, should end at login form
    if (status === 200) {
      await expect(page).toHaveURL(/\/login/);
    }
  });
});
