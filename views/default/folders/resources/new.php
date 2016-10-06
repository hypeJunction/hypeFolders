<?php

use hypeJunction\Folders\MainFolder;

$folder = elgg_extract('folder', $vars);
$parent = elgg_extract('resource', $vars);

if (!$folder instanceof MainFolder) {
	return;
}

echo elgg_view_input('hidden', [
	'name' => 'main_folder_guid',
	'value' => $folder->guid,
]);
echo elgg_view_input('hidden', [
	'name' => 'parent_guid',
	'value' => $parent->guid,
]);
