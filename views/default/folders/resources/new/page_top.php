<?php

use hypeJunction\Folders\MainFolder;

$folder = elgg_extract('folder', $vars);
$resource = elgg_extract('resource', $vars);

if (!$folder instanceof MainFolder) {
	return;
}

elgg_set_page_owner_guid($folder->container_guid);

elgg_extend_view('pages/edit', 'folders/resources/new');

echo elgg_view_form('pages/edit', [
	'enctype' => 'multipart/form-data',
	'class' => 'elgg-form-folders-resources-add',
], [
	'folder' => $folder,
	'resource' => $resource,
	'container_guid' => $folder->container_guid,
]);