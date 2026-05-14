<?php

namespace hypeJunction\Folders;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritDoc}
	 */
	public function init(): void {
		elgg_register_event_handler('seeds', 'database', [Seeder::class, 'addSeed']);

		elgg_register_event_handler('entity:url', 'object', [Router::class, 'entityUrlHandler'], 999);

		// Permissions
		elgg_register_event_handler('container_permissions_check', 'object', [Permissions::class, 'checkContainerPermissions']);
		elgg_register_event_handler('container_permissions_check', 'all', [Permissions::class, 'checkFolderPermissions']);

		// Setup menus
		elgg_register_event_handler('register', 'menu:folders', [Menus::class, 'setupFolderMenu']);
		elgg_register_event_handler('register', 'menu:entity', [Menus::class, 'setupFolderResourceMenu']);
		elgg_register_event_handler('register', 'menu:owner_block', [Menus::class, 'setupOwnerBlockMenu']);

		// Group tools
		if (elgg_get_plugin_setting('group_folders', 'hypefolders', false)) {
			elgg()->group_tools->register('folders', [
				'label' => elgg_echo('folders:group_tool:folders'),
				'default_on' => true,
			]);
			elgg()->group_tools->register('admin_only_folders', [
				'label' => elgg_echo('folders:group_tool:admin_only'),
				'default_on' => false,
			]);
			elgg()->group_tools->register('add_to_folders', [
				'label' => elgg_echo('folders:group_tool:add_to_folders'),
				'default_on' => false,
			]);
		}

		// Add resource to folder
		elgg_register_event_handler('create', 'object', [MainFolder::class, 'addCreatedResource']);

		// Sync folder hierarchy
		elgg_register_event_handler('update', 'object', [MainFolder::class, 'syncTitle']);
		elgg_register_event_handler('delete', 'object', [MainFolder::class, 'removeDeletedItems'], 999);
	}

	/**
	 * {@inheritDoc}
	 */
	public function activate(): void {
		// Run install SQL when plugin is activated
		$sql_file = $this->elgg()->config->path . 'mod/hypefolders/install/mysql.sql';
		if (file_exists($sql_file)) {
			elgg_call(ELGG_IGNORE_ACCESS, function () use ($sql_file) {
				$db = _elgg_services()->db;
				$sql = file_get_contents($sql_file);
				// Replace prefix placeholder
				$prefix = elgg_get_config('dbprefix');
				$sql = str_replace('prefix_', $prefix, $sql);
				foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
					if ($stmt) {
						$db->updateData($stmt);
					}
				}
			});
		}
	}
}
