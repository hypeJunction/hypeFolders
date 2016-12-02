<?php

use hypeJunction\Folders\MainFolder;

$guids = array_values((array) get_input('guids', []));
$main_folder_guid = get_input('main_folder_guid');
$resource_guid = get_input('resource_guid');

$main_folder = get_entity($main_folder_guid);
$resource = get_entity($resource_guid);

if (empty($guids) || !is_array($guids)) {
	forward(REFERRER);
}

if (!$main_folder instanceof MainFolder || !$main_folder->canWriteToContainer()) {
	register_error(elgg_echo('folders:folder:error:no_entity'));
	forward(REFERRER);
}

$success = 0;
foreach ($guids as $weight => $guid) {
	if ($main_folder->addResource($guid, $resource->guid, $weight)) {
		$success++;
	}
}

//system_message(elgg_echo('folders:resources:add:success', array($success, count($guids))));

$forward_url = ($resource) ? $resource->getURL() : $main_folder->getURL();
forward($forward_url);
