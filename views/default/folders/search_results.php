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
$folder_guid = (int) $folder->guid;
$options = [
	'types' => 'object',
	'subtypes' => $subtypes,
	'limit' => $limit,
	'offset' => $offset,
	'wheres' => [
		// only show items that are not part of the folder yet
		function (\Elgg\Database\QueryBuilder $qb, $main_alias) use ($folder_guid) {
			$dbprefix = elgg_get_config('dbprefix');
			return "NOT EXISTS(
				SELECT 1 FROM {$dbprefix}entity_relationships
					WHERE guid_one = {$main_alias}.guid
					AND relationship = 'resource'
					AND guid_two = {$folder_guid}
			)";
		},
		function (\Elgg\Database\QueryBuilder $qb, $main_alias) use ($folder_guid) {
			return $qb->compare("{$main_alias}.guid", '!=', $folder_guid, ELGG_VALUE_INTEGER);
		},
	],
	'sort_by' => [
		'property' => 'name',
		'direction' => 'ASC',
	],
	'query' => $query,
];

$container = $folder->getContainerEntity();

$owner_guids = ELGG_ENTITIES_ANY_VALUE;
$container_guids = ELGG_ENTITIES_ANY_VALUE;

// We want to make sure the folder items are accessible
// by users who are allowed to see the folder
$group_acl_id = null;
if ($container instanceof \ElggGroup) {
	$acl = $container->getOwnedAccessCollection('group_acl');
	if ($acl) {
		$group_acl_id = $acl->id;
	}
}

$access_ids = array_filter([
	ACCESS_PUBLIC,
	ACCESS_LOGGED_IN,
	$folder->access_id,
	$group_acl_id,
]);

if ($container instanceof ElggUser) {
	$restrict = elgg_get_plugin_setting('user_folders_restrict_by_owner', 'hypefolders', true);
	if ($restrict && !elgg_is_admin_logged_in()) {
		$owner_guids = $container->guid;
	}
} else {
	$restrict = elgg_get_plugin_setting('group_folders_restrict_by_container', 'hypefolders', true);
	if ($restrict) {
		$container_guids = $container->guid;
	}
}

$options['owner_guids'] = $owner_guids;
$options['container_guids'] = $container_guids;

if (!empty($access_ids)) {
	$options['wheres'][] = function (\Elgg\Database\QueryBuilder $qb, $main_alias) use ($access_ids) {
		return $qb->compare("{$main_alias}.access_id", 'IN', $access_ids, ELGG_VALUE_INTEGER);
	};
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
