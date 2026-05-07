<?php

namespace hypeJunction\Folders;

use ElggGroup;

/**
 * Container/folder permission handlers for hypeFolders.
 */
class Permissions {

	/**
	 * Check container permissions for creating new folders/subfolders.
	 *
	 * @param \Elgg\Event $hook Event
	 * @return bool|null
	 */
	public static function checkContainerPermissions(\Elgg\Event $hook) {

		$container = $hook->getParam('container');
		$subtype = $hook->getParam('subtype');
		$user = $hook->getParam('user');
		
		if (!in_array($subtype, [MainFolder::SUBTYPE])) {
			return;
		}

		if ($container instanceof ElggGroup) {
			if (!elgg_get_plugin_setting('group_folders', 'hypefolders', false)) {
				return false;
			}

			if ($container->folders_enable == 'no') {
				return false;
			}

			if ($container->admin_only_folders !== 'no') {
				return $container->canEdit($user->guid);
			}
		} else {
			if (!elgg_get_plugin_setting('user_folders', 'hypefolders', false)) {
				return false;
			}
		}
	}

	/**
	 * Check folders permissions for adding new content.
	 *
	 * @param \Elgg\Event $hook Event
	 * @return bool|null
	 */
	public static function checkFolderPermissions(\Elgg\Event $hook) {

		$folder = $hook->getParam('container');
		$type = $hook->getType();
		$subtype = $hook->getParam('subtype');
		$user = $hook->getParam('user');

		if (!$folder instanceof MainFolder) {
			return;
		}

		$container = $folder->getContainerEntity();
		if (!$container || !$container->canWriteToContainer($user->guid, $type, $subtype)) {
			return false;
		}

		if ($container instanceof ElggGroup && $container->add_to_folders_enable == 'yes') {
			return true;
		}
	}
}
