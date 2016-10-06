<?php

run_function_once('hypefolders_upgrade_20160510a');

function hypefolders_upgrade_20160510a() {

	$dbprefix = elgg_get_config('dbprefix');

	// Setup MySQL databases
	run_sql_script(dirname(dirname(__FILE__)) . '/install/mysql.sql');

	$folders = new \ElggBatch('elgg_get_entities', [
		'types' => 'object',
		'subtypes' => \hypeJunction\Folders\MainFolder::SUBTYPE,
		'limit' => 0,
	]);

	foreach ($folders as $folder) {

		$resources = new ElggBatch('elgg_get_entities_from_relationship', [
			'relationship' => 'resource',
			'relationship_guid' => $folder->guid,
			'inverse_relationship' => true,
			'limit' => 0,
		]);

		foreach ($resources as $resource) {

			$relationship = check_entity_relationship($resource->guid, 'resource', $folder->guid);

			$annotations = elgg_get_annotations(array(
				'guids' => $resource->guid,
				'annotation_names' => array('parent', 'weight'),
				'annotation_owner_guids' => $folder->guid,
				'limit' => 0,
			));

			$parent_guid = $folder->guid;
			$weight = 0;
			foreach ($annotations as $annotation) {
				switch ($annotation->name) {
					case 'parent' :
						$parent_guid = $annotation->value;
						break;

					case 'weight' :
						$weight = $annotation->value;
						break;
				}
			}

			$query = "
				INSERT INTO {$dbprefix}folders
				SET relationship_id = :relationship_id,
				    folder_guid = :folder_guid,
					parent_guid = :parent_guid,
				    resource_guid = :resource_guid,
					weight = :weight,
					title = :title
				ON DUPLICATE KEY UPDATE
					parent_guid = :parent_guid
			";

			$params = [
				':relationship_id' => (int) $relationship->id,
				':folder_guid' => (int) $folder->guid,
				':parent_guid' => (int) $parent_guid,
				':resource_guid' => (int) $resource->guid,
				':weight' => (int) $weight,
				':title' => (string) $resource->getDisplayName(),
			];
			
			$result = insert_data($query, $params);

			if ($result) {
//				elgg_delete_annotations(array(
//					'guids' => $resource->guid,
//					'annotation_names' => array('parent', 'weight'),
//					'annotation_owner_guids' => $folder->guid,
//					'limit' => 0,
//				));
			}
		}
	}
}
