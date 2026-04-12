<?php

namespace hypeJunction\Folders;

use Elgg\IntegrationTestCase;

/**
 * Router::entityUrlHandler URL generation for folder entities.
 */
class RouterTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testMainFolderUrlPointsToViewRoute(): void {
		$user = $this->createUser();
		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'Url Folder';
		$folder->save();

		$url = Router::entityUrlHandler('entity:url', 'object', '', ['entity' => $folder]);
		$this->assertIsString($url);
		$this->assertStringContainsString("folders/view/{$folder->guid}", $url);

		$folder->delete();
	}

	public function testFolderRouteHandlerKnownSubpagesReturnTrue(): void {
		// The handler echoes resources — capture output so the test does not pollute PHPUnit.
		ob_start();
		try {
			$handled = Router::handleFolders(['all']);
		} finally {
			ob_end_clean();
		}
		$this->assertTrue($handled);
	}

	public function testFolderRouteHandlerUnknownPageReturnsFalse(): void {
		ob_start();
		try {
			$handled = Router::handleFolders(['no-such-subpage']);
		} finally {
			ob_end_clean();
		}
		$this->assertFalse($handled);
	}
}
