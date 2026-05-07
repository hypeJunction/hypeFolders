<?php

use hypeJunction\Folders\MainFolder;

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MainFolder::SUBTYPE);
$folder = get_entity($guid);
/* @var $folder MainFolder */
$resource_guid = elgg_extract('resource_guid', $vars);
$resource = get_entity($resource_guid);
if (!$resource) {
	$resource = $folder;
}

$container = $folder->getContainerEntity();
if (!$container) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

if ($container instanceof \ElggGroup) {
	elgg_group_gatekeeper(true, $container->guid);
}

elgg_set_page_owner_guid($container->guid);
$folder->setBreadcrumbs($resource->guid);
elgg_pop_breadcrumb();
$title = $resource->getDisplayName();
elgg_push_breadcrumb($title);
$items = \hypeJunction\Folders\Menus::getProfileMenuItems($resource, $folder, false);
foreach ($items as $item) {
	$item->addLinkClass('elgg-button elgg-button-action');
	elgg_register_menu_item('title', $item);
}

$content = elgg_view('folders/resource', ['folder' => $folder, 'entity' => $resource, 'full_view' => true]);
$sidebar = elgg_view('folders/sidebar', ['folder' => $folder, 'resource' => $resource]);
if (elgg_is_xhr()) {
	echo $content;
} else {
	$layout = elgg_view_layout('content', ['title' => $title, 'content' => $content, 'sidebar' => $sidebar, 'filter' => false, 'folder' => $folder]);
	echo elgg_view_page($title, $layout, 'default', ['folder' => $folder]);
}
