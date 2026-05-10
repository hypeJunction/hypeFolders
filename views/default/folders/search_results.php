<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\FoldersService;
use hypeJunction\Folders\MainFolder;

$folder = elgg_extract('folder', $vars);
if (!$folder instanceof MainFolder) {
	return;
}

$query = elgg_extract('query', $vars, get_input('query', ''));
$limit = get_input('limit', 25);
$offset = get_input('search_results_offset', 0);

$svc = new FoldersService();
$subtypes = $svc->getContentTypes();
$exceptions = [
	MainFolder::SUBTYPE,
	Folder::SUBTYPE,
];

$subtypes = array_diff($subtypes, $exceptions);
$dbprefix = elgg_get_config('dbprefix');
$options = [
	'types' => 'object',
	'subtypes' => $subtypes,
	'limit' => $limit,
	'offset' => $offset,
	'joins' => [
		"
			JOIN {$dbprefix}objects_entity oe_sort
				ON oe_sort.guid = e.guid
		",
	],
	'wheres' => [
		// only show items that are not part of the folder yet
		"
		NOT EXISTS(
			SELECT 1 FROM {$dbprefix}entity_relationships
				WHERE guid_one=e.guid
				AND relationship = 'resource'
				AND guid_two = $folder->guid
		)
		",
		"e.guid != $folder->guid",
	],
	'order_by' => 'oe_sort.title ASC',
	'query' => $query,
];

$container = $folder->getContainerEntity();

$owner_guids = ELGG_ENTITIES_ANY_VALUE;
$container_guids = ELGG_ENTITIES_ANY_VALUE;

// We want to make sure the folder items are accessible
// by users who are allowed to see the folder
$access_ids = array_filter([
	ACCESS_PUBLIC,
	ACCESS_LOGGED_IN,
	$folder->access_id,
	$container->group_acl,
]);

if ($container instanceof ElggUser) {
	$restrict = elgg_get_plugin_setting('user_folders_restrict_by_owner', 'hypeFolders', true);
	if ($restrict && !elgg_is_admin_logged_in()) {
		$owner_guids = $container->guid;
	}
} else {
	$restrict = elgg_get_plugin_setting('group_folders_restrict_by_container', 'hypeFolders', true);
	if ($restrict) {
		$container_guids = $container->guid;
	}
}

$options['owner_guids'] = $owner_guids;
$options['container_guids'] = $container_guids;

if (!empty($access_ids)) {
	$access_ids_in = implode(',', $access_ids);
	$options['wheres'][] = "e.access_id IN ($access_ids_in)";
}

if ($query) {
	$results = (array) elgg_trigger_plugin_hook('search', 'object', $options, []);
} else {
	$options['count'] = true;
	$count = elgg_get_entities($options);
	unset($options['count']);
	$results = [
		'count' => $count,
		'entities' => elgg_get_entities($options),
	];
}

$entities = elgg_extract('entities', $results);
echo elgg_view_entity_list($entities, [
	'list_id' => "folders-search_results-$folder->guid",
	'no_results' => elgg_echo('folders:search:no_results'),
	'list_class' => 'folders-content-list',
	'pagination' => elgg_is_active_plugin('hypeLists'),
	'pagination_type' => 'infinite',
	'count' => elgg_extract('count', $results),
	'limit' => $limit,
	'offset' => $offset,
	'base_url' => elgg_normalize_url("folders/search/search_results?main_folder_guid={$folder->guid}"),
	'offset_key' => 'search_results_offset',
	'item_view' => 'folders/resource',
	'folder' => $folder,
	'full_view' => false,
	'query' => $query,
	'input_name' => elgg_extract('input_name', $vars, false),
]);
