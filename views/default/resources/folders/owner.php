<?php

use hypeJunction\Folders\MainFolder;

$username = elgg_extract('username', $vars);
$user = get_user_by_username($username);

if (!$user || !elgg_get_plugin_setting('user_folders', 'hypeFolders', false)) {
	forward('', '404');
}

elgg_set_page_owner_guid($user->guid);

elgg_push_breadcrumb(elgg_echo('folders'), 'folders/all');
elgg_push_breadcrumb($user->getDisplayName(), "folders/owner/$user->guid");

elgg_register_title_button('folders', 'add', 'object', MainFolder::SUBTYPE);

$title = elgg_echo('folders:owner', array($user->getDisplayName()));

$content = elgg_view('folders/listing/owner', [
	'entity' => $user,
]);

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter_context' => 'mine',
		));

echo elgg_view_page($title, $layout);
