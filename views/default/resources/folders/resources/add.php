<?php

use hypeJunction\Folders\MainFolder;

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MainFolder::SUBTYPE);

$folder = get_entity($guid);
/* @var $folder MainFolder */

if (!$folder->canWriteToContainer()) {
	forward('', '403');
}

$resource_guid = elgg_extract('resource_guid', $vars);
$resource = get_entity($resource_guid);
/* @var $resource ElggEntity */

if (!$resource) {
	$resource = $folder;
}

$folder->setBreadcrumbs($resource->guid);

$title = elgg_echo('folders:resources:add');
elgg_push_breadcrumb($title);

$content = elgg_view('folders/resources/add', array(
	'folder' => $folder,
	'resource' => $resource,
));

if (elgg_is_xhr()) {
	echo $content;
} else {

	$filter = elgg_view('folders/filter', [
		'folder' => $folder,
		'resource' => $resource,
		'filter_context' => 'resources/add',
	]);

	$sidebar = elgg_view('folders/sidebar', array(
		'folder' => $folder,
		'resource' => $resource,
	));

	$layout = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'filter' => $filter,
		'sidebar' => $sidebar,
	));
	echo elgg_view_page($title, $layout);
}
