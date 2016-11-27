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
				//'callback' => false,
		]);

		$selected = elgg_extract('resource', $params, $folder);

		$ancestors = $folder->getAncestors($selected->guid);
		$ancestors = array_map(function($elem) {
			return $elem->guid;
		}, $ancestors);

		$drag = '';
		$item_class = '';
		if ($folder->canEdit()) {
			$drag = elgg_view_icon('arrows');
			$item_class = 'elgg-state-draggable';
		}
		$menu = self::getProfileMenuItems($folder, $folder);
		foreach ($menu as &$item) {
			$icon = $item->getData('icon');
			if ($icon) {
				$item->setText(elgg_view_icon($icon));
				$item->setData('icon', null);
			}
		}
		$controls = elgg_view_menu('resource:item:controls', [
			'items' => $menu,
			'class' => 'elgg-menu-hz',
		]);

		$link = elgg_view('output/url', [
			'text' => $folder->title,
			'href' => "folders/view/$folder->guid",
		]);

		$return[] = ElggMenuItem::factory([
					'name' => "resource:$folder->guid",
					'text' => $drag . $link . $controls,
					'href' => false,
					'priority' => 1,
					'data-guid' => $folder->guid,
					'item_class' => (in_array($folder->guid, $ancestors)) ? "elgg-state-highlighted $item_class" : $item_class,
					'selected' => $folder->guid == $selected->guid,
					'data' => [
						'guid' => $folder->guid,
						'collapse' => !in_array($folder->guid, $ancestors),
					],
		]);

		foreach ($resources as $resource) {
			$menu = self::getProfileMenuItems($resource, $folder);
			foreach ($menu as &$item) {
				$icon = $item->getData('icon');
				if ($icon) {
					$item->setText(elgg_view_icon($icon));
					$item->setData('icon', null);
				}
			}
			$controls = elgg_view_menu('resource:item:controls', [
				'items' => $menu,
				'class' => 'elgg-menu-hz',
			]);

			$link = elgg_view('output/url', [
				'text' => $resource->title,
				'href' => "folders/view/$folder->guid/$resource->guid"
			]);

			$parent = $folder->getParent($resource->guid);
			$return[] = ElggMenuItem::factory([
						'name' => "resource:$resource->guid",
						'text' => $drag . $link . $controls,
						'href' => false,
						'priority' => $folder->getWeight($resource->guid) ?: 9999,
						'parent_name' => ($parent) ? "resource:$parent->guid" : null,
						'item_class' => (in_array($resource->guid, $ancestors)) ? "elgg-state-highlighted $item_class" : $item_class,
						'selected' => $resource->guid == $selected->guid,
						'data' => [
							'guid' => $resource->guid,
							'parent-guid' => $parent->guid,
							'folder-guid' => $folder->guid,
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

		$folder_guid = $entity->getVolatileData('select:folder_guid');
		if ($folder_guid) {
			$folder = get_entity($folder_guid);
		} else if ($entity instanceof MainFolder) {
			$folder = $entity;
		}

		if (!$folder instanceof MainFolder) {
			return;
		}

		$items = self::getProfileMenuItems($entity, $folder);
		return array_merge($return, $items);
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

	/**
	 * Returns profile menu items
	 * 
	 * @param ElggEntity $resource Entity
	 * @param MainFolder $folder   Main folder
	 * @return ElggMenuItem[]
	 */
	public static function getProfileMenuItems(ElggEntity $resource, MainFolder $folder) {

		$return = [];
		if ($folder->canWriteToContainer()) {
			$return[] = ElggMenuItem::factory([
						'name' => 'resources:add',
						'text' => elgg_echo('folders:resources:add'),
						'title' => elgg_echo('folders:resources:add'),
						'href' => "folders/resources/add/$folder->guid/$resource->guid",
						'link_class' => 'js-folders-resources-add',
						'data' => [
							'icon' => 'plus',
						],
			]);
		}

		if ($resource instanceof MainFolder) {
			if ($resource->canEdit()) {
				$return[] = ElggMenuItem::factory([
							'name' => 'edit',
							'text' => elgg_echo('edit'),
							'title' => elgg_echo('edit'),
							'href' => "folders/edit/$resource->guid",
							'data' => [
								'icon' => 'pencil',
							],
				]);
			}
		}

		if ($resource instanceof Folder && $folder->canWriteToContainer()) {
			if ($resource->canEdit()) {
				$return[] = ElggMenuItem::factory([
							'name' => 'edit',
							'text' => elgg_echo('edit'),
							'title' => elgg_echo('edit'),
							'href' => "folders/resources/edit/$folder->guid/$resource->guid",
							'data' => [
								'icon' => 'pencil',
							],
				]);
			}
		}

		if ($resource instanceof Folder || $resource instanceof MainFolder) {
			if ($resource->canDelete()) {
				$return[] = ElggMenuItem::factory([
							'name' => 'delete',
							'text' => elgg_echo('delete'),
							'title' => elgg_echo('delete'),
							'href' => elgg_http_add_url_query_elements("action/entity/delete", [
								'guid' => $resource->guid,
							]),
							'confirm' => true,
							'is_action' => true,
							'data' => [
								'icon' => 'delete',
							],
				]);
			}
		} else if ($folder->canWriteToContainer()) {
			$return[] = ElggMenuItem::factory([
						'name' => 'remove',
						'text' => elgg_echo('folders:resources:remove'),
						'title' => elgg_echo('folders:resources:remove'),
						'href' => elgg_http_add_url_query_elements("action/folders/resources/remove", [
							'guid' => $resource->guid,
						]),
						'item_class' => 'elgg-menu-item-delete',
						'confirm' => true,
						'is_action' => true,
						'data' => [
							'icon' => 'chain-broken',
						],
			]);
		}

		return $return;
	}

}
