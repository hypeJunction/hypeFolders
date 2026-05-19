<?php

run_function_once('hypefolders_upgrade_20160510a');
function hypefolders_upgrade_20160510a()
{
    $dbprefix = elgg_get_config('dbprefix');
    // Setup MySQL databases
    run_sql_script(dirname(dirname(__FILE__)) . '/install/mysql.sql');
    $folders = new \ElggBatch('elgg_get_entities', ['types' => 'object', 'subtypes' => \hypeJunction\Folders\MainFolder::SUBTYPE, 'limit' => 0]);
    foreach ($folders as $folder) {
        $resources = new ElggBatch('elgg_get_entities', ['relationship' => 'resource', 'relationship_guid' => $folder->guid, 'inverse_relationship' => true, 'limit' => 0]);
        foreach ($resources as $resource) {
            $relationship = (get_entity($resource->guid)?->getRelationship($folder->guid, 'resource') ?? null);
            $annotations = elgg_get_annotations(array('guids' => $resource->guid, 'annotation_names' => array('parent', 'weight'), 'annotation_owner_guids' => $folder->guid, 'limit' => 0));
            $parent_guid = $folder->guid;
            $weight = 0;
            foreach ($annotations as $annotation) {
                switch ($annotation->name) {
                    case 'parent':
                        $parent_guid = $annotation->value;
                        break;
                    case 'weight':
                        $weight = $annotation->value;
                        break;
                }
            }
            $query = "\n\t\t\t\tINSERT INTO {$dbprefix}folders\n\t\t\t\tSET relationship_id = :relationship_id,\n\t\t\t\t    folder_guid = :folder_guid,\n\t\t\t\t\tparent_guid = :parent_guid,\n\t\t\t\t    resource_guid = :resource_guid,\n\t\t\t\t\tweight = :weight,\n\t\t\t\t\ttitle = :title\n\t\t\t\tON DUPLICATE KEY UPDATE\n\t\t\t\t\tparent_guid = :parent_guid\n\t\t\t";
            $params = [':relationship_id' => (int) $relationship->id, ':folder_guid' => (int) $folder->guid, ':parent_guid' => (int) $parent_guid, ':resource_guid' => (int) $resource->guid, ':weight' => (int) $weight, ':title' => (string) $resource->getDisplayName()];
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