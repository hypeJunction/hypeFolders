<?php

use hypeJunction\Folders\MainFolder;

$items = get_input('items');

foreach ($items as $item) {
	if (empty($item) || !is_array($item)) {
		continue;
	}

	$weight = elgg_extract('weight', $item);
	$resource_guid = elgg_extract('guid', $item);
	$parent_guid = elgg_extract('parent_guid', $item);
	$folder_guid = elgg_extract('folder_guid', $item);

	$folder = get_entity($folder_guid);
	if (!$folder instanceof MainFolder || !$folder->canWriteToContainer()) {
		continue;
	}

	$folder->addResource($resource_guid, $parent_guid, $weight);
}

return elgg_ok_response(elgg_echo('folders:reoder:success'));
