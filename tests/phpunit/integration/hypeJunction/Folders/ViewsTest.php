<?php

namespace hypeJunction\Folders;

use Elgg\IntegrationTestCase;

/**
 * Smoke tests: ensure key plugin views can render without fatal errors.
 */
class ViewsTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testMainFolderObjectViewRenders(): void {
		$user = $this->createUser();
		\elgg_get_session()->setLoggedInUser($user);

		$folder = new MainFolder();
		$folder->owner_guid = $user->guid;
		$folder->container_guid = $user->guid;
		$folder->access_id = ACCESS_PUBLIC;
		$folder->title = 'View Folder';
		$folder->save();

		$output = \elgg_view_entity($folder);
		$this->assertIsString($output);

		$folder->delete();
		\elgg_get_session()->removeLoggedInUser();
	}

	public function testEditFormViewRenders(): void {
		$user = $this->createUser();
		\elgg_get_session()->setLoggedInUser($user);

		$output = \elgg_view_form('folders/edit', [], [
			'container_guid' => $user->guid,
		]);
		$this->assertIsString($output);
		$this->assertNotEmpty($output);

		\elgg_get_session()->removeLoggedInUser();
	}
}
