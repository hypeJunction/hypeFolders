<?php

namespace hypeJunction\Folders;

use Elgg\HooksRegistrationService\Hook;
use Elgg\IntegrationTestCase;

/**
 * Tests for the container_permissions_check hook handlers in Permissions.
 */
class PermissionsTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testCheckContainerPermissionsBailsForUnrelatedSubtype(): void {
		$user = $this->createUser();
		$params = [
			'container' => $user,
			'subtype' => 'blog',
			'user' => $user,
		];
		$hook = new Hook(elgg(), 'container_permissions_check', 'object', true, $params);
		$result = Permissions::checkContainerPermissions($hook);
		$this->assertNull($result);
	}

	public function testCheckContainerPermissionsRejectsUserFolderWhenSettingDisabled(): void {
		\elgg_get_plugin_from_id('hypefolders')->setSetting('user_folders', false);
		$user = $this->createUser();
		$params = [
			'container' => $user,
			'subtype' => MainFolder::SUBTYPE,
			'user' => $user,
		];
		$hook = new Hook(elgg(), 'container_permissions_check', 'object', true, $params);
		$result = Permissions::checkContainerPermissions($hook);
		$this->assertFalse($result);
	}

	public function testCheckContainerPermissionsAllowsUserFolderWhenSettingEnabled(): void {
		\elgg_get_plugin_from_id('hypefolders')->setSetting('user_folders', 1);
		$user = $this->createUser();
		$params = [
			'container' => $user,
			'subtype' => MainFolder::SUBTYPE,
			'user' => $user,
		];
		$hook = new Hook(elgg(), 'container_permissions_check', 'object', true, $params);
		$result = Permissions::checkContainerPermissions($hook);
		// Handler falls through when enabled, returning null (= keep existing permission)
		$this->assertNull($result);
	}

	public function testCheckFolderPermissionsIgnoresNonMainFolderContainer(): void {
		$user = $this->createUser();
		$params = [
			'container' => $user,
			'subtype' => 'file',
			'user' => $user,
		];
		$hook = new Hook(elgg(), 'container_permissions_check', 'object', true, $params);
		$result = Permissions::checkFolderPermissions($hook);
		$this->assertNull($result);
	}
}
