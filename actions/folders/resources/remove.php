<?php

use hypeJunction\Folders\MainFolder;

$guids = array_values((array) get_input('guids', []));
$main_folder_guid = get_input('main_folder_guid');

$main_folder = get_entity($main_folder_guid);

if (empty($guids) || !is_array($guids)) {
	return elgg_error_response('');
}

if (!$main_folder instanceof MainFolder || !$main_folder->canEdit()) {
	return elgg_error_response(elgg_echo('folders:folder:error:no_entity'));
}

$success = 0;
foreach ($guids as $weight => $guid) {
	if ($main_folder->removeResource($guid)) {
		$success++;
	}
}

$forward_url = $main_folder->getURL();
return elgg_ok_response('', '', $forward_url);
