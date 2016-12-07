<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MainFolder::SUBTYPE);

$folder = get_entity($guid);
/* @var $folder MainFolder */

$resource_guid = elgg_extract('resource_guid', $vars);
elgg_entity_gatekeeper($resource_guid, 'object', Folder::SUBTYPE);

$resource = get_entity($resource_guid);
/* @var $resource Folder */

if (!$resource->canEdit()) {
	forward('', '403');
}

$container = $folder->getContainerEntity();
elgg_set_page_owner_guid($container->guid);

$folder->setBreadcrumbs($resource->guid);

$title = elgg_echo('folders:edit');
elgg_push_breadcrumb($title);

$sidebar = elgg_view('folders/sidebar', array(
	'folder' => $folder,
	'resource' => $resource,
		));

$filter = elgg_view('folders/filter', [
	'folder' => $folder,
	'resource' => $resource,
	'filter_context' => 'edit',
		]);

$content = elgg_view('folders/resources/edit', array(
	'entity' => $resource,
	'folder' => $folder,
		));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => $sidebar,
		));

echo elgg_view_page($title, $layout);
