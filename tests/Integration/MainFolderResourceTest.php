<?php

namespace hypeJunction\Folders\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Folders\MainFolder;

class MainFolderResourceTest extends IntegrationTestCase {

	public function up() {
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

		// Write-path mechanics test. The logged-out test session (User 0) cannot
		// write to the user's container, so run the writes under ELGG_IGNORE_ACCESS
		// — this isolates the addResource()/removeResource() DB path (relationship
		// + folders-table insert/delete), which is what this test covers.
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			$folder = new MainFolder();
			$folder->owner_guid = $user->guid;
			$folder->container_guid = $user->guid;
			$folder->title = 'Test folder';
			$this->assertTrue($folder->save());

			$resource = $this->createObject([
				'subtype' => 'file',
				'owner_guid' => $user->guid,
			]);

			$row_id = $folder->addResource($resource->guid);
			$this->assertIsInt($row_id);
			$this->assertGreaterThan(0, $row_id);

			$this->assertNotFalse($folder->isResource($resource->guid));
			$this->assertTrue($resource->hasRelationship($folder->guid, 'resource'));

			$resources = $folder->getResources();
			$this->assertIsArray($resources);
			$this->assertArrayHasKey((int) $resource->guid, $resources);

			$this->assertTrue($folder->removeResource($resource->guid));
			$this->assertFalse($folder->isResource($resource->guid));
			$this->assertFalse($resource->hasRelationship($folder->guid, 'resource'));

			$resources_after = $folder->getResources();
			$this->assertTrue(
				empty($resources_after) || !array_key_exists((int) $resource->guid, $resources_after)
			);
		});
	}

	public function testRemoveResourceWithoutRelationshipReturnsFalse() {
		$user = $this->createUser();

		elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			$folder = new MainFolder();
			$folder->owner_guid = $user->guid;
			$folder->container_guid = $user->guid;
			$folder->title = 'Empty folder';
			$this->assertTrue($folder->save());

			$resource = $this->createObject([
				'subtype' => 'file',
				'owner_guid' => $user->guid,
			]);

			$this->assertFalse($folder->removeResource($resource->guid));
		});
	}
}
