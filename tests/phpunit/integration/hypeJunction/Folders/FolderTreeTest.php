<?php

namespace hypeJunction\Folders;

use Elgg\IntegrationTestCase;

/**
 * MainFolder tree operations: addResource, removeResource, getChildren, getParent,
 * getAncestors, getWeight. These hit the custom {dbprefix}folders table written by
 * Bootstrap::activate().
 */
class FolderTreeTest extends IntegrationTestCase {

	private $user;
	private $folder;

	public function up() {
		$this->user = $this->createUser();
		$this->folder = new MainFolder();
		$this->folder->owner_guid = $this->user->guid;
		$this->folder->container_guid = $this->user->guid;
		$this->folder->access_id = ACCESS_PUBLIC;
		$this->folder->title = 'Tree Root';
		$this->folder->save();
	}

	public function down() {
		if ($this->folder) {
			$this->folder->delete();
		}
	}

	public function getPluginID(): string {
		return '';
	}

	private function createResource(string $title = 'Resource'): \ElggObject {
		$obj = new \ElggObject();
		$obj->setSubtype('resource_folder');
		$obj->owner_guid = $this->user->guid;
		$obj->container_guid = $this->user->guid;
		$obj->access_id = ACCESS_PUBLIC;
		$obj->title = $title;
		$obj->save();
		return $obj;
	}

	public function testAddResourceCreatesRelationship(): void {
		$resource = $this->createResource('Child 1');
		$result = $this->folder->addResource($resource->guid);
		$this->assertNotFalse($result);

		$this->assertTrue(
			(bool) check_entity_relationship($resource->guid, 'resource', $this->folder->guid)
		);
		$this->assertNotFalse($this->folder->isResource($resource->guid));

		$resource->delete();
	}

	public function testAddResourceRefusesSelf(): void {
		$this->assertFalse($this->folder->addResource($this->folder->guid));
	}

	public function testAddResourceRefusesWhenResourceEqualsParent(): void {
		$resource = $this->createResource();
		$this->assertFalse($this->folder->addResource($resource->guid, $resource->guid));
		$resource->delete();
	}

	public function testRemoveResourceDropsRelationship(): void {
		$resource = $this->createResource();
		$this->folder->addResource($resource->guid);

		$removed = $this->folder->removeResource($resource->guid);
		$this->assertTrue((bool) $removed);
		$this->assertFalse(
			(bool) check_entity_relationship($resource->guid, 'resource', $this->folder->guid)
		);

		$resource->delete();
	}

	public function testRemoveResourceReturnsFalseForMissing(): void {
		$this->assertFalse($this->folder->removeResource(0));
	}

	public function testGetChildrenReturnsAddedResources(): void {
		$a = $this->createResource('A');
		$b = $this->createResource('B');
		$this->folder->addResource($a->guid);
		$this->folder->addResource($b->guid);

		$children = $this->folder->getChildren();
		$this->assertIsArray($children);
		$this->assertArrayHasKey((int) $a->guid, $children);
		$this->assertArrayHasKey((int) $b->guid, $children);

		$a->delete();
		$b->delete();
	}

	public function testNestedResourceHasCorrectParent(): void {
		$parent = $this->createResource('Parent');
		$child = $this->createResource('Child');

		$this->folder->addResource($parent->guid);
		$this->folder->addResource($child->guid, $parent->guid);

		$resolved = $this->folder->getParent($child->guid);
		$this->assertNotFalse($resolved);
		$this->assertEquals((int) $parent->guid, (int) $resolved->guid);

		$parent->delete();
		$child->delete();
	}

	public function testGetAncestorsReturnsChainToRoot(): void {
		$a = $this->createResource('Ancestor A');
		$b = $this->createResource('Ancestor B');
		$this->folder->addResource($a->guid);
		$this->folder->addResource($b->guid, $a->guid);

		$ancestors = $this->folder->getAncestors($b->guid);
		$this->assertIsArray($ancestors);
		$this->assertGreaterThanOrEqual(2, count($ancestors));

		$a->delete();
		$b->delete();
	}

	public function testRemoveDeletedItemsEventCleansTable(): void {
		$resource = $this->createResource('Doomed');
		$this->folder->addResource($resource->guid);
		$guid = $resource->guid;
		$resource->delete();

		// After deletion, the folders table row should be gone too.
		$dbprefix = \elgg_get_config('dbprefix');
		$rows = \elgg()->db->getData(
			"SELECT id FROM {$dbprefix}folders WHERE resource_guid = :g",
			null,
			[':g' => $guid]
		);
		$this->assertEmpty($rows);
	}
}
