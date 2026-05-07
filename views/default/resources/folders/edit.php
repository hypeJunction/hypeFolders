<?php

use hypeJunction\Folders\MainFolder;

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MainFolder::SUBTYPE);

$folder = get_entity($guid);

if (!$folder->canEdit()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException(elgg_echo('folders:edit:error:entity'));
}

$container = $folder->getContainerEntity();
elgg_set_page_owner_guid($container->guid);

$folder->setBreadcrumbs($folder->guid);
elgg_push_breadcrumb(elgg_echo('folders:edit'));

$title = elgg_echo('folders:edit');
$content = elgg_view('folders/edit', [
	'entity' => $folder,
	'container' => $container,
]);

$filter = elgg_view('folders/filter', [
	'folder' => $folder,
	'resource' => $folder,
	'filter_context' => 'edit',
]);

$sidebar = elgg_view('folders/sidebar', [
	'folder' => $folder,
]);

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => $sidebar,
]);

echo elgg_view_page($title, $layout);

