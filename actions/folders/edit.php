<?php

elgg_make_sticky_form('folders/edit');

$guid = get_input('guid');
$container_guid = get_input('container_guid');
$title = get_input('title');
$description = get_input('description');
$tags = get_input('tags', '');
$access_id = get_input('access_id');

$user = elgg_get_logged_in_user_entity();

$entity = ($guid) ? get_entity($guid) : null;
$container = get_entity($container_guid);

if (!$title) {
	return elgg_error_response(elgg_echo('folders:input:error:required', ['folders:folder:title']));
}

if ($guid) {
	if (!$entity) {
		return elgg_error_response(elgg_echo('folders:get:error:entity'));
	}
} else {
	if (!$container || !$container->canWriteToContainer(0, 'object', \hypeJunction\Folders\MainFolder::SUBTYPE)) {
		return elgg_error_response(elgg_echo('folders:write:error:container'));
	}

	$entity = new \hypeJunction\Folders\MainFolder();
	$entity->container_guid = $container->guid;
	$entity->owner_guid = $user->guid;
}

$entity->title = $title;
$entity->description = $description;
$entity->tags = elgg_string_to_array($tags);
$entity->access_id = $access_id;

if ($entity->save()) {
	$entity->saveIconFromUploadedFile('icon');

	elgg_clear_sticky_form('folders/edit');

	return elgg_ok_response('', elgg_echo('folders:save:success'), $entity->getURL());
} else {
	return elgg_error_response(elgg_echo('folders:save:error:generic'));
}
