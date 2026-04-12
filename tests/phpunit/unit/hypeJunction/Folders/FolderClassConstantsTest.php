<?php

namespace hypeJunction\Folders;

use Elgg\UnitTestCase;

/**
 * Pure-PHP checks on the Folder and MainFolder classes.
 */
class FolderClassConstantsTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	public function testMainFolderSubtypeConstant(): void {
		$this->assertEquals('main_resource_folder', MainFolder::SUBTYPE);
	}

	public function testResourceFolderSubtypeConstant(): void {
		$this->assertEquals('resource_folder', Folder::SUBTYPE);
	}

	public function testMainFolderExtendsElggObject(): void {
		$this->assertTrue(is_subclass_of(MainFolder::class, \ElggObject::class));
	}

	public function testResourceFolderExtendsElggObject(): void {
		$this->assertTrue(is_subclass_of(Folder::class, \ElggObject::class));
	}
}
