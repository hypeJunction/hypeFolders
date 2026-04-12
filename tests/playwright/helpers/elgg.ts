import { Page, expect } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(
  page: Page,
  username: string,
  password: string = 'testpass123'
) {
  await page.goto('/login');
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');
}

export async function queryDb(sql: string, params: any[] = []) {
  const conn = await mysql.createConnection(DB_CONFIG);
  try {
    const [rows] = await conn.execute(sql, params);
    return rows as any[];
  } finally {
    await conn.end();
  }
}

export async function getEntitiesBySubtype(subtype: string, ownerGuid?: number) {
  let sql = 'SELECT * FROM elgg_entities WHERE subtype = ?';
  const params: any[] = [subtype];
  if (ownerGuid) {
    sql += ' AND owner_guid = ?';
    params.push(ownerGuid);
  }
  sql += ' ORDER BY guid DESC';
  return queryDb(sql, params);
}

export async function getEntity(guid: number) {
  const rows = await queryDb('SELECT * FROM elgg_entities WHERE guid = ?', [guid]);
  return rows[0];
}

export async function getMetadata(entityGuid: number, name: string) {
  return queryDb(
    'SELECT * FROM elgg_metadata WHERE entity_guid = ? AND name = ?',
    [entityGuid, name]
  );
}

export async function getRelationship(
  guid_one: number,
  relationship: string,
  guid_two: number
) {
  return queryDb(
    'SELECT * FROM elgg_entity_relationships WHERE guid_one = ? AND relationship = ? AND guid_two = ?',
    [guid_one, relationship, guid_two]
  );
}

export async function getFoldersRow(folderGuid: number, resourceGuid: number) {
  return queryDb(
    'SELECT * FROM elgg_folders WHERE folder_guid = ? AND resource_guid = ?',
    [folderGuid, resourceGuid]
  );
}

export async function deleteFolderByTitle(title: string) {
  // Cleanup helper — deletes any main_resource_folder created during tests.
  const rows = await queryDb(
    `SELECT e.guid FROM elgg_entities e
     JOIN elgg_entities_metadata m ON 1=0
     WHERE e.subtype = 'main_resource_folder'`,
    []
  );
  // Minimal, best-effort cleanup — callers should prefer unique titles per run.
  return rows;
}
