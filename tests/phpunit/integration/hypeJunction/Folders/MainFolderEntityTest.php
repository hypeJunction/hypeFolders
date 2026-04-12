<?php

namespace hypeJunction\Folders;

use Elgg\IntegrationTestCase;

/**
 * Entity CRUD and class mapping for main_resource_folder + resource_folder subtypes.
 */
class MainFolderEntityTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testMainFolderSubtypeMapping(): void {
		$user = $this->createUser();
		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'Test Folder';
		$this->assertNotFalse($folder->save());

		$loaded = get_entity($folder->guid);
		$this->assertInstanceOf(MainFolder::class, $loaded);
		$this->assertEquals(MainFolder::SUBTYPE, $loaded->getSubtype());

		$folder->delete();
	}

	public function testResourceFolderSubtypeMapping(): void {
		$user = $this->createUser();
		$folder = new Folder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'Child Folder';
		$this->assertNotFalse($folder->save());

		$loaded = get_entity($folder->guid);
		$this->assertInstanceOf(Folder::class, $loaded);
		$this->assertEquals(Folder::SUBTYPE, $loaded->getSubtype());

		$folder->delete();
	}

	public function testMainFolderMetadataPersists(): void {
		$user = $this->createUser();
		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'Meta Folder';
		$folder->description = 'A folder with metadata';
		$this->assertNotFalse($folder->save());

		_elgg_services()->entityCache->delete($folder->guid);
		$loaded = get_entity($folder->guid);
		$this->assertEquals('Meta Folder', $loaded->title);
		$this->assertEquals('A folder with metadata', $loaded->description);

		$folder->delete();
	}

	public function testMainFolderSaveInitialisesPriority(): void {
		$user = $this->createUser();
		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'Priority Folder';
		$this->assertNotFalse($folder->save());

		$this->assertEquals(0, (int) $folder->priority);

		$folder->delete();
	}

	public function testOwnerCanEditNonOwnerCannot(): void {
		$owner = $this->createUser();
		$other = $this->createUser();
		$folder = new MainFolder();
		$folder->owner_guid = $owner->guid;
		$folder->container_guid = $owner->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'Owned Folder';
		$folder->save();

		$this->assertTrue($folder->canEdit($owner->guid));
		$this->assertFalse($folder->canEdit($other->guid));

		$folder->delete();
	}
}
