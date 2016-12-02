<?php

use hypeJunction\Folders\MainFolder;

$resource_guid = get_input('guid');
$parent_guid = get_input('parent_guid');
$folder_guid = get_input('folder_guid');

$folder = get_entity($folder_guid);
if (!$folder instanceof MainFolder || !$folder->canWriteToContainer()) {
	return elgg_error_response(elgg_echo('folders:error:permissions'));
}

$folder->addResource($resource_guid, $parent_guid);

return elgg_ok_response('', elgg_echo('folders:move:success'));
