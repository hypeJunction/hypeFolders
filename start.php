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

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;
use hypeJunction\Folders\Menus;
use hypeJunction\Folders\Router;
use hypeJunction\Folders\Permissions;

elgg_register_event_handler('init', 'system', function() {

	// Handle page and URLs
	elgg_register_page_handler('folders', [Router::class, 'handleFolders']);
	elgg_register_plugin_hook_handler('entity:url', 'object', [Router::class, 'entityUrlHandler'], 999);

	// Permissions
	elgg_register_plugin_hook_handler('container_permissions_check', 'object', [Permissions::class, 'checkContainerPermissions']);
	elgg_register_plugin_hook_handler('container_permissions_check', 'all', [Permissions::class, 'checkFolderPermissions']);

	// Register actions
	elgg_register_action('folders/edit', __DIR__ . '/actions/folders/edit.php');
	elgg_register_action('folders/reorder', __DIR__ . '/actions/folders/reorder.php');
	elgg_register_action('folders/folder/edit', __DIR__ . '/actions/folders/folder/edit.php');
	elgg_register_action('folders/resources/add', __DIR__ . '/actions/folders/resources/add.php');
	elgg_register_action('folders/resources/move', __DIR__ . '/actions/folders/resources/move.php');
	elgg_register_action('folders/resources/remove', __DIR__ . '/actions/folders/resources/remove.php');
	
	// Setup menus
	elgg_register_plugin_hook_handler('register', 'menu:folders', [Menus::class, 'setupFolderMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupFolderResourceMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', [Menus::class, 'setupOwnerBlockMenu']);

	// Make it pretty
	elgg_extend_view('elgg.css', 'folders/stylesheet.css');

	// Register for search
	elgg_register_entity_type('object', MainFolder::SUBTYPE);
	elgg_register_entity_type('object', Folder::SUBTYPE);

	// Group tools
	if (elgg_get_plugin_setting('group_folders', 'hypeFolders', false)) {
		add_group_tool_option('folders', elgg_echo('folders:group_tool:folders'), true);
		add_group_tool_option('admin_only_folders', elgg_echo('folders:group_tool:admin_only'), false);
		add_group_tool_option('add_to_folders', elgg_echo('folders:group_tool:add_to_folders'), false);
	}

	// Add resource to folder
	elgg_extend_view('forms/file/upload', 'folders/resources/new');
	elgg_extend_view('forms/pages/edit', 'folders/resources/new');
	elgg_extend_view('forms/videolist/edit', 'folders/resources/new');
	elgg_register_event_handler('create', 'object', [MainFolder::class, 'addCreatedResource']);

	// Sync folder hierarchy
	elgg_register_event_handler('update', 'object', [MainFolder::class, 'syncTitle']);
	elgg_register_event_handler('delete', 'object', [MainFolder::class, 'removeDeletedItems'], 999);
	
});

elgg_register_event_handler('upgrade', 'system', function() {

	if (!elgg_is_admin_logged_in()) {
		return;
	}

	require __DIR__ . '/lib/upgrades.php';
});