<?php

/**
 * hypeFolders
 *
 * Allows users to create folders and content trees
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2016, Ismayil Khayredinov
 */

require_once __DIR__ . '/autoloader.php';

use hypeJunction\Folders\MainFolder;
use hypeJunction\Folders\Menus;
use hypeJunction\Folders\Router;
use hypeJunction\Folders\Permissions;

elgg_register_event_handler('init', 'system', function () {

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
});
