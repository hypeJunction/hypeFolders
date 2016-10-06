<?php

use hypeJunction\Folders\MainFolder;

elgg_gatekeeper();

$container_guid = elgg_extract('container_guid', $vars);
$container = get_entity($container_guid);

if (!$container) {
	$container = elgg_get_logged_in_user_entity();
}

if (!$container->canWriteToContainer(0, 'object', MainFolder::SUBTYPE)) {
	register_error(elgg_echo('folders:write:error:container'));
	forward(REFERRER);
}

elgg_push_breadcrumb($container->getDisplayName(), $container->getURL());
if ($container instanceof ElggGroup) {
	elgg_push_breadcrumb(elgg_echo('folders'), "folders/group/$container->guid");
} else {
	elgg_push_breadcrumb(elgg_echo('folders'), "folders/owner/$container->username");
}
elgg_push_breadcrumb(elgg_echo('folders:add'));

$title = elgg_echo('folders:add');
$content = elgg_view('folders/edit', array(
	'container' => $container,
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => false,
));

echo elgg_view_page($title, $layout);

