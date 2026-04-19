<?php

namespace hypeJunction\Folders;

use Elgg\IntegrationTestCase;

/**
 * Smoke tests: ensure key plugin views can render without fatal errors.
 */
class ViewsTest extends IntegrationTestCase {

	public function up() {
		// Load plugin views so elgg_view_form() etc. can find them
		$pluginPath = dirname(__DIR__, 5); // Folders/ -> hypeJunction/ -> integration/ -> phpunit/ -> tests/ -> plugin root
		_elgg_services()->views->registerPluginViews($pluginPath);
		elgg_set_entity_class('object', MainFolder::SUBTYPE, MainFolder::class);
		elgg_set_entity_class('object', Folder::SUBTYPE, Folder::class);
	}

	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testMainFolderObjectViewRenders(): void {
		$user = $this->createUser();
		$session = _elgg_services()->session;
		$session->setLoggedInUser($user);

		try {
$folder = elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
				$f = new MainFolder();
				$f->owner_guid = $user->guid;
				$f->container_guid = $user->guid;
				$f->access_id = ACCESS_PUBLIC;
				$f->title = 'View Folder';
				$f->save();
				return $f;
			});

			$output = \elgg_view_entity($folder);
			$this->assertIsString($output);

			elgg_call(ELGG_IGNORE_ACCESS, fn() => $folder->delete());
		} finally {
			$session->removeLoggedInUser();
		}
	}

	public function testEditFormViewRenders(): void {
		$user = $this->createUser();
		$session = _elgg_services()->session;
		$session->setLoggedInUser($user);

		try {
$output = \elgg_view_form('folders/edit', [], [
				'container_guid' => $user->guid,
			]);
			$this->assertIsString($output);
			$this->assertNotEmpty($output);
		} finally {
			$session->removeLoggedInUser();
		}
	}
}
