<?php

namespace hypeJunction\Folders;

use Elgg\Hook;
use Elgg\IntegrationTestCase;

/**
 * Tests for the container_permissions_check hook handlers in Permissions.
 * These use the legacy 4-arg static signature; tests still invoke them directly
 * so we can mock \Elgg\Hook for the signature expected by Elgg 4.x hook plumbing.
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
		$result = Permissions::checkContainerPermissions('container_permissions_check', 'object', true, $params);
		$this->assertNull($result);
	}

	public function testCheckContainerPermissionsRejectsUserFolderWhenSettingDisabled(): void {
		\elgg_get_plugin_from_id('hypeFolders')->setSetting('user_folders', false);
		$user = $this->createUser();
		$params = [
			'container' => $user,
			'subtype' => MainFolder::SUBTYPE,
			'user' => $user,
		];
		$result = Permissions::checkContainerPermissions('container_permissions_check', 'object', true, $params);
		$this->assertFalse($result);
	}

	public function testCheckContainerPermissionsAllowsUserFolderWhenSettingEnabled(): void {
		\elgg_get_plugin_from_id('hypeFolders')->setSetting('user_folders', 1);
		$user = $this->createUser();
		$params = [
			'container' => $user,
			'subtype' => MainFolder::SUBTYPE,
			'user' => $user,
		];
		$result = Permissions::checkContainerPermissions('container_permissions_check', 'object', true, $params);
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
		$result = Permissions::checkFolderPermissions('container_permissions_check', 'object', true, $params);
		$this->assertNull($result);
	}
}
