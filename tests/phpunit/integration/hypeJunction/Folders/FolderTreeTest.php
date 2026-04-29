<?php

namespace hypeJunction\Folders;

use Elgg\Database\Select;
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
		// Register the delete event handler so removeDeletedItems fires in tests
		elgg_register_event_handler('delete', 'object', [MainFolder::class, 'removeDeletedItems'], 999);

		$this->user = $this->createUser();
$this->folder = elgg_call(ELGG_IGNORE_ACCESS, function () {
			$f = new MainFolder();
			$f->owner_guid = $this->user->guid;
			$f->container_guid = $this->user->guid;
			$f->access_id = ACCESS_PUBLIC;
			$f->title = 'Tree Root';
			$f->save();
			return $f;
		});
	}

	public function down() {
		if ($this->folder) {
			elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->folder->delete());
		}
	}

	public function getPluginID(): string {
		return '';
	}

	private function createResource(string $title = 'Resource'): \ElggObject {
return elgg_call(ELGG_IGNORE_ACCESS, function () use ($title) {
			$obj = new \ElggObject();
			$obj->setSubtype('resource_folder');
			$obj->owner_guid = $this->user->guid;
			$obj->container_guid = $this->user->guid;
			$obj->access_id = ACCESS_PUBLIC;
			$obj->title = $title;
			$obj->save();
			return $obj;
		});
	}

	public function testAddResourceCreatesRelationship(): void {
		$resource = $this->createResource('Child 1');
		$result = elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->folder->addResource($resource->guid));
		$this->assertNotFalse($result);

$this->assertTrue(
			(bool) _elgg_services()->relationshipsTable->check($resource->guid, 'resource', $this->folder->guid)
		);
		$this->assertNotFalse($this->folder->isResource($resource->guid));

		elgg_call(ELGG_IGNORE_ACCESS, fn() => $resource->delete());
	}

	public function testAddResourceRefusesSelf(): void {
		$this->assertFalse($this->folder->addResource($this->folder->guid));
	}

	public function testAddResourceRefusesWhenResourceEqualsParent(): void {
		$resource = $this->createResource();
		$this->assertFalse($this->folder->addResource($resource->guid, $resource->guid));
		elgg_call(ELGG_IGNORE_ACCESS, fn() => $resource->delete());
	}

	public function testRemoveResourceDropsRelationship(): void {
		$resource = $this->createResource();
		elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->folder->addResource($resource->guid));

		$removed = elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->folder->removeResource($resource->guid));
		$this->assertTrue((bool) $removed);
$this->assertFalse(
			(bool) _elgg_services()->relationshipsTable->check($resource->guid, 'resource', $this->folder->guid)
		);

		elgg_call(ELGG_IGNORE_ACCESS, fn() => $resource->delete());
	}

	public function testRemoveResourceReturnsFalseForMissing(): void {
		$this->assertFalse($this->folder->removeResource(0));
	}

	public function testGetChildrenReturnsAddedResources(): void {
		$a = $this->createResource('A');
		$b = $this->createResource('B');
elgg_call(ELGG_IGNORE_ACCESS, function () use ($a, $b) {
			$this->folder->addResource($a->guid);
			$this->folder->addResource($b->guid);
		});

		$children = $this->folder->getChildren();
		$this->assertIsArray($children);
		$this->assertArrayHasKey((int) $a->guid, $children);
		$this->assertArrayHasKey((int) $b->guid, $children);

elgg_call(ELGG_IGNORE_ACCESS, function () use ($a, $b) {
			$a->delete();
			$b->delete();
		});
	}

	public function testNestedResourceHasCorrectParent(): void {
		$parent = $this->createResource('Parent');
		$child = $this->createResource('Child');

elgg_call(ELGG_IGNORE_ACCESS, function () use ($parent, $child) {
			$this->folder->addResource($parent->guid);
			$this->folder->addResource($child->guid, $parent->guid);
		});

		$resolved = $this->folder->getParent($child->guid);
		$this->assertNotFalse($resolved);
		$this->assertEquals((int) $parent->guid, (int) $resolved->guid);

elgg_call(ELGG_IGNORE_ACCESS, function () use ($parent, $child) {
			$parent->delete();
			$child->delete();
		});
	}

	public function testGetAncestorsReturnsChainToRoot(): void {
		$a = $this->createResource('Ancestor A');
		$b = $this->createResource('Ancestor B');
elgg_call(ELGG_IGNORE_ACCESS, function () use ($a, $b) {
			$this->folder->addResource($a->guid);
			$this->folder->addResource($b->guid, $a->guid);
		});

		$ancestors = $this->folder->getAncestors($b->guid);
		$this->assertIsArray($ancestors);
		$this->assertGreaterThanOrEqual(2, count($ancestors));

elgg_call(ELGG_IGNORE_ACCESS, function () use ($a, $b) {
			$a->delete();
			$b->delete();
		});
	}

	public function testRemoveDeletedItemsEventCleansTable(): void {
		$resource = $this->createResource('Doomed');
		elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->folder->addResource($resource->guid));
		$guid = $resource->guid;
		elgg_call(ELGG_IGNORE_ACCESS, fn() => $resource->delete());

		// After deletion, the folders table row should be gone too.
		$select = Select::fromTable('folders');
		$select->select('id')->where($select->compare('resource_guid', '=', $guid, ELGG_VALUE_GUID));
		$rows = \elgg()->db->getData($select);
		$this->assertEmpty($rows);
	}
}
