<?php

use hypeJunction\Folders\MainFolder;

elgg_ajax_gatekeeper();

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MainFolder::SUBTYPE);

$folder = get_entity($guid);
/* @var $folder MainFolder */

$resource_guid = elgg_extract('resource_guid', $vars);
$resource = get_entity($resource_guid);
/* @var $resource ElggEntity */

if (!$resource) {
	$resource = $folder;
}

$container = $folder->getContainerEntity();
elgg_set_page_owner_guid($container->guid);

$subtype = elgg_extract('subtype', $vars);

echo elgg_view("folders/resources/new/$subtype", [
	'folder' => $folder,
	'resource' => $resource,
]);