<?php

use hypeJunction\Folders\MainFolder;

$username = elgg_extract('username', $vars);
$user = get_user_by_username($username);

if (!$user) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

if (!$user->canEdit()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

elgg_set_page_owner_guid($user->guid);

elgg_push_breadcrumb(elgg_echo('folders'), 'folders/all');
elgg_push_breadcrumb($user->getDisplayName(), "folders/owner/$container->username");
elgg_push_breadcrumb(elgg_echo('friends'), "folders/friends/$container->username");

elgg_register_title_button('folders', 'add', 'object', MainFolder::SUBTYPE);

$title = elgg_echo('folders:friends');
$content = elgg_view('folders/listing/friends', [
	'entity' => $user,
]);

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter_context' => 'friends',
]);

echo elgg_view_page($title, $layout);
