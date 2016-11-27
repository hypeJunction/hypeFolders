<?php

namespace hypeJunction\Folders;

use ElggEntity;
use ElggGroup;
use ElggObject;
use ElggUser;

class MainFolder extends ElggObject {

	const CLASSNAME = __CLASS__;
	const SUBTYPE = 'main_resource_folder';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

	/**
	 * Checks if a resource has been added to the folder
	 * 
	 * @param int $resource_guid GUID of the resource
	 * @return int|false
	 */
	public function isResource($resource_guid = 0) {
		$relationship = check_entity_relationship($resource_guid, 'resource', $this->guid);
		return $relationship ? $relationship->id : false;
	}

	/**
	 * Adds a resource to a folder
	 * Creates an annotation that specifies the weight of a resource within a parent node
	 *
	 * @param int $resource_guid GUID of the resource
	 * @param int $parent_guid   GUID of the parent (defaults to root)
	 * @param int $weight        Weight
	 * @return int|false
	 */
	public function addResource($resource_guid, $parent_guid = 0, $weight = 0) {
		$resource = get_entity($resource_guid);
		if (!$resource) {
			return false;
		}

		$id = $this->isResource($resource_guid);
		if ($id) {
			$weight = $weight ?: $this->getWeight($resource_guid);
		} else {
			add_entity_relationship($resource_guid, 'resource', $this->guid);
			$id = $this->isResource($resource_guid);
		}

		if (!$id) {
			return false;
		}

		if (!$parent_guid) {
			$parent_guid = $this->guid;
		} else if (!$this->isResource($parent_guid)) {
			$this->addResource($parent_guid);
		}

		$parent = get_entity($parent_guid);
		if (!$parent) {
			return false;
		}

		$dbprefix = elgg_get_config('dbprefix');

		$query = "
			INSERT INTO {$dbprefix}folders
			SET relationship_id = :relationship_id,
				folder_guid = :folder_guid,
				parent_guid = :parent_guid,
				resource_guid = :resource_guid,
				weight = :weight,
				title = :title
			ON DUPLICATE KEY UPDATE
				parent_guid = :parent_guid,
				weight = :weight,
				title = :title
		";

		$params = [
			':relationship_id' => (int) $id,
			':folder_guid' => (int) $this->guid,
			':parent_guid' => (int) $parent->guid,
			':resource_guid' => (int) $resource->guid,
			':weight' => (int) $weight,
			':title' => (string) $resource->getDisplayName(),
		];

		return insert_data($query, $params);
	}

	/**
	 * Removes a resource from folder
	 *
	 * @param int $resource_guid GUID of the resource
	 * @return boolean
	 */
	public function removeResource($resource_guid) {
		if (!$resource_guid) {
			return false;
		}

		$relationship = check_entity_relationship($resource_guid, 'resource', $this->guid);
		if (!$relationship) {
			return false;
		}

		$id = $relationship->id;
		$result = remove_entity_relationship($resource_guid, 'resource', $this->guid);

		if ($result) {
			$dbprefix = elgg_get_config('dbprefix');
			$query = "
				DELETE FROM {$dbprefix}folders
				WHERE relationship_id = :relationship_id
			";

			delete_data($query, [
				':relationship_id' => $id,
			]);
		}

		return $result;
	}

	/**
	 * Returns all resources in a folder
	 * 
	 * @param array $options Getter options
	 * @return \stdClass[]|false
	 */
	public function getResources($options = array()) {

		$defaults = array(
			'limit' => 0,
		);

		$options = array_merge($defaults, $options);

		$dbprefix = elgg_get_config('dbprefix');

		$options['joins'][] = "
			JOIN {$dbprefix}folders frs ON e.guid = frs.resource_guid
		";

		$options['selects'][] = 'frs.*';
		$options['order_by'] = 'frs.weight = 0, frs.weight ASC';
		$options['wheres'][] = "
			frs.folder_guid = $this->guid
			AND frs.resource_guid != $this->guid
		";

		$rows = elgg_get_entities($options);
		
		if (is_array($rows)) {
			$keys = array_map(function($elem) {
				return (int) $elem->guid;
			}, $rows);
			$rows = array_combine($keys, $rows);
		}

		return $rows;

	}

	/**
	 * Returns children of a parent within a folder
	 * Defaults to root
	 * 
	 * @param int   $parent_guid GUID of the parent entity
	 * @param array $options     Getter options
	 * @return ElggEntity[]
	 */
	public function getChildren($parent_guid = 0, $options = array()) {

		if (!$parent_guid) {
			$parent_guid = $this->guid;
		}

		$parent_guid = (int) $parent_guid;
		$options['wheres'][] = "frs.parent_guid = $parent_guid";

		return $this->getResources($options);
	}

	/**
	 * Returns a parent of a given resource within folder
	 *
	 * @param int $resource_guid GUID of the resource
	 * @return \stdClass
	 */
	public function getParent($resource_guid = 0) {

		$resources = $this->getResources([
			'callback' => false,
		]);
		
		if (!$resources) {
			return false;
		}

		$resource_guid = (int) $resource_guid;
		$resource = elgg_extract($resource_guid, $resources);
		if (!$resource) {
			return false;
		}

		$parent_guid = (int) $resource->parent_guid;
		if ($parent_guid == $this->guid) {
			return $this;
		}
		
		$parent = elgg_extract($parent_guid, $resources, false);
		
		return $parent;
	}

	/**
	 * Returns a weight of a given resource within folder
	 *
	 * @param int $resource_guid GUID of the resource
	 * @return int
	 */
	public function getWeight($resource_guid = 0) {
		$resources = $this->getResources([
			'callback' => false,
		]);
		
		if (!$resources) {
			return 0;
		}

		foreach ($resources as $resource) {
			if ($resource->guid == $resource_guid) {
				return (int) $resource->weight;
			}
		}

		return count($resources) + 1;
	}

	/**
	 * Returns ancestors of the resource in the folder
	 *
	 * @param int $resource_guid GUID of the resource
	 * @return \stdClass[]
	 */
	public function getAncestors($resource_guid = 0) {

		$resource = get_entity($resource_guid);
		if (!$resource) {
			$resource = $this;
		}

		$ancestors = array($resource);

		if ($resource->guid) {
			$parent = $this->getParent($resource->guid);
			while ($parent && $parent->guid != $resource->guid) {
				$ancestors[] = $parent;
				$new_parent = $this->getParent($parent->guid);
				if ($new_parent->guid != $parent->guid) {
					$parent = $new_parent;
				} else {
					$parent = false;
				}
			}
		}

		return array_reverse($ancestors);
	}

	/**
	 * Sets breadcrumbs to a resource within a folder
	 *
	 * @param int $resource_guid GUID of the resource
	 * @return void
	 */
	public function setBreadcrumbs($resource_guid = 0) {
		$container = $this->getContainerEntity();
		//elgg_set_page_owner_guid($container->guid);
		if ($container instanceof ElggUser) {
			elgg_push_breadcrumb($container->getDisplayName(), $container->getURL());
			elgg_push_breadcrumb(elgg_echo('folders'), "folders/owner/$container->username");
		} else if ($container instanceof ElggGroup) {
			elgg_push_breadcrumb($container->getDisplayName(), $container->getURL());
			elgg_push_breadcrumb(elgg_echo('folders'), "folders/group/$container->guid");
		}

		$ancestors = $this->getAncestors($resource_guid);
		foreach ($ancestors as $ancestor) {
			elgg_push_breadcrumb($ancestor->title, "folders/view/$this->guid/$ancestor->guid");
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		$return = parent::save();
		if ($return && !isset($this->priority)) {
			$this->priority = 0;
		}
		return $return;
	}

	/**
	 * Add new resource when entity is created with a special form
	 *
	 * @param string     $event  "create"
	 * @param string     $type   "object"
	 * @param ElggEntity $entity New entity
	 * @return void
	 */
	public static function addCreatedResource($event, $type, $entity) {

		$folder_guid = get_input('main_folder_guid');
		$folder = get_entity($folder_guid);

		$parent_guid = (int) get_input('parent_guid');

		if (!$folder instanceof MainFolder) {
			return;
		}

		$svc = new FoldersService();

		if (!in_array($entity->getSubtype(), $svc->getContentTypes())) {
			return;
		}

		$folder->addResource($entity->guid, $parent_guid);
	}

	/**
	 * Sync item title in the folders table
	 * 
	 * @param string     $event  "update"
	 * @param string     $type   "object"
	 * @param ElggEntity $entity Entity
	 * @return void
	 */
	public static function syncTitle($event, $type, $entity) {

		$original_attributes = $entity->getOriginalAttributes();
		if (!array_key_exists('title', $original_attributes)) {
			return;
		}

		$dbprefix = elgg_get_config('dbprefix');
		$query = "
			UPDATE {$dbprefix}folders
			SET title = :title
			WHERE resource_guid = :resource_guid
		";

		$params = [
			':title' => (string) $entity->getDisplayName(),
			':resource_guid' => $entity->guid,
		];

		update_data($query, $params);
	}

	/**
	 * Remove deleted items from the tree
	 *
	 * @param string     $event  "delete"
	 * @param string     $type   "object"
	 * @param ElggEntity $entity Entity
	 * @return void
	 */
	public static function removeDeletedItems($event, $type, $entity) {

		$dbprefix = elgg_get_config('dbprefix');
		$query = "
			DELETE FROM {$dbprefix}folders
			WHERE folder_guid = :guid
			OR parent_guid = :guid
			OR resource_guid = :guid
		";

		$params = [
			':guid' => $entity->guid,
		];

		delete_data($query, $params);
	}
}
