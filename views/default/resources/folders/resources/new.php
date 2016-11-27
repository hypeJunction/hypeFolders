<?php

use hypeJunction\Folders\MainFolder;

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

$content = elgg_view("folders/resources/new/$subtype", [
	'folder' => $folder,
	'resource' => $resource,
]);

if (elgg_is_xhr()) {
	echo $content;
	return;
}

$title = elgg_echo('folders:resources:new_type', [strtolower(elgg_echo("folders:new:$subtype"))]);

$folder->setBreadcrumbs($resource->guid);

elgg_push_breadcrumb($title);

$sidebar = elgg_view('folders/sidebar', array(
	'folder' => $folder,
	'resource' => $resource,
		));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => false,
	'sidebar' => $sidebar,
		));

echo elgg_view_page($title, $layout);