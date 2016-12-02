<?php

namespace hypeJunction\Folders;

class Router {

	/**
	 * Handles folder pages
	 *
	 * /folders/all
	 * /folders/owner/<username>
	 * /folders/friends/<username>
	 * /folders/view/<folder_guid>[/<resource_guid>]
	 * /folders/add[/<container_guid>]
	 * /folders/edit/<entity_guid>
	 * /folders/resources/add/<folder_guid>[/<resource_guid>]
	 *
	 * @param array  $segments   URL segments
	 * @return bool
	 */
	public static function handleFolders($segments) {

		$page = array_shift($segments);

		switch ($page) {

			case 'view' :
				echo elgg_view_resource('folders/view', [
					'guid' => array_shift($segments),
					'resource_guid' => array_shift($segments),
				]);
				return true;

			case 'all' :
				echo elgg_view_resource('folders/all');
				return true;

			case 'owner' :
				echo elgg_view_resource('folders/owner', [
					'username' => array_shift($segments),
				]);
				return true;

			case 'friends' :
				echo elgg_view_resource('folders/friends', [
					'username' => array_shift($segments),
				]);
				return true;

			case 'group' :
				echo elgg_view_resource('folders/group', [
					'container_guid' => array_shift($segments),
				]);
				return true;

			case 'add' :
				echo elgg_view_resource('folders/add', [
					'container_guid' => array_shift($segments),
				]);
				return true;

			case 'edit' :
				echo elgg_view_resource('folders/edit', [
					'guid' => array_shift($segments),
				]);
				return true;

			case 'resources' :
				$subpage = array_shift($segments);
				switch ($subpage) {
					case 'edit' :
						echo elgg_view_resource('folders/resources/edit', [
							'guid' => array_shift($segments),
							'resource_guid' => array_shift($segments),
						]);
						return true;
					case 'add' :
						echo elgg_view_resource('folders/resources/add', [
							'guid' => array_shift($segments),
							'resource_guid' => array_shift($segments),
						]);
						return true;

					case 'new' :
						echo elgg_view_resource('folders/resources/new', [
							'guid' => array_shift($segments),
							'resource_guid' => array_shift($segments),
							'subtype' => array_shift($segments),
						]);
						return true;

					case 'move' :
						echo elgg_view_resource('folders/resources/move', [
							'guid' => array_shift($segments),
							'resource_guid' => array_shift($segments),
						]);
						return true;
				}
				return false;

			case 'search' :
				echo elgg_view_resource('folders/search');
				return true;
		}

		return false;
	}

	/**
	 * Returns a URL of the folder
	 *
	 * @param string $hook   "entity:url"
	 * @param string $type   "object"
	 * @param string $return URL
	 * @param array  $params Hook params
	 * @return string
	 */
	public static function entityUrlHandler($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		$subtype = $entity->getSubtype();
		switch ($subtype) {
			case MainFolder::SUBTYPE :
				/* @var $entity MainFolder */
				return elgg_normalize_url("folders/view/$entity->guid");

			case Folder::SUBTYPE :
				/* @var $entity Folder */
				$folder = $entity->getMainFolder();
				return elgg_normalize_url("folders/view/$folder->guid/$entity->guid");

			default :
				if (!elgg_in_context('folders')) {
					return;
				}
				$folder_guid = $entity->getVolatileData('select:folder_guid');
				$folder = get_entity($folder_guid);
				if ($folder) {
					return elgg_normalize_url("folders/view/$folder->guid/$entity->guid");
				}
				break;
		}
	}
}
