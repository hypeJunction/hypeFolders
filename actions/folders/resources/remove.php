<?php

use hypeJunction\Folders\MainFolder;

$guids = array_values((array) get_input('guids', []));
$main_folder_guid = get_input('main_folder_guid');

$main_folder = get_entity($main_folder_guid);

if (empty($guids) || !is_array($guids)) {
	elgg_redirect_response(REFERRER);
}

if (!$main_folder instanceof MainFolder || !$main_folder->canWriteToContainer()) {
	elgg_register_error_message(elgg_echo('folders:folder:error:no_entity'));
	elgg_redirect_response(REFERRER);
}

$success = 0;
foreach ($guids as $weight => $guid) {
	if ($main_folder->removeResource($guid)) {
		$success++;
	}
}

$forward_url = $main_folder->getURL();
elgg_redirect_response($forward_url);
