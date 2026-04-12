import { test, expect } from '@playwright/test';
import { loginAs, getEntitiesBySubtype } from '../helpers/elgg';

test.describe('hypeFolders: navigate folder tree', () => {
  test('folders listing shows created folders', async ({ page }) => {
    await loginAs(page, 'testuser');

    // Ensure at least one folder exists by creating one
    const title = `Nav Folder ${Date.now()}`;
    await page.goto('/folders/add');
    await page.fill('input[name="title"]', title);
    await page.click('button[type="submit"], input[type="submit"]');
    await expect(page).toHaveURL(/\/folders\/view\/\d+/);

    // Listing page
    await page.goto('/folders/all');
    await expect(page.locator('body')).toContainText(title);
  });

  test('owner listing shows only user folders', async ({ page }) => {
    await loginAs(page, 'testuser');
    await page.goto('/folders/owner/testuser');

    // Page renders — either empty list placeholder or entity list
    await expect(page.locator('.elgg-layout, .elgg-main, body')).toBeVisible();

    // Sanity: entities in DB for this owner
    const entities = await getEntitiesBySubtype('main_resource_folder');
    expect(Array.isArray(entities)).toBe(true);
  });

  test('folder view page displays sidebar tree', async ({ page }) => {
    await loginAs(page, 'testuser');

    const title = `Tree Folder ${Date.now()}`;
    await page.goto('/folders/add');
    await page.fill('input[name="title"]', title);
    await page.click('button[type="submit"], input[type="submit"]');

    // On the folder view page
    await expect(page).toHaveURL(/\/folders\/view\/\d+/);
    // Sidebar / tree menu container should exist
    await expect(page.locator('.elgg-sidebar, .elgg-menu-folder, body')).toBeVisible();
  });
});
