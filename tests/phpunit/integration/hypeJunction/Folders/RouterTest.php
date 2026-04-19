<?php

namespace hypeJunction\Folders;

use Elgg\HooksRegistrationService\Hook;
use Elgg\IntegrationTestCase;

/**
 * Router::entityUrlHandler URL generation for folder entities.
 */
class RouterTest extends IntegrationTestCase {

	public function up() {
		// Load plugin views so elgg_view_resource() can find them
		$pluginPath = dirname(__DIR__, 5); // Folders/ -> hypeJunction/ -> integration/ -> phpunit/ -> tests/ -> plugin root
		_elgg_services()->views->registerPluginViews($pluginPath);
	}

	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testMainFolderUrlPointsToViewRoute(): void {
		$user = $this->createUser();
$folder = elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			$f = new MainFolder();
			$f->owner_guid = $user->guid;
			$f->container_guid = $user->guid;
			$f->access_id = ACCESS_PUBLIC;
			$f->title = 'Url Folder';
			$f->save();
			return $f;
		});

		$hook = new Hook(elgg(), 'entity:url', 'object', '', ['entity' => $folder]);
		$url = Router::entityUrlHandler($hook);
		$this->assertIsString($url);
		$this->assertStringContainsString("folders/view/{$folder->guid}", $url);

		elgg_call(ELGG_IGNORE_ACCESS, fn() => $folder->delete());
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
