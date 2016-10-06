<?php

namespace hypeJunction\Folders;

use ElggGroup;

class Permissions {

	/**
	 * Check container permissions for creating new folders/subfolders
	 * 
	 * @param string $hook   "container_permissions_check"
	 * @param string $type   "object"
	 * @param bool   $return Permission 
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function checkContainerPermissions($hook, $type, $return, $params) {

		$container = elgg_extract('container', $params);
		$subtype = elgg_extract('subtype', $params);
		$user = elgg_extract('user', $params);
		
		if (!in_array($subtype, [MainFolder::SUBTYPE])) {
			return;
		}

		if ($container instanceof ElggGroup) {
			if (!elgg_get_plugin_setting('group_folders', 'hypeFolders', false)) {
				return false;
			}
			if ($container->folders_enable == 'no') {
				return false;
			}
			if ($container->admin_only_folders !== 'no') {
				return $container->canEdit($user->guid);
			}
		} else {
			if (!elgg_get_plugin_setting('user_folders', 'hypeFolders', false)) {
				return false;
			}
		}
	}

	/**
	 * Check folders permissions for adding new content
	 *
	 * @param string $hook   "container_permissions_check"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function checkFolderPermissions($hook, $type, $return, $params) {

		$folder = elgg_extract('container', $params);
		$subtype = elgg_extract('subtype', $params);
		$user = elgg_extract('user', $params);
		
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
