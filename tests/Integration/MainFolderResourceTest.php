<?php

namespace hypeJunction\Folders\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Folders\MainFolder;

/**
 * Write-path coverage for the folder resource tree.
 *
 * MainFolder::addResource()/removeResource() drive the three write-path
 * surfaces that GET render gating never reaches and that broke during the
 * Elgg 7.x migration: the entity-relationship layer (addRelationship /
 * removeRelationship) and the raw insert/delete against the custom `folders`
 * table. See bd elgg-migrate-ifpdo.
 */
class MainFolderResourceTest extends IntegrationTestCase {

	public function up() {
		// The `folders` table is created by the plugin's activate() hook. The
		// integration test DB (c_i_elgg_ prefix) does not run that hook, so
		// provision the table here from the same install SQL, idempotently.
		$sql_file = dirname(__DIR__, 2) . '/install/mysql.sql';
		if (!file_exists($sql_file)) {
			$this->markTestSkipped('folders install SQL not found');
		}

		$conn = _elgg_services()->db->getConnection('write');
		$sql = str_replace('prefix_', elgg_get_config('dbprefix'), file_get_contents($sql_file));
		foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
			if ($stmt) {
				$conn->executeStatement($stmt);
			}
		}
	}

	public function down() {}

	public function testAddGetRemoveResourceRoundTrip() {
		$user = $this->createUser();

		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->title = 'Test folder';
		$this->assertTrue($folder->save());

		$resource = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $user->guid,
		]);

		// Write path #1+#2: add relationship + insert into the folders table.
		$row_id = $folder->addResource($resource->guid);
		$this->assertIsInt($row_id);
		$this->assertGreaterThan(0, $row_id);

		// Relationship was created.
		$this->assertNotFalse($folder->isResource($resource->guid));
		$this->assertTrue($resource->hasRelationship($folder->guid, 'resource'));

		// The custom-table row is visible through the getter join.
		$resources = $folder->getResources();
		$this->assertIsArray($resources);
		$this->assertArrayHasKey((int) $resource->guid, $resources);

		// Write path #3: delete from the folders table + drop the relationship.
		$this->assertTrue($folder->removeResource($resource->guid));
		$this->assertFalse($folder->isResource($resource->guid));
		$this->assertFalse($resource->hasRelationship($folder->guid, 'resource'));

		$resources_after = $folder->getResources();
		$this->assertTrue(
			empty($resources_after) || !array_key_exists((int) $resource->guid, $resources_after)
		);
	}

	public function testRemoveResourceWithoutRelationshipReturnsFalse() {
		$user = $this->createUser();

		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->title = 'Empty folder';
		$this->assertTrue($folder->save());

		$resource = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $user->guid,
		]);

		// Never added — removeResource must no-op cleanly, not fatal.
		$this->assertFalse($folder->removeResource($resource->guid));
	}
}
