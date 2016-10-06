<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

$folder = elgg_extract('folder', $vars);
if (!$folder instanceof MainFolder) {
	return;
}

$resource = elgg_extract('resource', $vars);
if (!$resource) {
	$resource = $folder;
}

$no_results = '';
if ($resource instanceof MainFolder || $resource instanceof Folder) {
	$no_results = elgg_echo('folders:resources:no_results');
}

$resources = $folder->getChildren($resource->guid, ['limit' => 0]);

if (empty($resources) && elgg_extract('show_placeholder', $vars, false)) {
	echo elgg_format_element('ul', [
		'class' => 'elgg-list folders-content-list',
	], '');
	return;
}

echo elgg_view_entity_list($resources, [
	'list_id' => "folders-resources-$folder->guid",
	'list_class' => 'folders-content-list',
	'pagination_type' => 'infinite',
	'base_url' => elgg_normalize_url("folders/view/$folder->guid/$resource->guid"),
	'offset_key' => 'folder_resources_offset',
	'item_view' => elgg_extract('item_view', $vars, 'folders/resource'),
	'folder' => $folder,
	'input_name' => elgg_extract('input_name', $vars, false),
	'limit' => $limit,
	'offset' => $offset,
	'count' => $count,
	'full_view' => false,
	'no_results' => $no_results,
]);
