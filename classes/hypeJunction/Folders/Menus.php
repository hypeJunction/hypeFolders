<?php

namespace hypeJunction\Folders;

use ElggEntity;
use ElggGroup;
use ElggMenuItem;
use ElggUser;

class Menus {

	/**
	 * Setup folder tree
	 *
	 * @param string $hook   "register"
	 * @param string $type   "menu:folder"
	 * @param array  $return Menu
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function setupFolderMenu($hook, $type, $return, $params) {

		$folder = elgg_extract('folder', $params);

		if (!$folder instanceof MainFolder) {
			return;
		}

		$resources = $folder->getResources([
			'callback' => false,
		]);

		$selected = elgg_extract('resource', $params, $folder);

		$ancestors = $folder->getAncestors($selected->guid);
		array_walk($ancestors, function($elem) {
			return $elem->guid;
		});

		$return[] = ElggMenuItem::factory([
				'name' => "resource:$folder->guid",
				'text' => $folder->title,
				'href' => "folders/view/$folder->guid",
				'priority' => 1,
				'data-guid' => $folder->guid,
				'item_class' => 'elgg-state-highlighted',
				'selected' => $folder->guid == $selected->guid,
				'data' => [
					'guid' => $folder->guid,
					'collapse' => true,
				],
		]);
		
		foreach ($resources as $resource) {
			$parent = $folder->getParent($resource->guid);
			$return[] = ElggMenuItem::factory([
				'name' => "resource:$resource->guid",
				'text' => $resource->title,
				'href' => "folders/view/$folder->guid/$resource->guid",
				'priority' => $folder->getWeight($resource->guid) ? : 9999,
				'parent_name' => ($parent) ? "resource:$parent->guid" : null,
				'data-guid' => $resource->guid,
				'item_class' => (in_array($resource->guid, $ancestors)) ? 'elgg-state-highlighted' : '',
				'selected' => $resource == $selected,
				'data' => [
					'guid' => $resource->guid,
					'collapse' => !in_array($resource->guid, $ancestors),
				],
			]);
		}

		return $return;
	}

	/**
	 * Adds a node to the folder tree
	 *
	 * @param ElggEntity                        $resource   Resource
	 * @param \hypeJunctions\Folders\MainFolder  $folder     Folder
	 * @param array                              $params     Additional params
	 * @return array
	 */
	public static function setupFolderTreeNode($resource, $folder, $params = array()) {

		if (!$folder instanceof MainFolder) {
			return [];
		}

		$ancestry = elgg_extract('ancestry', $params, []);
		$selected = elgg_extract('resource', $params);

		$menu = [];



		$children = $folder->getChildren($resource->guid);
		foreach ($children as $child) {
			$submenu = self::setupFolderTreeNode($child, $folder, $params);
			if (is_array($submenu)) {
				$menu = array_merge($menu, $submenu);
			}
		}

		return $menu;
	}

	/**
	 * Setup folder resource menu
	 *
	 * @param string $hook   "register"
	 * @param string $type   "menu:resource"
	 * @param array  $return Menu
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function setupFolderResourceMenu($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if ($entity instanceof MainFolder) {
			if ($entity->canEdit()) {
				$return[] = ElggMenuItem::factory([
					'name' => 'edit',
					'text' => elgg_echo('edit'),
					'href' => "folders/edit/$entity->guid",
				]);
			}

			if ($entity->canDelete()) {
				$return[] = ElggMenuItem::factory([
					'name' => 'edit',
					'text' => elgg_echo('edit'),
					'href' => elgg_http_add_url_query_elements("action/entity/delete", [
						'guid' => $entity->guid,
					]),
				]);
			}

			return $return;
		}

		$folder_guid = $entity->getVolatileData('select:folder_guid');
		$folder = get_entity($folder_guid);

		if (!$folder instanceof MainFolder) {
			return;
		}

		if ($entity instanceof Folder && $folder->canWriteToContainer()) {
			if ($entity->canEdit()) {
				$return[] = ElggMenuItem::factory([
					'name' => 'edit',
					'text' => elgg_echo('edit'),
					'href' => "folders/edit/$folder->guid/$entity->guid",
				]);
			}

			if ($entity->canDelete()) {
				$return[] = ElggMenuItem::factory([
					'name' => 'delete',
					'text' => elgg_echo('delete'),
					'href' => elgg_http_add_url_query_elements("action/entity/delete", [
						'guid' => $entity->guid,
					]),
				]);
			}

			return $return;
		}

		if ($folder->canWriteToContainer()) {
			$return[] = ElggMenuItem::factory([
				'name' => 'remove',
				'text' => elgg_echo('folders:resources:remove'),
				'href' => elgg_http_add_url_query_elements("action/folders/resources/remove", [
					'guid' => $entity->guid,
				]),
				'item_class' => 'elgg-menu-item-delete',
			]);

			return $return;
		}
	}

	/**
	 * Setup owner block
	 *
	 * @param string $hook   "register"
	 * @param string $type   "menu:folder"
	 * @param array  $return Menu
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function setupOwnerBlockMenu($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if ($entity instanceof ElggGroup) {
			if (elgg_get_plugin_setting('group_folders', 'hypeFolders', false) && $entity->folders_enable !== 'no') {
				$return[] = ElggMenuItem::factory([
					'name' => 'folders',
					'href' => "folders/group/$entity->guid",
					'text' => elgg_echo('folders:group'),
				]);
			}
		} else if ($entity instanceof ElggUser) {
			if (elgg_get_plugin_setting('user_folders', 'hypeFolders', false)) {
				$return[] = ElggMenuItem::factory([
					'name' => 'folders',
					'href' => "folders/owner/$entity->username",
					'text' => elgg_echo('folders'),
				]);
			}
		}

		return $return;
	}

}
